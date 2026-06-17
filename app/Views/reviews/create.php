<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container" style="max-width:720px">

        <p class="mb-3"><a href="/profile/my-bookings">&larr; Back to my bookings</a></p>

        <div class="mb-4">
            <h4 class="mb-2">Leave a review</h4>
            <?php if (!empty($item['service_title'])): ?>
                <p class="dash-page-lead mb-0">
                    <?= esc($item['service_title']) ?>
                    <?php if (!empty($item['event_title'])): ?>
                        &middot; <?= esc($item['event_title']) ?>
                    <?php endif; ?>
                    <?php if (!empty($item['event_date'])): ?>
                        <span class="text-muted">— <?= date('d M Y', strtotime($item['event_date'])) ?></span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>

        <?php $errors = session()->getFlashdata('errors') ?? []; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="dash-card">
            <form method="post" action="/review/store">
                <?= csrf_field() ?>
                <input type="hidden" name="booking_item_id" value="<?= (int) $bookingItemId ?>">

                <div class="mb-4">
                    <label class="form-label d-block fw-semibold">Your rating</label>
                    <div class="review-stars" role="radiogroup" aria-label="Star rating">
                        <?php $old = (int) (old('rating') ?? 0); ?>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>"
                                   <?= $old === $i ? 'checked' : '' ?> required>
                            <label for="star<?= $i ?>" title="<?= $i ?> star<?= $i === 1 ? '' : 's' ?>">&#9733;</label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label fw-semibold">Title</label>
                    <input type="text" class="form-control" id="title" name="title"
                           maxlength="150" value="<?= esc(old('title') ?? '') ?>"
                           placeholder="Summarise your experience" required>
                </div>

                <div class="mb-4">
                    <label for="comment" class="form-label fw-semibold">Your review</label>
                    <textarea class="form-control" id="comment" name="comment" rows="6"
                              maxlength="2000" placeholder="Tell other customers about your experience" required><?= esc(old('comment') ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Submit review</button>
                <a href="/profile/my-bookings" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
</main>

<style>
.review-stars { display: inline-flex; flex-direction: row-reverse; gap: 4px; font-size: 32px; }
.review-stars input { display: none; }
.review-stars label { color: #d8cdbb; cursor: pointer; transition: color .15s ease; line-height: 1; }
.review-stars input:checked ~ label,
.review-stars label:hover,
.review-stars label:hover ~ label { color: #B98C2A; }
</style>

<?= $this->include('footer') ?>
