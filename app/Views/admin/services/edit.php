<h1 class="h3 mb-3">Edit service</h1>
<form method="post" action="<?= site_url('/admin/services/' . $service['id'] . '/edit') ?>" class="card shadow-sm p-4">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Title</label>
            <input class="form-control" name="title" value="<?= esc(old('title', $service['title'])) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Price</label>
            <input class="form-control" name="price" value="<?= esc(old('price', (string) $service['price'])) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <?php foreach (['active','inactive'] as $st): ?>
                    <option value="<?= esc($st) ?>" <?= ($service['status'] ?? '') === $st ? 'selected' : '' ?>><?= esc($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select">
                <option value="">—</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>" <?= (int) ($service['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>><?= esc($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Short description</label>
            <input class="form-control" name="short_description" value="<?= esc(old('short_description', $service['short_description'] ?? '')) ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="8"><?= esc(old('description', $service['description'] ?? '')) ?></textarea>
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-primary" type="submit">Save</button>
        <a class="btn btn-link" href="<?= site_url('/admin/services/' . $service['id']) ?>">Cancel</a>
    </div>
</form>
