<?php
// ============================================================
// Google API Configuration — AMS
// ============================================================

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';

define('GOOGLE_CREDENTIALS_PATH', __DIR__ . '/google_credentials.json');
define('GOOGLE_TOKEN_PATH',       __DIR__ . '/google_token.json');
define('GOOGLE_REDIRECT_URI',     getenv('GOOGLE_REDIRECT_URI') ?: "$protocol://$host/auth/google_auth.php");

// Scope yang dibutuhkan AMS
define('GOOGLE_SCOPES', [
    Google\Service\Gmail::GMAIL_SEND,
    Google\Service\Calendar::CALENDAR,
]);
