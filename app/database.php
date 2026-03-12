<?php

function db()
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        config('db.host'),
        config('db.port'),
        config('db.name'),
        config('db.charset', 'utf8mb4')
    );

    try {
        $pdo = new PDO($dsn, config('db.user'), config('db.pass'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        http_response_code(500);
        echo '<h1>Database connection failed</h1>';
        echo '<p>Please verify your MySQL credentials in <code>app/config.php</code> or environment variables.</p>';
        exit;
    }

    $pdo->exec("SET time_zone = '+00:00'");

    return $pdo;
}
