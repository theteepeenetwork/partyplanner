<h1 class="h3 mb-3">Edit page: <?= esc($page['slug']) ?></h1>
<form method="post" action="<?= site_url('/admin/pages/edit/' . $page['slug']) ?>" class="card shadow-sm p-4">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label class="form-label">Title</label>
        <input class="form-control" name="title" value="<?= esc(old('title', $page['title'])) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" style="max-width:220px">
            <?php foreach (['draft','published'] as $st): ?>
                <option value="<?= esc($st) ?>" <?= ($page['status'] ?? '') === $st ? 'selected' : '' ?>><?= esc($st) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Meta title</label>
        <input class="form-control" name="meta_title" value="<?= esc(old('meta_title', $page['meta_title'] ?? '')) ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Meta description</label>
        <input class="form-control" name="meta_description" value="<?= esc(old('meta_description', $page['meta_description'] ?? '')) ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Content (HTML allowed)</label>
        <textarea class="form-control font-monospace" name="content" rows="16"><?= htmlspecialchars((string) old('content', $page['content'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
    </div>
    <button class="btn btn-primary" type="submit">Save</button>
    <a class="btn btn-link" href="<?= site_url('/admin/pages') ?>">Back to list</a>
</form>
