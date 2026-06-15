<?php
// ─────────────────────────────────────────────────────────
//  CyberShield — Configuration Template
//  Copy this file to config.php and fill in your values.
//  NEVER commit config.php to GitHub.
// ─────────────────────────────────────────────────────────

define('DB_HOST',    'localhost');
define('DB_NAME',    'your_database_name');   // e.g. u123456789_cybershield
define('DB_USER',    'your_database_user');   // e.g. u123456789_csuser
define('DB_PASS',    'your_database_password');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'CyberShield');
define('APP_URL',  'https://cybershield.techniki.in');   // no trailing slash
define('SESSION_NAME', 'cybershield_session');

define('LEVEL_THRESHOLDS', serialize([
    1 => 0, 2 => 200, 3 => 600, 4 => 1200,
    5 => 2200, 6 => 3500, 7 => 5500, 8 => 8000,
]));

define('LEVEL_NAMES', serialize([
    1 => 'Recruit', 2 => 'Analyst', 3 => 'Investigator',
    4 => 'Defender', 5 => 'Specialist', 6 => 'Expert',
    7 => 'Elite', 8 => 'CyberGuard',
]));

error_reporting(E_ALL);
ini_set('display_errors', 0);
