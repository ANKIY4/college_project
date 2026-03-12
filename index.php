<?php
require_once __DIR__ . '/app/bootstrap.php';

$events = fetch_active_events();
$thisMonthEvents = filter_current_month_events($events);

$pageTitle = 'Events | ' . config('app.name', 'Event Management Portal');
require __DIR__ . '/templates/header.php';
?>

<section class="hero">
    <h1>College Event Management</h1>
    <p>
        Discover this month’s events, check registration opening dates, and register as soon as the form goes live.
        Admins can publish event schedules and export registrations in CSV format.
    </p>
</section>

<section>
    <h2 class="section-title">Events This Month</h2>

    <?php if (empty($thisMonthEvents)): ?>
        <div class="empty-state">No events are scheduled for this month yet. Please check back soon.</div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($thisMonthEvents as $event): ?>
                <?php $state = registration_state($event); ?>
                <article class="card">
                    <h3><?php echo e($event['title']); ?></h3>
                    <p class="meta">Date: <?php echo e(format_event_date($event['event_date'])); ?></p>
                    <p class="meta">Venue: <?php echo e($event['venue']); ?></p>
                    <span class="badge badge-<?php echo e($state); ?>"><?php echo e(ucfirst($state)); ?></span>
                    <p><?php echo e(registration_status_message($event)); ?></p>
                    <a class="btn btn-primary" href="<?php echo e(base_url('event.php?id=' . (int) $event['id'])); ?>">View Event</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section>
    <h2 class="section-title">All Active Events</h2>

    <?php if (empty($events)): ?>
        <div class="empty-state">No active events are available right now.</div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($events as $event): ?>
                <?php $state = registration_state($event); ?>
                <article class="card">
                    <h3><?php echo e($event['title']); ?></h3>
                    <p class="meta"><?php echo e(format_event_date($event['event_date'])); ?> • <?php echo e($event['venue']); ?></p>
                    <span class="badge badge-<?php echo e($state); ?>"><?php echo e(ucfirst($state)); ?></span>
                    <p><?php echo e(registration_status_message($event)); ?></p>
                    <a class="btn btn-outline" href="<?php echo e(base_url('event.php?id=' . (int) $event['id'])); ?>">Details</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/templates/footer.php'; ?>
