<?php
require_once __DIR__ . '/../app/bootstrap.php';

require_admin();

$statement = db()->query('SELECT e.*, u.name AS created_by_name,
    (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) AS registration_count
    FROM events e
    LEFT JOIN users u ON u.id = e.created_by
    ORDER BY e.event_date ASC, e.registration_open_at ASC');
$events = $statement->fetchAll();

$pageTitle = 'Admin Dashboard';
$extraScripts = ['public/js/admin-live-registrations.js'];
require __DIR__ . '/../templates/header.php';
?>

<section class="hero">
    <h1>Admin Dashboard</h1>
    <p>Manage event dates, registration windows, and download participant CSV files.</p>
    <div class="actions" style="margin-top: 12px;">
        <a class="btn btn-primary" href="<?php echo e(base_url('admin/event_form.php')); ?>">Create New Event</a>
        <a class="btn btn-outline" href="<?php echo e(base_url('admin/export_csv.php')); ?>">Download All Registrations CSV</a>
    </div>
</section>

<section class="card" id="live-registrations-panel" data-feed-url="<?php echo e(base_url('admin/registrations_feed.php')); ?>">
    <h2>Live Registrations</h2>
    <p class="meta">Auto-refreshes every 5 seconds.</p>

    <div class="inline-list" id="live-per-event-counts">
        <div>Loading event registration counts...</div>
    </div>

    <p><strong>Total registrations: <span id="live-total-registrations">0</span></strong></p>
    <p class="meta" id="live-registrations-updated">Last updated: --</p>
    <p class="meta" id="live-registrations-status">Waiting for first update...</p>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>College</th>
                    <th>Event</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody id="live-recent-registrations-body">
                <tr>
                    <td colspan="5" class="meta">Loading recent registrations...</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section>
    <h2 class="section-title">Events</h2>

    <?php if (empty($events)): ?>
        <div class="empty-state">No events created yet.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Registration Window</th>
                        <th>Status</th>
                        <th>Registrations</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <?php $state = registration_state($event); ?>
                        <tr>
                            <td>
                                <strong><?php echo e($event['title']); ?></strong><br>
                                <span class="meta"><?php echo e($event['venue']); ?></span>
                            </td>
                            <td><?php echo e(format_event_date($event['event_date'])); ?></td>
                            <td>
                                <?php echo e(format_utc_to_local($event['registration_open_at'])); ?><br>
                                <span class="meta">to <?php echo e($event['registration_close_at'] ? format_utc_to_local($event['registration_close_at']) : 'No end date'); ?></span>
                            </td>
                            <td><span class="badge badge-<?php echo e($state); ?>"><?php echo e(ucfirst($state)); ?></span></td>
                            <td><?php echo (int) $event['registration_count']; ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn btn-outline" href="<?php echo e(base_url('admin/event_form.php?id=' . (int) $event['id'])); ?>">Edit</a>
                                    <a class="btn btn-muted" href="<?php echo e(base_url('event.php?id=' . (int) $event['id'])); ?>">View</a>
                                    <a class="btn btn-primary" href="<?php echo e(base_url('admin/export_csv.php?event_id=' . (int) $event['id'])); ?>">CSV</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../templates/footer.php'; ?>
