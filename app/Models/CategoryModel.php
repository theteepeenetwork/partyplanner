<?php namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'parent_id'];

    /**
     * Top-level categories for home search, browse root dropdown, etc.
     *
     * @return list<array<string, mixed>>
     */
    public function getRootCategories(): array
    {
        return $this->where('parent_id', null)->orderBy('name', 'ASC')->findAll();
    }

    /**
     * Human-readable path for a service row (main · sub · third).
     */
    public function getServiceCategoryLabel(array $service): string
    {
        $parts = [];
        foreach (['category_id', 'subcategory_id', 'third_category_id'] as $col) {
            if (! empty($service[$col])) {
                $row = $this->find((int) $service[$col]);
                if ($row) {
                    $parts[] = $row['name'];
                }
            }
        }

        return implode(' · ', $parts);
    }

    /**
     * Validate root / sub / optional third against the adjacency tree.
     *
     * @param mixed $subId
     * @param mixed $thirdId
     */
    public function validateAssignment($rootId, $subId, $thirdId): ?string
    {
        $rootId = (int) $rootId;
        if ($rootId < 1) {
            return 'Please select a category.';
        }

        $root = $this->find($rootId);
        if (! $root) {
            return 'Invalid category.';
        }

        if ($this->isNonNullParent($root['parent_id'] ?? null)) {
            return 'Please select a top-level category.';
        }

        $subId = $this->normalizeOptionalId($subId);
        $thirdId = $this->normalizeOptionalId($thirdId);

        $rootChildCount = (new self())->where('parent_id', $rootId)->countAllResults();

        if ($rootChildCount > 0) {
            if ($subId === null) {
                return 'Please select a subcategory.';
            }
            $sub = $this->find($subId);
            if (! $sub || (int) ($sub['parent_id'] ?? 0) !== $rootId) {
                return 'Invalid subcategory for the selected category.';
            }
        } else {
            if ($subId !== null) {
                return 'This category does not use subcategories.';
            }
            if ($thirdId !== null) {
                return 'This category does not use further subcategories.';
            }

            return null;
        }

        if ($thirdId === null) {
            return null;
        }

        $sub = $this->find((int) $subId);
        if (! $sub) {
            return 'Invalid subcategory.';
        }

        $third = $this->find($thirdId);
        if (! $third || (int) ($third['parent_id'] ?? 0) !== (int) $sub['id']) {
            return 'Invalid further subcategory for the selected subcategory.';
        }

        return null;
    }

    /**
     * @param mixed $v
     */
    private function normalizeOptionalId($v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }

        $n = (int) $v;

        return $n > 0 ? $n : null;
    }

    /**
     * @param mixed $parentId
     */
    private function isNonNullParent($parentId): bool
    {
        return $parentId !== null && $parentId !== '';
    }

    /**
     * Walk parent_id chain to the catalogue root.
     */
    public function getRootAncestorId(?int $id): ?int
    {
        if ($id === null || $id < 1) {
            return null;
        }

        $current = $this->find($id);
        if (! $current) {
            return null;
        }

        for ($i = 0; $i < 24; $i++) {
            if (! $this->isNonNullParent($current['parent_id'] ?? null)) {
                return (int) $current['id'];
            }
            $parent = $this->find((int) $current['parent_id']);
            if (! $parent) {
                return (int) $current['id'];
            }
            $current = $parent;
        }

        return (int) $current['id'];
    }

    /**
     * Match planning checklist buckets to live root categories by name keywords.
     *
     * @param list<array{icon: string, label: string, keywords: list<string>}> $definitions
     *
     * @return list<array{icon: string, label: string, cat_ids: list<int>}>
     */
    public function mapPlanningDefinitionsToRoots(array $definitions): array
    {
        $roots   = $this->getRootCategories();
        $matched = [];

        foreach ($definitions as $def) {
            $ids = [];
            foreach ($roots as $root) {
                $name = strtolower((string) ($root['name'] ?? ''));
                foreach ($def['keywords'] ?? [] as $kw) {
                    $kw = strtolower((string) $kw);
                    if ($kw === '') {
                        continue;
                    }
                    if (str_contains($name, $kw)) {
                        $ids[] = (int) $root['id'];
                        break;
                    }
                }
            }
            $matched[] = [
                'icon'    => $def['icon'],
                'label'   => $def['label'],
                'cat_ids' => array_values(array_unique($ids)),
            ];
        }

        return $matched;
    }

    /**
     * First root category id whose name matches any keyword (case-insensitive), or null.
     *
     * @param list<string> $keywords
     */
    public function findFirstRootIdByKeywords(array $keywords): ?int
    {
        $roots = $this->getRootCategories();
        foreach ($roots as $root) {
            $name = strtolower((string) ($root['name'] ?? ''));
            foreach ($keywords as $kw) {
                $kw = strtolower((string) $kw);
                if ($kw !== '' && str_contains($name, $kw)) {
                    return (int) $root['id'];
                }
            }
        }

        return null;
    }
}
