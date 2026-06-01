<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">

        <?= $this->include('dashboard/_vendor_tabs') ?>
        <?= $this->include('dashboard/_flash_alerts') ?>

        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="mb-4">
                    <h3 class="mb-1">Host Profile</h3>
                    <p class="text-muted mb-0">This information appears in the <strong>Meet your host</strong> section on each of your service listings. A complete profile builds trust with customers.</p>
                </div>

                <form action="/profile/host-profile" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <!-- Photo -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Your photo</h5>
                            <div class="d-flex align-items-center gap-4 flex-wrap">
                                <?php if (!empty($user['host_photo_path'])): ?>
                                    <img src="<?= base_url(esc($user['host_photo_path'])) ?>"
                                         alt="Host photo"
                                         class="rounded-circle"
                                         style="width:96px;height:96px;object-fit:cover;border:2px solid #dee2e6;">
                                <?php else: ?>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary text-white fw-bold"
                                         style="width:96px;height:96px;font-size:2rem;flex-shrink:0;">
                                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <label class="form-label mb-1 fw-semibold" for="host_photo">Upload a new photo</label>
                                    <input type="file" class="form-control" id="host_photo" name="host_photo"
                                           accept="image/jpeg,image/png,image/webp,image/gif">
                                    <div class="form-text">JPG, PNG or WebP · max 5 MB. Square photos work best.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bio & tagline -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">About you</h5>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="host_tagline">Role / tagline</label>
                                <input type="text" class="form-control" id="host_tagline" name="host_tagline"
                                       maxlength="255"
                                       placeholder="e.g. Bandleader &amp; saxophone · hosting since 2016"
                                       value="<?= esc($user['host_tagline'] ?? '') ?>">
                                <div class="form-text">Shown directly below your name. Keep it short.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="host_bio">Bio</label>
                                <textarea class="form-control" id="host_bio" name="host_bio"
                                          rows="5"
                                          placeholder="Tell customers a little about yourself and your experience…"><?= esc($user['host_bio'] ?? '') ?></textarea>
                                <div class="form-text">2–4 sentences works well. Aim for warm and personal.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Quote & plays -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Personal touch</h5>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="host_quote">Your quote</label>
                                <textarea class="form-control" id="host_quote" name="host_quote"
                                          rows="3"
                                          placeholder="One thing you'd say to a customer considering your service…"><?= esc($user['host_quote'] ?? '') ?></textarea>
                                <div class="form-text">Displayed as a pull-quote on your listing. One or two sentences.</div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-semibold" for="host_plays">Events you work at</label>
                                <?php
                                $playsArr = [];
                                if (!empty($user['host_plays'])) {
                                    $decoded = json_decode($user['host_plays'], true);
                                    $playsArr = is_array($decoded) ? $decoded : [];
                                }
                                $playsValue = implode(', ', $playsArr);
                                ?>
                                <input type="text" class="form-control" id="host_plays" name="host_plays"
                                       placeholder="e.g. Weddings, Corporate parties, Private celebrations"
                                       value="<?= esc($playsValue) ?>">
                                <div class="form-text">Comma-separated list — shown as tags on your listing.</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">Save host profile</button>
                        <a href="/profile" class="btn btn-outline-secondary">Cancel</a>
                    </div>

                </form>

                <?php if (!empty($user['host_bio']) || !empty($user['host_tagline'])): ?>
                <div class="card mt-5">
                    <div class="card-body">
                        <h5 class="card-title mb-3 text-muted">Preview — as customers will see it</h5>
                        <div class="host-preview-card p-4 rounded" style="background:#f8f5f1;border:1px solid #e4d9c9;">
                            <div class="d-flex align-items-start gap-3 mb-3">
                                <?php if (!empty($user['host_photo_path'])): ?>
                                    <img src="<?= base_url(esc($user['host_photo_path'])) ?>"
                                         alt="Host photo"
                                         class="rounded-3"
                                         style="width:80px;height:80px;object-fit:cover;flex-shrink:0;border:1px solid #e4d9c9;">
                                <?php else: ?>
                                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-secondary text-white fw-bold"
                                         style="width:80px;height:80px;font-size:1.6rem;flex-shrink:0;">
                                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-bold fs-5 mb-1"><?= esc($user['name']) ?></div>
                                    <?php if (!empty($user['host_tagline'])): ?>
                                        <div class="text-muted small"><?= esc($user['host_tagline']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($user['host_bio'])): ?>
                                <p class="mb-3 text-muted" style="font-size:0.95rem;"><?= nl2br(esc($user['host_bio'])) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($playsArr)): ?>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <?php foreach ($playsArr as $tag): ?>
                                        <span class="badge rounded-pill" style="background:#efe6d9;color:#3a312d;font-weight:600;font-size:0.8rem;padding:6px 12px;"><?= esc($tag) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($user['host_quote'])): ?>
                                <blockquote class="mb-0 ps-3" style="border-left:3px solid #b66a4d;font-style:italic;color:#3a312d;">
                                    <?= esc($user['host_quote']) ?>
                                </blockquote>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>
</main>

<?= $this->include('footer') ?>
