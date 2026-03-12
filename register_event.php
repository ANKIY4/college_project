<?php
require_once __DIR__ . '/app/bootstrap.php';

require_login();

if (!is_post_request()) {
    redirect('index.php');
}

$eventId = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
$fallback = 'event.php?id=' . $eventId;

require_valid_csrf($fallback);

if ($eventId <= 0) {
    flash('error', 'Invalid event selection.');
    redirect('index.php');
}

$event = fetch_event_by_id($eventId);
if (!$event) {
    flash('error', 'This event is not available.');
    redirect('index.php');
}

if (!is_registration_open($event)) {
    flash('error', 'Registration is not open for this event right now.');
    redirect($fallback);
}

$currentUser = current_user();
$formData = [
    'full_name' => clean_input(isset($_POST['full_name']) ? $_POST['full_name'] : ''),
    'email' => clean_input(isset($_POST['email']) ? $_POST['email'] : ''),
    'phone' => clean_input(isset($_POST['phone']) ? $_POST['phone'] : ''),
    'college' => clean_input(isset($_POST['college']) ? $_POST['college'] : ''),
];

$errors = validate_registration($formData);
if (!empty($errors)) {
    flash('error', reset($errors));
    redirect($fallback);
}

$existingStatement = db()->prepare('SELECT id FROM registrations WHERE event_id = :event_id AND user_id = :user_id LIMIT 1');
$existingStatement->execute([
    'event_id' => $eventId,
    'user_id' => (int) $currentUser['id'],
]);

if ($existingStatement->fetch()) {
    flash('info', 'You are already registered for this event.');
    redirect($fallback);
}

$insertStatement = db()->prepare('INSERT INTO registrations (event_id, user_id, full_name, email, phone, college)
    VALUES (:event_id, :user_id, :full_name, :email, :phone, :college)');

$insertStatement->execute([
    'event_id' => $eventId,
    'user_id' => (int) $currentUser['id'],
    'full_name' => $formData['full_name'],
    'email' => $formData['email'],
    'phone' => $formData['phone'],
    'college' => $formData['college'],
]);

flash('success', 'Registration submitted successfully.');
redirect($fallback);
