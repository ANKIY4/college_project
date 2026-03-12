<?php
require_once __DIR__ . '/../app/bootstrap.php';

require_admin();

$eventId = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;

if ($eventId > 0) {
    $event = fetch_event_by_id($eventId, true);

    if (!$event) {
        flash('error', 'Event not found for CSV export.');
        redirect('admin/dashboard.php');
    }

    $statement = db()->prepare('SELECT
            r.id,
            e.title AS event_title,
            e.event_date,
            r.full_name,
            r.email,
            r.phone,
            r.college,
            u.email AS account_email,
            r.submitted_at
        FROM registrations r
        INNER JOIN events e ON e.id = r.event_id
        INNER JOIN users u ON u.id = r.user_id
        WHERE r.event_id = :event_id
        ORDER BY r.submitted_at DESC');

    $statement->execute(['event_id' => $eventId]);
    $rows = $statement->fetchAll();

    $safeTitle = preg_replace('/[^a-z0-9]+/i', '_', strtolower($event['title']));
    $filename = 'registrations_' . trim($safeTitle, '_') . '_' . date('Ymd_His') . '.csv';
} else {
    $statement = db()->query('SELECT
            r.id,
            e.title AS event_title,
            e.event_date,
            r.full_name,
            r.email,
            r.phone,
            r.college,
            u.email AS account_email,
            r.submitted_at
        FROM registrations r
        INNER JOIN events e ON e.id = r.event_id
        INNER JOIN users u ON u.id = r.user_id
        ORDER BY e.event_date ASC, r.submitted_at DESC');

    $rows = $statement->fetchAll();
    $filename = 'registrations_all_events_' . date('Ymd_His') . '.csv';
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Registration ID',
    'Event Title',
    'Event Date',
    'Full Name',
    'Registration Email',
    'Account Email',
    'Phone',
    'College',
    'Submitted At (Asia/Kathmandu)',
]);

foreach ($rows as $row) {
    fputcsv($output, [
        $row['id'],
        $row['event_title'],
        format_event_date($row['event_date']),
        $row['full_name'],
        $row['email'],
        $row['account_email'],
        $row['phone'],
        $row['college'],
        format_utc_to_local($row['submitted_at']),
    ]);
}

fclose($output);
exit;
