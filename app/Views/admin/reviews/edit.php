<header class="admin-page-header">
    <h1 class="admin-page-title">Edit review</h1>
    <p class="admin-page-subtitle">Adjust the rating or wording. Saved text is re-run through the profanity filter.</p>
</header>
<form method="post" action="<?= site_url('/admin/reviews/' . $review['id'] . '/edit') ?>" class="card shadow-sm p-4">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Rating</label>
            <select name="rating" class="form-select">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?= $i ?>" <?= (int) ($review['rating'] ?? 0) === $i ? 'selected' : '' ?>><?= $i ?> star<?= $i === 1 ? '' : 's' ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-9">
            <label class="form-label">Title</label>
            <input class="form-control" name="title" maxlength="150" value="<?= esc(old('title', $review['title'])) ?>" required>
        </div>
        <div class="col-12">
            <label class="form-label">Comment</label>
            <textarea class="form-control" name="comment" rows="8" maxlength="2000" required><?= esc(old('comment', $review['comment'])) ?></textarea>
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-primary" type="submit">Save</button>
        <a class="btn btn-link" href="<?= site_url('/admin/reviews/' . $review['id']) ?>">Cancel</a>
    </div>
</form>
