<?php
require_once __DIR__ . '/../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

function send_json_error($statusCode, $message)
{
    http_response_code((int) $statusCode);
    echo json_encode([
        'ok' => false,
        'message' => (string) $message,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SERVER['REQUEST_METHOD']) || strtoupper((string) $_SERVER['REQUEST_METHOD']) !== 'GET') {
    send_json_error(405, 'Method not allowed.');
}

$user = current_user();
if (!$user) {
    send_json_error(401, 'Authentication required.');
}

if (!isset($user['role']) || $user['role'] !== 'admin') {
    send_json_error(403, 'Admin access is required.');
}

$recentLimit = 20;

try {
    $totalStatement = db()->prepare('SELECT COUNT(*) AS total_registrations FROM registrations');
    $totalStatement->execute();
    $totalRegistrations = (int) $totalStatement->fetchColumn();

    if (events_has_is_active_column()) {
        $perEventStatement = db()->prepare('SELECT
                e.id,
                e.title,
                COUNT(r.id) AS registration_count
            FROM events e
            LEFT JOIN registrations r ON r.event_id = e.id
            GROUP BY e.id, e.title, e.event_date, e.registration_open_at, e.is_active
            HAVING e.is_active = :active OR COUNT(r.id) > 0
            ORDER BY e.event_date ASC, e.registration_open_at ASC, e.id ASC');
        $perEventStatement->execute(['active' => 1]);
    } else {
        $perEventStatement = db()->prepare('SELECT
                e.id,
                e.title,
                COUNT(r.id) AS registration_count
            FROM events e
            LEFT JOIN registrations r ON r.event_id = e.id
            GROUP BY e.id, e.title, e.event_date, e.registration_open_at
            ORDER BY e.event_date ASC, e.registration_open_at ASC, e.id ASC');
        $perEventStatement->execute();
    }
    $perEventRows = $perEventStatement->fetchAll();

    $recentStatement = db()->prepare('SELECT
            r.full_name,
            r.email,
            r.college,
            e.title AS event_title,
            r.submitted_at
        FROM registrations r
        INNER JOIN events e ON e.id = r.event_id
        ORDER BY r.submitted_at DESC
        LIMIT :recent_limit');
    $recentStatement->bindValue(':recent_limit', (int) $recentLimit, PDO::PARAM_INT);
    $recentStatement->execute();
    $recentRows = $recentStatement->fetchAll();

    $perEventCounts = array_map(function ($row) {
        return [
            'event_id' => (int) $row['id'],
            'event_title' => $row['title'],
            'registration_count' => (int) $row['registration_count'],
        ];
    }, $perEventRows);

    $recentRegistrations = array_map(function ($row) {
        return [
            'full_name' => $row['full_name'],
            'email' => $row['email'],
            'college' => $row['college'],
            'event_title' => $row['event_title'],
            'submitted_at_local' => format_utc_to_local($row['submitted_at']),
        ];
    }, $recentRows);

    echo json_encode([
        'ok' => true,
        'total_registrations' => $totalRegistrations,
        'per_event_counts' => $perEventCounts,
        'recent_registrations' => $recentRegistrations,
        'generated_at_local' => format_utc_to_local(gmdate('Y-m-d H:i:s')),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    send_json_error(500, 'Unable to load registration feed right now.');
}
