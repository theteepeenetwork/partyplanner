<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">

        <?= $this->include('dashboard/_vendor_tabs') ?>

        <!-- 1. Greeting / Header -->
        <div class="mb-4">
            <h3 class="mb-2">Welcome back, <?= esc($user['name']) ?> 👋</h3>
            <p class="dash-page-lead mb-2">Your vendor command centre—pending requests, calendar, and payouts stay organised so customers know they are in good hands.</p>
            <p class="text-muted small mb-0">Focus on what needs action today; everything else is a tap away in the tabs above.</p>
        </div>

        <!-- 2. Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-light mx-auto">
                        <i class="fas fa-clock"></i>
                    </div>
                    <!-- TODO: Replace with dynamic count from booking_items where status='pending' for vendor's services -->
                    <div class="stat-value"><?= esc($pendingBookings) ?></div>
                    <div class="stat-label">Pending Requests</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card">
                    <div class="stat-icon bg-success-light mx-auto">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <!-- TODO: Replace with dynamic count from accepted booking_items for vendor's services -->
                    <div class="stat-value"><?= esc($upcomingBookings) ?></div>
                    <div class="stat-label">Upcoming Bookings</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-light mx-auto">
                        <i class="fas fa-pound-sign"></i>
                    </div>
                    <!-- TODO: Calculate from payments table for current month where vendor's services were booked -->
                    <div class="stat-value">£0</div>
                    <div class="stat-label">Earnings This Month</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card">
                    <div class="stat-icon bg-info-light mx-auto">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-value"><?= esc($activeServicesCount) ?></div>
                    <div class="stat-label">Active Services</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card">
                    <div class="stat-icon bg-purple-light mx-auto">
                        <i class="fas fa-eye"></i>
                    </div>
                    <!-- TODO: Implement profile/service view tracking -->
                    <div class="stat-value">—</div>
                    <div class="stat-label">Profile Views</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card">
                    <div class="stat-icon bg-danger-light mx-auto">
                        <i class="fas fa-reply"></i>
                    </div>
                    <!-- TODO: Calculate average response time from chat_messages -->
                    <div class="stat-value">—</div>
                    <div class="stat-label">Avg Response</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">

                <!-- 3. Needs Your Attention -->
                <div class="dash-card">
                    <h5><i class="fas fa-exclamation-circle text-warning me-2"></i>Needs Your Attention</h5>

                    <?php $hasAttention = false; ?>

                    <?php if ($pendingBookings > 0): $hasAttention = true; ?>
                        <div class="attention-card border-warning">
                            <div class="attention-icon bg-warning-light"><i class="fas fa-clock"></i></div>
                            <div class="attention-content">
                                <div class="attention-title">Booking Requests Awaiting Response</div>
                                <p class="attention-desc"><?= $pendingBookings ?> booking request<?= $pendingBookings > 1 ? 's' : '' ?> need<?= $pendingBookings == 1 ? 's' : '' ?> your response</p>
                            </div>
                            <div class="attention-action">
                                <a href="/profile/bookings" class="btn btn-sm btn-outline-warning">Review</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($unreadMessages > 0): $hasAttention = true; ?>
                        <div class="attention-card border-info">
                            <div class="attention-icon bg-info-light"><i class="fas fa-envelope"></i></div>
                            <div class="attention-content">
                                <div class="attention-title">Unread Customer Messages</div>
                                <p class="attention-desc"><?= $unreadMessages ?> unread message<?= $unreadMessages > 1 ? 's' : '' ?> from customers</p>
                            </div>
                            <div class="attention-action">
                                <!-- TODO: Link to messages page when built -->
                                <a href="/profile/messages" class="btn btn-sm btn-outline-info">View</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($servicesMissingImages > 0): $hasAttention = true; ?>
                        <div class="attention-card border-danger">
                            <div class="attention-icon bg-danger-light"><i class="fas fa-image"></i></div>
                            <div class="attention-content">
                                <div class="attention-title">Services Missing Photos</div>
                                <p class="attention-desc"><?= $servicesMissingImages ?> service<?= $servicesMissingImages > 1 ? 's' : '' ?> without images — add photos to attract more customers</p>
                            </div>
                            <div class="attention-action">
                                <a href="/profile/services" class="btn btn-sm btn-outline-danger">Fix</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($activeServicesCount == 0): $hasAttention = true; ?>
                        <div class="attention-card border-primary">
                            <div class="attention-icon bg-primary-light"><i class="fas fa-plus-circle"></i></div>
                            <div class="attention-content">
                                <div class="attention-title">No Active Services</div>
                                <p class="attention-desc">Create your first service listing to start receiving bookings</p>
                            </div>
                            <div class="attention-action">
                                <a href="/service/create" class="btn btn-sm btn-outline-primary">Create</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!$hasAttention): ?>
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                            <p class="text-muted mb-0">You're all caught up! Nothing needs your attention right now.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 4. Upcoming Bookings Preview -->
                <div class="dash-card">
                    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-2 mb-3">
                        <div>
                            <h5 class="mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i>Upcoming Bookings</h5>
                            <p class="text-muted small mb-0 mt-1">Confirmed work on your calendar at a glance. Tap an event for full customer details.</p>
                        </div>
                        <a href="/profile/bookings" class="btn btn-sm btn-outline-primary flex-shrink-0">View All</a>
                    </div>

                    <?php if (!empty($upcomingBookingsList)): ?>
                        <?php foreach ($upcomingBookingsList as $booking): ?>
                            <div class="booking-preview-item">
                                <div class="booking-date-badge">
                                    <?php
                                    $date = new DateTime($booking['event_date'] ?? 'now');
                                    ?>
                                    <span class="date-month"><?= $date->format('M') ?></span>
                                    <span class="date-day"><?= $date->format('d') ?></span>
                                </div>
                                <div class="booking-preview-details">
                                    <div class="booking-event-name"><?= esc($booking['event_title'] ?? 'Event') ?></div>
                                    <div class="booking-meta">
                                        <i class="fas fa-user me-1"></i><?= esc($booking['customer_name'] ?? 'Customer') ?>
                                        <?php if (!empty($booking['location'])): ?>
                                            <span class="ms-2"><i class="fas fa-map-marker-alt me-1"></i><?= esc($booking['location']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="badge bg-success">Confirmed</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="dash-empty-state text-center py-4 px-3">
                            <i class="fas fa-calendar-check fa-3x text-muted mb-3 d-block" aria-hidden="true"></i>
                            <h6 class="fw-semibold">No upcoming bookings yet</h6>
                            <p class="text-muted small mb-4">When customers confirm dates with you, they will display here and on your calendar. Polished listings get booked faster.</p>
                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                                <a href="/service/create" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add a service</a>
                                <a href="/browse-services" class="btn btn-sm btn-outline-secondary">See how listings look</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 5. Quick Actions -->
                <div class="dash-card">
                    <h5 class="mb-1"><i class="fas fa-bolt text-warning me-2"></i>Quick Actions</h5>
                    <p class="text-muted small mb-3 d-none d-md-block">Shortcuts to the tasks vendors complete most often.</p>
                    <div class="row g-2">
                        <div class="col-6 col-md-4 col-lg">
                            <a href="/service/create" class="quick-action-btn">
                                <div class="action-icon bg-primary-light"><i class="fas fa-plus"></i></div>
                                <span class="action-label">Add New Service</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-4 col-lg">
                            <!-- TODO: Link to availability management page -->
                            <a href="/profile/calendar" class="quick-action-btn">
                                <div class="action-icon bg-info-light"><i class="fas fa-calendar-day"></i></div>
                                <span class="action-label">Update Availability</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-4 col-lg">
                            <a href="/profile/bookings" class="quick-action-btn">
                                <div class="action-icon bg-success-light"><i class="fas fa-list"></i></div>
                                <span class="action-label">View Bookings</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-4 col-lg">
                            <a href="/profile/calendar" class="quick-action-btn">
                                <div class="action-icon bg-warning-light"><i class="fas fa-calendar"></i></div>
                                <span class="action-label">View Calendar</span>
                            </a>
                        </div>
                        <div class="col-6 col-md-4 col-lg">
                            <a href="/profile/services" class="quick-action-btn">
                                <div class="action-icon bg-purple-light"><i class="fas fa-eye"></i></div>
                                <span class="action-label">My Services</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">

                <!-- 6. Service Health Checklist -->
                <div class="dash-card">
                    <h5 class="mb-1"><i class="fas fa-heartbeat text-danger me-2"></i>Service Health</h5>
                    <p class="text-muted small mb-3">Complete profiles with photos and clear policies convert more enquiries into paid bookings.</p>

                    <?php if (!empty($serviceHealthItems)): ?>
                        <?php foreach ($serviceHealthItems as $svc): ?>
                            <div class="mb-3">
                                <div class="fw-bold small mb-1"><?= esc($svc['title']) ?></div>
                                <div class="checklist-item">
                                    <div class="checklist-icon <?= $svc['has_description'] ? 'complete' : 'incomplete' ?>">
                                        <i class="fas <?= $svc['has_description'] ? 'fa-check' : 'fa-times' ?>"></i>
                                    </div>
                                    <span class="checklist-label">Completed profile</span>
                                </div>
                                <div class="checklist-item">
                                    <div class="checklist-icon <?= $svc['has_images'] ? 'complete' : 'incomplete' ?>">
                                        <i class="fas <?= $svc['has_images'] ? 'fa-check' : 'fa-times' ?>"></i>
                                    </div>
                                    <span class="checklist-label">Added service photos</span>
                                </div>
                                <div class="checklist-item">
                                    <div class="checklist-icon <?= $svc['has_price'] ? 'complete' : 'incomplete' ?>">
                                        <i class="fas <?= $svc['has_price'] ? 'fa-check' : 'fa-times' ?>"></i>
                                    </div>
                                    <span class="checklist-label">Set pricing</span>
                                </div>
                                <div class="checklist-item">
                                    <div class="checklist-icon <?= $svc['has_cancellation'] ? 'complete' : 'incomplete' ?>">
                                        <i class="fas <?= $svc['has_cancellation'] ? 'fa-check' : 'fa-times' ?>"></i>
                                    </div>
                                    <span class="checklist-label">Added cancellation policy</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="dash-empty-state text-center py-3 px-2">
                            <p class="text-muted small mb-3 mb-md-4">Publish at least one service to unlock completion checks for descriptions, imagery, pricing, and policies.</p>
                            <a href="/service/create" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add your first service</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 7. Recent Activity / Notifications -->
                <div class="dash-card">
                    <h5><i class="fas fa-bell text-info me-2"></i>Recent Activity</h5>

                    <?php if (!empty($pendingBookingsList)): ?>
                        <?php foreach (array_slice($pendingBookingsList, 0, 4) as $item): ?>
                            <div class="activity-item">
                                <div class="activity-dot bg-warning"></div>
                                <span class="activity-text">
                                    New booking request from <strong><?= esc($item['customer_name'] ?? 'Customer') ?></strong>
                                    for <?= esc($item['service_title'] ?? 'a service') ?>
                                </span>
                                <span class="activity-time">
                                    <!-- TODO: Use actual created_at timestamp -->
                                    Recent
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (empty($pendingBookingsList) && $unreadMessages == 0): ?>
                        <!-- Placeholder activity when no real data exists -->
                        <div class="activity-item">
                            <div class="activity-dot bg-info"></div>
                            <span class="activity-text">Welcome to your vendor dashboard!</span>
                            <span class="activity-time">Just now</span>
                        </div>
                        <div class="activity-item">
                            <div class="activity-dot bg-success"></div>
                            <span class="activity-text">Your account is set up and ready to go</span>
                            <span class="activity-time">Today</span>
                        </div>
                        <!-- TODO: Connect to real activity log (booking requests, quote accepts, service views, reviews) -->
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>
</main>

<?= $this->include('footer') ?>
