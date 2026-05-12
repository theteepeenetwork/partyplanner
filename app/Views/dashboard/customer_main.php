<?= $this->include('header') ?>

<main class="page-main">
<div class="dashboard-wrapper">
    <div class="container">

        <?= $this->include('dashboard/_customer_tabs') ?>

        <?= $this->include('dashboard/_flash_alerts') ?>

        <!-- 1. Welcome Section -->
        <div class="mb-4">
            <h3 class="mb-2">Welcome back, <?= esc($user['name']) ?> 👋</h3>
            <p class="dash-page-lead mb-3">This is your private planning hub. Bookings, messages, and payment summaries stay in one place so you always know what is next.</p>
            <?php
            // Find next upcoming event
            $nextEvent = null;
            $daysUntil = null;
            foreach ($events as $evt) {
                if (!empty($evt['date'])) {
                    $eventDate = new DateTime($evt['date']);
                    $today = new DateTime('today');
                    if ($eventDate >= $today) {
                        $diff = $today->diff($eventDate)->days;
                        if ($nextEvent === null || $diff < $daysUntil) {
                            $nextEvent = $evt;
                            $daysUntil = $diff;
                        }
                    }
                }
            }
            ?>
            <?php if ($nextEvent): ?>
                <p class="text-muted">
                    <i class="fas fa-calendar-alt me-1"></i>
                    Your next event <strong>"<?= esc($nextEvent['title']) ?>"</strong> is in
                    <strong><?= $daysUntil ?> day<?= $daysUntil != 1 ? 's' : '' ?></strong>
                </p>
            <?php elseif (empty($events)): ?>
                <p class="text-muted mb-0">Add a date and a few details first, then browse trusted vendors and request bookings. Nothing is final until you confirm with the vendor.</p>
            <?php else: ?>
                <p class="text-muted mb-0">Here is a snapshot of your events, requests, and spend. Numbers update as vendors respond and you pay deposits.</p>
            <?php endif; ?>
        </div>

        <!-- 4. Booking Summary Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-light mx-auto"><i class="fas fa-clock"></i></div>
                    <div class="stat-value"><?= $totalPendingRequests ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="stat-card">
                    <div class="stat-icon bg-success-light mx-auto"><i class="fas fa-check"></i></div>
                    <div class="stat-value"><?= $totalAccepted ?></div>
                    <div class="stat-label">Accepted</div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="stat-card">
                    <div class="stat-icon bg-info-light mx-auto"><i class="fas fa-pound-sign"></i></div>
                    <!-- TODO: Calculate awaiting payment from payments table -->
                    <div class="stat-value"><?= $totalAwaitingPayment ?></div>
                    <div class="stat-label">Awaiting Payment</div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-light mx-auto"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-value"><?= $totalConfirmed ?></div>
                    <div class="stat-label">Confirmed</div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="stat-card">
                    <div class="stat-icon bg-danger-light mx-auto"><i class="fas fa-times-circle"></i></div>
                    <div class="stat-value"><?= $totalDeclined ?></div>
                    <div class="stat-label">Declined</div>
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

                    <?php if ($totalAccepted > 0): $hasAttention = true; ?>
                        <div class="attention-card border-success">
                            <div class="attention-icon bg-success-light"><i class="fas fa-check-circle"></i></div>
                            <div class="attention-content">
                                <div class="attention-title">Vendor Accepted a Booking</div>
                                <p class="attention-desc"><?= $totalAccepted ?> booking<?= $totalAccepted > 1 ? 's have' : ' has' ?> been accepted — review and confirm</p>
                            </div>
                            <div class="attention-action">
                                <!-- TODO: Link to bookings management -->
                                <a href="/profile" class="btn btn-sm btn-outline-success">Review</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($totalDeclined > 0): $hasAttention = true; ?>
                        <div class="attention-card border-danger">
                            <div class="attention-icon bg-danger-light"><i class="fas fa-times-circle"></i></div>
                            <div class="attention-content">
                                <div class="attention-title">Vendor Declined a Request</div>
                                <p class="attention-desc"><?= $totalDeclined ?> request<?= $totalDeclined > 1 ? 's were' : ' was' ?> declined — find alternative services</p>
                            </div>
                            <div class="attention-action">
                                <a href="/browse-services" class="btn btn-sm btn-outline-danger">Browse</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($unreadMessages > 0): $hasAttention = true; ?>
                        <div class="attention-card border-info">
                            <div class="attention-icon bg-info-light"><i class="fas fa-envelope"></i></div>
                            <div class="attention-content">
                                <div class="attention-title">Messages from Vendors</div>
                                <p class="attention-desc"><?= $unreadMessages ?> unread message<?= $unreadMessages > 1 ? 's' : '' ?></p>
                            </div>
                            <div class="attention-action">
                                <a href="/profile/messages" class="btn btn-sm btn-outline-info">View</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($events)): $hasAttention = true; ?>
                        <div class="attention-card border-primary">
                            <div class="attention-icon bg-primary-light"><i class="fas fa-calendar-plus"></i></div>
                            <div class="attention-content">
                                <div class="attention-title">No Events Created Yet</div>
                                <p class="attention-desc">Create your first event to start planning and booking services</p>
                            </div>
                            <div class="attention-action">
                                <a href="/event/create" class="btn btn-sm btn-outline-primary">Create Event</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- TODO: Add attention items for: deposit/payment needed, booking awaiting confirmation,
                         quote expiring soon, balance due soon, missing event details -->

                    <?php if (!$hasAttention): ?>
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                            <p class="text-muted mb-0">You're all caught up! Nothing needs your attention right now.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 2. Event Overview Cards -->
                <div class="dash-card">
                    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center gap-2 mb-3">
                        <div>
                            <h5 class="mb-0"><i class="fas fa-calendar text-primary me-2"></i>My Events</h5>
                            <p class="text-muted small mb-0 mt-1">Each event holds your bookings and budget for that celebration.</p>
                        </div>
                        <a href="/event/create" class="btn btn-sm btn-primary flex-shrink-0"><i class="fas fa-plus me-1"></i>Create Event</a>
                    </div>

                    <?php if (!empty($events)): ?>
                        <?php foreach ($events as $event): ?>
                            <div class="event-overview-card">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="event-title"><?= esc($event['title']) ?></div>
                                        <div class="event-meta mt-1">
                                            <?php if (!empty($event['date'])): ?>
                                                <span><i class="fas fa-calendar-alt me-1"></i><?= date('d M Y', strtotime($event['date'])) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($event['location'])): ?>
                                                <span class="ms-3"><i class="fas fa-map-marker-alt me-1"></i><?= esc($event['location']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($event['category'])): ?>
                                                <span class="ms-3"><i class="fas fa-tag me-1"></i><?= esc($event['category']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-info"><?= $event['servicesBooked'] ?> service<?= $event['servicesBooked'] != 1 ? 's' : '' ?> booked</span>
                                            <?php if ($event['totalCost'] > 0): ?>
                                                <span class="badge bg-secondary ms-1">Est. £<?= number_format($event['totalCost'], 2) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end mt-2 mt-md-0">
                                        <!-- TODO: Link to individual event view page -->
                                        <a href="/browse-services" class="btn btn-sm btn-outline-primary me-1">Add Services</a>
                                        <!-- TODO: Link to event management page -->
                                        <a href="/profile" class="btn btn-sm btn-primary">View Event</a>
                                    </div>
                                </div>

                                <!-- Planning progress bar -->
                                <?php
                                $maxServices = 8;
                                $progress = min(100, ($event['servicesBooked'] / $maxServices) * 100);
                                ?>
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Planning progress</span>
                                        <span><?= $event['servicesBooked'] ?>/<?= $maxServices ?> key services</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $progress ?>%" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="dash-empty-state text-center py-4 px-3">
                            <i class="fas fa-calendar-plus fa-3x text-muted mb-3 d-block" aria-hidden="true"></i>
                            <h5 class="fw-semibold">No events yet</h5>
                            <p class="text-muted mb-4">Create an event to save your date, guest count, and venue—then add services and track vendor responses in one place.</p>
                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center align-items-stretch align-items-sm-center">
                                <a href="/event/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Create your first event</a>
                                <a href="/browse-services" class="btn btn-outline-primary">Browse services</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 5. Planning Progress Tracker -->
                <div class="dash-card">
                    <h5 class="mb-1"><i class="fas fa-tasks text-success me-2"></i>Planning Progress</h5>
                    <p class="text-muted small mb-3">A simple checklist so big items like catering and photos do not slip through the cracks.</p>

                    <?php if (!empty($events)): ?>
                        <?php
                        // Build set of booked category IDs across all events
                        $bookedCategoryIds = [];
                        foreach ($events as $evt) {
                            foreach ($evt['bookingItems'] as $bi) {
                                if (!empty($bi['category_id'])) {
                                    $bookedCategoryIds[$bi['category_id']] = true;
                                }
                            }
                        }

                        // Define key service categories for event planning
                        $planningCategories = [
                            ['icon' => 'fa-utensils', 'label' => 'Catering'],
                            ['icon' => 'fa-camera', 'label' => 'Photography'],
                            ['icon' => 'fa-music', 'label' => 'Entertainment'],
                            ['icon' => 'fa-car', 'label' => 'Transport'],
                            ['icon' => 'fa-paint-brush', 'label' => 'Decorations'],
                            ['icon' => 'fa-spa', 'label' => 'Hair & Makeup'],
                            ['icon' => 'fa-birthday-cake', 'label' => 'Cakes & Desserts'],
                            ['icon' => 'fa-envelope', 'label' => 'Stationery'],
                        ];

                        $bookedCount = 0;
                        foreach ($events as $evt) {
                            $bookedCount += $evt['servicesBooked'];
                        }
                        ?>
                        <p class="text-muted small mb-3">
                            <?= $bookedCount ?>/<?= count($planningCategories) ?> key service categories covered
                        </p>

                        <?php foreach ($planningCategories as $idx => $cat): ?>
                            <?php
                            // TODO: Match categories by name/id from real booking data instead of placeholder status
                            $isBooked = $idx < $bookedCount;
                            $statusClass = $isBooked ? 'booked' : 'not-started';
                            $statusIcon = $isBooked ? 'fa-check' : 'fa-circle';
                            $statusLabel = $isBooked ? 'Booked' : 'Not started';
                            ?>
                            <div class="progress-tracker-item">
                                <div class="progress-tracker-icon <?= $statusClass ?>">
                                    <i class="fas <?= $statusIcon ?>"></i>
                                </div>
                                <span class="checklist-label"><i class="fas <?= $cat['icon'] ?> me-2 text-muted"></i><?= $cat['label'] ?></span>
                                <span class="badge <?= $isBooked ? 'bg-success' : 'bg-light text-muted' ?> ms-auto"><?= $statusLabel ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="dash-empty-state text-center py-3 px-2">
                            <p class="text-muted small mb-3 mb-md-4">Start with one event; we will help you tick off the essentials as you book vendors.</p>
                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                                <a href="/event/create" class="btn btn-sm btn-primary">Create an event</a>
                                <a href="/browse-services" class="btn btn-sm btn-outline-secondary">Browse services</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">

                <!-- 6. Recommended Next Services -->
                <div class="dash-card">
                    <h5 class="mb-1"><i class="fas fa-lightbulb text-warning me-2"></i>Recommended Services</h5>
                    <p class="text-muted small mb-3">Popular starting points from the marketplace—explore more categories anytime.</p>

                    <!-- TODO: Generate recommendations based on event type and missing service categories -->
                    <div class="attention-card border-info mb-2">
                        <div class="attention-icon bg-info-light"><i class="fas fa-camera"></i></div>
                        <div class="attention-content">
                            <div class="attention-title">Add Photography</div>
                            <p class="attention-desc">Capture every special moment</p>
                        </div>
                        <a href="/browse-services?category=2" class="btn btn-sm btn-outline-info">Browse</a>
                    </div>
                    <div class="attention-card border-success mb-2">
                        <div class="attention-icon bg-success-light"><i class="fas fa-utensils"></i></div>
                        <div class="attention-content">
                            <div class="attention-title">Book Catering</div>
                            <p class="attention-desc">Popular for events near you</p>
                        </div>
                        <a href="/browse-services?category=1" class="btn btn-sm btn-outline-success">Browse</a>
                    </div>
                    <div class="attention-card border-warning mb-2">
                        <div class="attention-icon bg-warning-light"><i class="fas fa-music"></i></div>
                        <div class="attention-content">
                            <div class="attention-title">Add Entertainment</div>
                            <p class="attention-desc">Customers also booked DJs</p>
                        </div>
                        <a href="/browse-services?category=3" class="btn btn-sm btn-outline-warning">Browse</a>
                    </div>
                </div>

                <!-- 8. Payment Snapshot -->
                <div class="dash-card">
                    <h5 class="mb-1"><i class="fas fa-credit-card text-primary me-2"></i>Payment Summary</h5>
                    <p class="text-muted small mb-3">Figures reflect recorded deposits and estimates. You will confirm balances with each vendor before the event.</p>
                    <div class="mb-2 d-flex justify-content-between">
                        <span class="text-muted small">Deposits Paid</span>
                        <!-- TODO: Pull real deposit amounts from payments table -->
                        <span class="fw-bold">£<?= number_format($depositsPaid, 2) ?></span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span class="text-muted small">Outstanding Deposits</span>
                        <!-- TODO: Calculate outstanding deposits -->
                        <span class="fw-bold">£0.00</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span class="text-muted small">Remaining Balance</span>
                        <!-- TODO: Calculate remaining balance from total cost minus payments -->
                        <span class="fw-bold">£<?= number_format(max(0, $totalSpend - $depositsPaid), 2) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small fw-bold">Total Event Spend</span>
                        <span class="fw-bold text-primary">£<?= number_format($totalSpend, 2) ?></span>
                    </div>
                </div>

                <!-- 7. Favourites / Saved Services -->
                <div class="dash-card">
                    <h5 class="mb-1"><i class="fas fa-heart text-danger me-2"></i>Saved Services</h5>
                    <p class="text-muted small mb-3">Shortlist vendors you love and come back when you are ready to book.</p>
                    <!-- TODO: Implement favourites/saved services functionality with a favourites table -->
                    <div class="dash-empty-state text-center py-3 px-2">
                        <i class="fas fa-heart fa-2x text-muted mb-2 d-block" aria-hidden="true"></i>
                        <p class="text-muted small mb-3">Nothing saved yet. Tap the heart on a service page to keep it here.</p>
                        <a href="/browse-services" class="btn btn-sm btn-primary">Browse services</a>
                    </div>
                </div>

                <!-- 9. Messages Preview -->
                <div class="dash-card">
                    <h5 class="mb-1"><i class="fas fa-comments text-info me-2"></i>Messages</h5>
                    <p class="text-muted small mb-3 d-none d-md-block">Questions and updates from vendors appear here after you request or confirm a booking.</p>

                    <?php if (!empty($recentMessages)): ?>
                        <?php foreach ($recentMessages as $msg): ?>
                            <div class="message-preview-item">
                                <div class="message-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="message-content">
                                    <div class="message-sender"><?= esc($msg['sender_name'] ?? 'Vendor') ?></div>
                                    <div class="message-snippet"><?= esc(substr($msg['message'] ?? '', 0, 50)) ?></div>
                                </div>
                                <div>
                                    <div class="message-time"><?= date('d M', strtotime($msg['created_at'] ?? 'now')) ?></div>
                                    <?php if (empty($msg['is_read'])): ?>
                                        <span class="unread-dot"></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="dash-empty-state text-center py-3 px-2">
                            <i class="fas fa-comments fa-2x text-muted mb-2 d-block d-md-none" aria-hidden="true"></i>
                            <p class="text-muted small mb-3">No conversations yet. Message a vendor from a booking or service page once you have started planning.</p>
                            <div class="d-flex flex-column gap-2">
                                <a href="/browse-services" class="btn btn-sm btn-primary">Browse services</a>
                                <a href="/profile/messages" class="btn btn-sm btn-outline-secondary">Open inbox</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 10. Recent Activity -->
                <div class="dash-card">
                    <h5><i class="fas fa-bell text-info me-2"></i>Recent Activity</h5>

                    <!-- TODO: Connect to real activity log tracking user actions -->
                    <?php if (!empty($events)): ?>
                        <?php foreach (array_slice($events, 0, 3) as $evt): ?>
                            <div class="activity-item">
                                <div class="activity-dot bg-primary"></div>
                                <span class="activity-text">Event "<strong><?= esc($evt['title']) ?></strong>" created</span>
                                <span class="activity-time">Recent</span>
                            </div>
                            <?php if ($evt['servicesBooked'] > 0): ?>
                                <div class="activity-item">
                                    <div class="activity-dot bg-success"></div>
                                    <span class="activity-text"><?= $evt['servicesBooked'] ?> service<?= $evt['servicesBooked'] > 1 ? 's' : '' ?> booked for <?= esc($evt['title']) ?></span>
                                    <span class="activity-time">Recent</span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="activity-item">
                            <div class="activity-dot bg-info"></div>
                            <span class="activity-text">Welcome to your event planning dashboard!</span>
                            <span class="activity-time">Just now</span>
                        </div>
                        <div class="activity-item">
                            <div class="activity-dot bg-success"></div>
                            <span class="activity-text">Your account is set up and ready to go</span>
                            <span class="activity-time">Today</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>
</main>

<?= $this->include('footer') ?>
