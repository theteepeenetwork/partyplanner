<h1 class="h3 mb-3">Edit event</h1>
<form method="post" action="<?= site_url('/admin/events/' . $event['id'] . '/edit') ?>" class="card shadow-sm p-4">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Title</label>
            <input class="form-control" name="title" value="<?= esc(old('title', $event['title'])) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" name="date" value="<?= esc(old('date', $event['date'] ?? '')) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <input class="form-control" name="status" value="<?= esc(old('status', $event['status'] ?? 'active')) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Event type</label>
            <input class="form-control" name="event_type" value="<?= esc(old('event_type', $event['event_type'] ?? '')) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Guest count</label>
            <input class="form-control" name="guest_count" value="<?= esc(old('guest_count', (string) ($event['guest_count'] ?? ''))) ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Location</label>
            <input class="form-control" name="location" value="<?= esc(old('location', $event['location'] ?? '')) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Venue</label>
            <input class="form-control" name="venue_name" value="<?= esc(old('venue_name', $event['venue_name'] ?? '')) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Postcode</label>
            <input class="form-control" name="postcode" value="<?= esc(old('postcode', $event['postcode'] ?? '')) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Town / city</label>
            <input class="form-control" name="town_city" value="<?= esc(old('town_city', $event['town_city'] ?? '')) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Budget min</label>
            <input class="form-control" name="budget_min" value="<?= esc(old('budget_min', (string) ($event['budget_min'] ?? ''))) ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Budget max</label>
            <input class="form-control" name="budget_max" value="<?= esc(old('budget_max', (string) ($event['budget_max'] ?? ''))) ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Style / theme</label>
            <input class="form-control" name="style_theme" value="<?= esc(old('style_theme', $event['style_theme'] ?? '')) ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="4"><?= esc(old('description', $event['description'] ?? '')) ?></textarea>
        </div>
        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea class="form-control" name="notes" rows="3"><?= esc(old('notes', $event['notes'] ?? '')) ?></textarea>
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-primary" type="submit">Save</button>
        <a class="btn btn-link" href="<?= site_url('/admin/events/' . $event['id']) ?>">Cancel</a>
    </div>
</form>
