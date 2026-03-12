<?php
require_once __DIR__ . '/../app/bootstrap.php';

require_admin();

$eventId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $eventId > 0;
$event = null;

if ($isEdit) {
    $event = fetch_event_by_id($eventId, true);
    if (!$event) {
        flash('error', 'Event not found.');
        redirect('admin/dashboard.php');
    }
}

$formData = [
    'title' => $event ? $event['title'] : '',
    'description' => $event ? $event['description'] : '',
    'venue' => $event ? $event['venue'] : '',
    'event_date' => $event ? $event['event_date'] : '',
    'registration_open_at' => $event ? utc_to_local_datetime_input($event['registration_open_at']) : '',
    'registration_close_at' => $event ? utc_to_local_datetime_input($event['registration_close_at']) : '',
    'is_active' => $event ? (int) $event['is_active'] : 1,
];
$errors = [];

if (is_post_request()) {
    $fallback = 'admin/event_form.php' . ($isEdit ? '?id=' . $eventId : '');
    require_valid_csrf($fallback);

    $formData['title'] = clean_input(isset($_POST['title']) ? $_POST['title'] : '');
    $formData['description'] = clean_input(isset($_POST['description']) ? $_POST['description'] : '');
    $formData['venue'] = clean_input(isset($_POST['venue']) ? $_POST['venue'] : '');
    $formData['event_date'] = clean_input(isset($_POST['event_date']) ? $_POST['event_date'] : '');
    $formData['registration_open_at'] = clean_input(isset($_POST['registration_open_at']) ? $_POST['registration_open_at'] : '');
    $formData['registration_close_at'] = clean_input(isset($_POST['registration_close_at']) ? $_POST['registration_close_at'] : '');
    $formData['is_active'] = isset($_POST['is_active']) ? 1 : 0;

    $errors = validate_event($formData);

    $openUtc = local_datetime_to_utc($formData['registration_open_at']);
    $closeUtc = $formData['registration_close_at'] !== '' ? local_datetime_to_utc($formData['registration_close_at']) : null;

    if ($formData['registration_open_at'] !== '' && !$openUtc) {
        $errors['registration_open_at'] = 'Registration open datetime is invalid.';
    }

    if ($formData['registration_close_at'] !== '' && !$closeUtc) {
        $errors['registration_close_at'] = 'Registration close datetime is invalid.';
    }

    if ($openUtc && $closeUtc && strtotime($closeUtc) <= strtotime($openUtc)) {
        $errors['registration_close_at'] = 'Registration close datetime must be after open datetime.';
    }

    if (empty($errors)) {
        if ($isEdit) {
            $statement = db()->prepare('UPDATE events
                SET title = :title,
                    description = :description,
                    venue = :venue,
                    event_date = :event_date,
                    registration_open_at = :registration_open_at,
                    registration_close_at = :registration_close_at,
                    is_active = :is_active
                WHERE id = :id');

            $statement->execute([
                'title' => $formData['title'],
                'description' => $formData['description'],
                'venue' => $formData['venue'],
                'event_date' => $formData['event_date'],
                'registration_open_at' => $openUtc,
                'registration_close_at' => $closeUtc,
                'is_active' => $formData['is_active'],
                'id' => $eventId,
            ]);

            flash('success', 'Event updated successfully.');
        } else {
            $currentUser = current_user();
            $statement = db()->prepare('INSERT INTO events
                (title, description, venue, event_date, registration_open_at, registration_close_at, is_active, created_by)
                VALUES
                (:title, :description, :venue, :event_date, :registration_open_at, :registration_close_at, :is_active, :created_by)');

            $statement->execute([
                'title' => $formData['title'],
                'description' => $formData['description'],
                'venue' => $formData['venue'],
                'event_date' => $formData['event_date'],
                'registration_open_at' => $openUtc,
                'registration_close_at' => $closeUtc,
                'is_active' => $formData['is_active'],
                'created_by' => (int) $currentUser['id'],
            ]);

            flash('success', 'Event created successfully.');
        }

        redirect('admin/dashboard.php');
    }
}

$pageTitle = $isEdit ? 'Edit Event' : 'Create Event';
$extraScripts = ['public/js/form-validation.js'];
require __DIR__ . '/../templates/header.php';
?>

<section class="card">
    <h1><?php echo e($isEdit ? 'Edit Event' : 'Create Event'); ?></h1>
    <p class="meta">All date inputs are interpreted in Asia/Kathmandu and stored in UTC.</p>

    <form method="POST" action="<?php echo e(base_url('admin/event_form.php' . ($isEdit ? '?id=' . $eventId : ''))); ?>" data-validate-type="event" novalidate>
        <?php echo csrf_field(); ?>

        <div class="form-row">
            <label for="title">Event Title</label>
            <input id="title" name="title" type="text" value="<?php echo e($formData['title']); ?>" class="<?php echo isset($errors['title']) ? 'input-error' : ''; ?>" required>
            <small class="field-error" data-error-for="title"><?php echo isset($errors['title']) ? e($errors['title']) : ''; ?></small>
        </div>

        <div class="form-row">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?php echo e($formData['description']); ?></textarea>
        </div>

        <div class="form-row">
            <label for="venue">Venue</label>
            <input id="venue" name="venue" type="text" value="<?php echo e($formData['venue']); ?>" class="<?php echo isset($errors['venue']) ? 'input-error' : ''; ?>" required>
            <small class="field-error" data-error-for="venue"><?php echo isset($errors['venue']) ? e($errors['venue']) : ''; ?></small>
        </div>

        <div class="form-row">
            <label for="event_date">Event Date</label>
            <input id="event_date" name="event_date" type="date" value="<?php echo e($formData['event_date']); ?>" class="<?php echo isset($errors['event_date']) ? 'input-error' : ''; ?>" required>
            <small class="field-error" data-error-for="event_date"><?php echo isset($errors['event_date']) ? e($errors['event_date']) : ''; ?></small>
        </div>

        <div class="form-row">
            <label for="registration_open_at">Registration Opens At</label>
            <input id="registration_open_at" name="registration_open_at" type="datetime-local" value="<?php echo e($formData['registration_open_at']); ?>" class="<?php echo isset($errors['registration_open_at']) ? 'input-error' : ''; ?>" required>
            <small class="field-error" data-error-for="registration_open_at"><?php echo isset($errors['registration_open_at']) ? e($errors['registration_open_at']) : ''; ?></small>
        </div>

        <div class="form-row">
            <label for="registration_close_at">Registration Closes At (optional)</label>
            <input id="registration_close_at" name="registration_close_at" type="datetime-local" value="<?php echo e($formData['registration_close_at']); ?>" class="<?php echo isset($errors['registration_close_at']) ? 'input-error' : ''; ?>">
            <small class="field-error" data-error-for="registration_close_at"><?php echo isset($errors['registration_close_at']) ? e($errors['registration_close_at']) : ''; ?></small>
        </div>

        <div class="form-row">
            <label>
                <input type="checkbox" name="is_active" value="1" <?php echo $formData['is_active'] ? 'checked' : ''; ?> style="width:auto; margin-right:8px;">
                Event is active and visible on user pages
            </label>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary"><?php echo e($isEdit ? 'Update Event' : 'Create Event'); ?></button>
            <a class="btn btn-muted" href="<?php echo e(base_url('admin/dashboard.php')); ?>">Cancel</a>
        </div>
    </form>
</section>

<?php require __DIR__ . '/../templates/footer.php'; ?>
