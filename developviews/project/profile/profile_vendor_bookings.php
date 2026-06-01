<!-- Bookings Tab -->
<div class="tab-pane fade" id="bookings" role="tabpanel" aria-labelledby="bookings-tab">
            <h2>Bookings</h2>
            <?php if (!empty($bookingItems)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Event Date</th>
                            <th>Ceremony Type</th>
                            <th>Location</th>
                            <th>Service</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                            <th>Chat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookingItems as $item): ?>
                            <tr>
                                <td><?= esc($item['event_title']) ?></td>
                                <td><?= esc($item['event_date']) ?></td>
                                <td><?= esc($item['ceremony_type']) ?></td>
                                <td><?= esc($item['location']) ?></td>
                                <td><?= esc($item['service_title']) ?></td>
                                <td><?= esc($item['start_time']) ?></td>
                                <td><?= esc($item['end_time']) ?></td>
                                <td>£<?= esc($item['price']) ?></td>
                                <td>
                                    <span class="badge badge-pill <?= $item['status'] == 'accepted' ? 'badge-success' : ($item['status'] == 'pending' ? 'badge-warning' : 'badge-danger') ?>">
                                        <?= ucfirst($item['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="<?= base_url('profile/update-booking-status/' . $item['booking_item_id']) ?>">
                                        <?= csrf_field() ?>
                                        <select class="form-control" name="status">
                                            <option value="pending" <?= ($item['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                                            <option value="accepted" <?= ($item['status'] == 'accepted') ? 'selected' : '' ?>>Accept</option>
                                            <option value="rejected" <?= ($item['status'] == 'rejected') ? 'selected' : '' ?>>Reject</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm mt-2">Update</button>
                                    </form>
                                </td>
                                <td>
                                    <a href="<?= base_url('chat/start/' . $item['customer_id'] . '/' . $item['service_id']) ?>" class="chat-icon">
                                        <i class="fa fa-comments"></i>
                                        <?php if ($item['has_new_messages']): ?>
                                            <span class="notification-icon"></span>
                                        <?php endif; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No booking requests at this time.</p>
            <?php endif; ?>
        </div>