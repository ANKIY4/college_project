<?php
require_once __DIR__ . '/app/bootstrap.php';

$eventId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($eventId <= 0) {
    flash('error', 'Invalid event selected.');
    redirect('index.php');
}

$event = fetch_event_by_id($eventId);
if (!$event) {
    flash('error', 'Event not found or inactive.');
    redirect('index.php');
}

$state = registration_state($event);
$currentUser = current_user();
$existingRegistration = null;

if ($currentUser) {
    $statement = db()->prepare('SELECT id, submitted_at FROM registrations WHERE event_id = :event_id AND user_id = :user_id LIMIT 1');
    $statement->execute([
        'event_id' => $eventId,
        'user_id' => (int) $currentUser['id'],
    ]);
    $existingRegistration = $statement->fetch();
}

$pageTitle = $event['title'] . ' | Event Details';
$extraScripts = ['public/js/form-validation.js'];
require __DIR__ . '/templates/header.php';
?>

<section class="card">
    <h1><?php echo e($event['title']); ?></h1>
    <div class="inline-list">
        <div><strong>Event Date:</strong> <?php echo e(format_event_date($event['event_date'])); ?></div>
        <div><strong>Venue:</strong> <?php echo e($event['venue']); ?></div>
        <div><strong>Registration Opens:</strong> <?php echo e(format_utc_to_local($event['registration_open_at'])); ?></div>
        <div><strong>Registration Closes:</strong> <?php echo e($event['registration_close_at'] ? format_utc_to_local($event['registration_close_at']) : 'Not specified'); ?></div>
    </div>
    <?php if (!empty($event['description'])): ?>
        <p><?php echo nl2br(e($event['description'])); ?></p>
    <?php endif; ?>

    <p><span class="badge badge-<?php echo e($state); ?>"><?php echo e(ucfirst($state)); ?></span> <?php echo e(registration_status_message($event)); ?></p>
</section>

<section class="card">
    <h2>Registration</h2>

    <?php if ($state !== 'open'): ?>
        <div class="alert alert-warning">The registration form is currently unavailable for this event.</div>
    <?php elseif (!$currentUser): ?>
        <div class="alert alert-info">
            Please <a href="<?php echo e(base_url('login.php')); ?>">log in</a> or <a href="<?php echo e(base_url('signup.php')); ?>">create an account</a>
            to register for this event.
        </div>
    <?php elseif ($existingRegistration): ?>
        <div class="alert alert-success">
            You are already registered for this event.
            Submitted on <?php echo e(format_utc_to_local($existingRegistration['submitted_at'])); ?>.
        </div>
    <?php else: ?>
        <form method="POST" action="<?php echo e(base_url('register_event.php')); ?>" class="form-card" data-validate-type="registration" novalidate>
            <?php echo csrf_field(); ?>
            <input type="hidden" name="event_id" value="<?php echo (int) $event['id']; ?>">

            <div class="form-row">
                <label for="full_name">Full Name</label>
                <input id="full_name" name="full_name" type="text" required value="<?php echo e($currentUser['name']); ?>">
                <small class="field-error" data-error-for="full_name"></small>
            </div>

            <div class="form-row">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required value="<?php echo e($currentUser['email']); ?>">
                <small class="field-error" data-error-for="email"></small>
            </div>

            <div class="form-row">
                <label for="phone">Phone</label>
                <input id="phone" name="phone" type="text" required placeholder="e.g. +9779812345678">
                <small class="field-error" data-error-for="phone"></small>
            </div>

            <div class="form-row">
                <label for="college">College</label>
                <input id="college" name="college" type="text" required placeholder="Your college name">
                <small class="field-error" data-error-for="college"></small>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Submit Registration</button>
                <a class="btn btn-muted" href="<?php echo e(base_url('index.php')); ?>">Back to Events</a>
            </div>
        </form>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/templates/footer.php'; ?>
