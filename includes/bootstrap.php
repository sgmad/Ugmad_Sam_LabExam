<?php
declare(strict_types=1);
session_start();

const DB_FILE = __DIR__ . '/../data/greenwallet.sqlite';

/** Shared SQLite connection; creates the users table on first run. */
function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    $pdo = new PDO('sqlite:' . DB_FILE, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        phone TEXT NOT NULL UNIQUE,
        email TEXT NOT NULL UNIQUE,
        pin_hash TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)');
    return $pdo;
}

function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }

function redirect(string $to): void { header('Location: ' . $to); exit; }

function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrf_check(): bool
{
    return hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''));
}

function flash(string $type, string $message): void { $_SESSION['flash'] = compact('type', 'message'); }

function take_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/** Accepts 09XXXXXXXXX, 639XXXXXXXXX or +639XXXXXXXXX; returns +639XXXXXXXXX or null. */
function normalize_phone(string $raw): ?string
{
    $digits = preg_replace('/[\s\-().]/', '', $raw);
    return preg_match('/^(?:\+63|63|0)(9\d{9})$/', $digits, $m) ? '+63' . $m[1] : null;
}

/** +639171234567 -> "+ 639* **** ****" */
function mask_phone(string $phone): string { return '+ ' . substr($phone, 1, 3) . '* **** ****'; }

/** Rules for choosing a new PIN. Returns an error message or null. */
function validate_new_pin(string $pin): ?string
{
    if ($pin === '') return 'PIN is required.';
    if (!preg_match('/^\d{6}$/', $pin)) return 'PIN must be exactly 6 digits.';
    if (preg_match('/^(\d)\1{5}$/', $pin)) return 'PIN is too easy to guess. Avoid repeating one digit.';
    return null;
}

function field_error(array $errors, string $key): string
{
    return isset($errors[$key]) ? '<p class="field-error" id="' . e($key) . '-error" role="alert">' . e($errors[$key]) . '</p>' : '';
}
