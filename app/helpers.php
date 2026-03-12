<?php

function config($key, $default = null)
{
    $segments = explode('.', (string) $key);
    $value = isset($GLOBALS['config']) ? $GLOBALS['config'] : [];

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

function base_url($path = '')
{
    static $basePath = null;

    if ($basePath === null) {
        $configured = getenv('APP_BASE_URL');

        if (is_string($configured) && trim($configured) !== '') {
            $basePath = '/' . trim($configured, '/');
            if ($basePath === '/') {
                $basePath = '';
            }
        } else {
            $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
            $projectRoot = defined('PROJECT_ROOT') ? realpath(PROJECT_ROOT) : false;

            if ($documentRoot && $projectRoot && strpos($projectRoot, $documentRoot) === 0) {
                $relative = substr($projectRoot, strlen($documentRoot));
                $relative = str_replace('\\', '/', $relative);
                $basePath = rtrim($relative, '/');
            } else {
                $basePath = '';
            }
        }
    }

    $trimmedPath = ltrim((string) $path, '/');

    if ($trimmedPath === '') {
        return $basePath === '' ? '/' : $basePath . '/';
    }

    return ($basePath === '' ? '' : $basePath) . '/' . $trimmedPath;
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect($path)
{
    if (is_string($path) && preg_match('#^https?://#i', $path)) {
        header('Location: ' . $path);
        exit;
    }

    header('Location: ' . base_url($path));
    exit;
}

function is_post_request()
{
    return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';
}

function flash($type, $message)
{
    if (!isset($_SESSION['_flash']) || !is_array($_SESSION['_flash'])) {
        $_SESSION['_flash'] = [];
    }

    $_SESSION['_flash'][] = [
        'type' => (string) $type,
        'message' => (string) $message,
    ];
}

function pull_flashes()
{
    $messages = isset($_SESSION['_flash']) && is_array($_SESSION['_flash']) ? $_SESSION['_flash'] : [];
    unset($_SESSION['_flash']);

    return $messages;
}

function old_value($source, $key, $default = '')
{
    if (!is_array($source)) {
        return $default;
    }

    if (!array_key_exists($key, $source)) {
        return $default;
    }

    return trim((string) $source[$key]);
}
