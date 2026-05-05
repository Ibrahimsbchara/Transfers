<?php
declare(strict_types=1);

define('DB_FILE', __DIR__ . '/transfers.sqlite');

function get_db(): PDO
{
    static $db = null;
    if ($db === null) {
        $db = new PDO('sqlite:' . DB_FILE);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->exec('PRAGMA foreign_keys = ON');
        _init_db($db);
    }
    return $db;
}

function _init_db(PDO $db): void
{
    $db->exec('
        CREATE TABLE IF NOT EXISTS transfer_dates (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            name        TEXT    NOT NULL UNIQUE,
            created_at  TEXT    DEFAULT CURRENT_TIMESTAMP
        )
    ');

    $db->exec('
        CREATE TABLE IF NOT EXISTS transfers (
            id                INTEGER PRIMARY KEY AUTOINCREMENT,
            date_id           INTEGER NOT NULL,
            vessel            TEXT    NOT NULL DEFAULT "",
            sender_name       TEXT    NOT NULL DEFAULT "",
            receiver_name     TEXT    NOT NULL DEFAULT "",
            amount            REAL    NOT NULL DEFAULT 0,
            bank_name         TEXT    NOT NULL DEFAULT "",
            account_number    TEXT    NOT NULL DEFAULT "",
            iban              TEXT    NOT NULL DEFAULT "",
            swift_code        TEXT    NOT NULL DEFAULT "",
            branch            TEXT    NOT NULL DEFAULT "",
            mobile_number     TEXT    NOT NULL DEFAULT "",
            cid               TEXT    NOT NULL DEFAULT "",
            place_of_delivery TEXT    NOT NULL DEFAULT "",
            sort_order        INTEGER NOT NULL DEFAULT 0,
            FOREIGN KEY (date_id) REFERENCES transfer_dates(id) ON DELETE CASCADE
        )
    ');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function h(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function set_flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function post(string $key, string $default = ''): string
{
    return trim($_POST[$key] ?? $default);
}

function post_upper(string $key): string
{
    return strtoupper(trim($_POST[$key] ?? ''));
}

function get_int(string $key): int
{
    return (int)($_GET[$key] ?? 0);
}

function post_int(string $key): int
{
    return (int)($_POST[$key] ?? 0);
}

function post_float(string $key): float
{
    return (float)($_POST[$key] ?? 0);
}
