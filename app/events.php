<?php

function app_timezone()
{
    static $timezone = null;

    if (!$timezone) {
        $timezone = new DateTimeZone(config('app.timezone', 'Asia/Kathmandu'));
    }

    return $timezone;
}

function db_timezone()
{
    static $timezone = null;

    if (!$timezone) {
        $timezone = new DateTimeZone(config('app.db_timezone', 'UTC'));
    }

    return $timezone;
}

function parse_utc_datetime($value)
{
    if (!$value) {
        return null;
    }

    $parsed = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $value, db_timezone());
    if ($parsed instanceof DateTimeImmutable) {
        return $parsed;
    }

    try {
        return new DateTimeImmutable((string) $value, db_timezone());
    } catch (Exception $exception) {
        return null;
    }
}

function local_datetime_to_utc($localDatetime)
{
    $localDatetime = clean_input($localDatetime);
    if ($localDatetime === '') {
        return null;
    }

    $local = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $localDatetime, app_timezone());
    if (!$local || $local->format('Y-m-d\TH:i') !== $localDatetime) {
        return null;
    }

    return $local->setTimezone(db_timezone())->format('Y-m-d H:i:s');
}

function utc_to_local_datetime_input($utcDatetime)
{
    if (!$utcDatetime) {
        return '';
    }

    $date = parse_utc_datetime($utcDatetime);
    if (!$date) {
        return '';
    }

    return $date->setTimezone(app_timezone())->format('Y-m-d\TH:i');
}

function format_utc_to_local($utcDatetime, $format = 'M d, Y h:i A')
{
    if (!$utcDatetime) {
        return 'N/A';
    }

    $date = parse_utc_datetime($utcDatetime);
    if (!$date) {
        return 'N/A';
    }

    return $date->setTimezone(app_timezone())->format($format);
}

function format_event_date($eventDate, $format = 'M d, Y')
{
    $date = DateTimeImmutable::createFromFormat('Y-m-d', (string) $eventDate, app_timezone());
    if (!$date) {
        return (string) $eventDate;
    }

    return $date->format($format);
}

function registration_state($event)
{
    $openAt = parse_utc_datetime(isset($event['registration_open_at']) ? $event['registration_open_at'] : null);

    if (!$openAt) {
        return 'closed';
    }

    $closeAt = parse_utc_datetime(isset($event['registration_close_at']) ? $event['registration_close_at'] : null);
    $now = new DateTimeImmutable('now', db_timezone());

    if ($now < $openAt) {
        return 'upcoming';
    }

    if ($closeAt && $now > $closeAt) {
        return 'closed';
    }

    return 'open';
}

function registration_status_message($event)
{
    $state = registration_state($event);

    if ($state === 'open') {
        $closeAt = isset($event['registration_close_at']) ? $event['registration_close_at'] : null;
        if ($closeAt) {
            return 'Registration is open until ' . format_utc_to_local($closeAt) . '.';
        }

        return 'Registration is open now.';
    }

    if ($state === 'upcoming') {
        return 'Registration opens on ' . format_utc_to_local($event['registration_open_at']) . '.';
    }

    return 'Registration is closed.';
}

function is_registration_open($event)
{
    return registration_state($event) === 'open';
}

function events_has_is_active_column()
{
    static $hasColumn = null;

    if ($hasColumn !== null) {
        return $hasColumn;
    }

    try {
        $statement = db()->query("SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'events'
              AND COLUMN_NAME = 'is_active'");
        $hasColumn = ((int) $statement->fetchColumn()) > 0;
    } catch (PDOException $exception) {
        $hasColumn = false;
    }

    return $hasColumn;
}

function fetch_active_events()
{
    $sql = 'SELECT e.*, u.name AS created_by_name
        FROM events e
        LEFT JOIN users u ON u.id = e.created_by';

    if (events_has_is_active_column()) {
        $sql .= ' WHERE e.is_active = 1';
    }

    $sql .= ' ORDER BY e.event_date ASC, e.registration_open_at ASC';

    $statement = db()->query($sql);

    return $statement->fetchAll();
}

function fetch_event_by_id($eventId, $includeInactive = false)
{
    $eventId = (int) $eventId;

    $sql = 'SELECT e.*, u.name AS created_by_name
        FROM events e
        LEFT JOIN users u ON u.id = e.created_by
        WHERE e.id = :id';

    if (!$includeInactive && events_has_is_active_column()) {
        $sql .= ' AND e.is_active = 1';
    }

    $sql .= ' LIMIT 1';

    $statement = db()->prepare($sql);
    $statement->execute(['id' => $eventId]);

    $event = $statement->fetch();

    return $event ?: null;
}

function filter_current_month_events($events)
{
    $monthKey = (new DateTimeImmutable('now', app_timezone()))->format('Y-m');

    return array_values(array_filter($events, function ($event) use ($monthKey) {
        return isset($event['event_date']) && strpos((string) $event['event_date'], $monthKey) === 0;
    }));
}
