<?php
require_once __DIR__ . '/db.php';

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (currentUser()['role'] !== 'admin') {
        header('Location: ' . APP_URL . '/dashboard.php');
        exit;
    }
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    if (!isset($_SESSION['user_cache'])) {
        $_SESSION['user_cache'] = queryOne('SELECT * FROM users WHERE id = ?', [$_SESSION['user_id']]);
    }
    return $_SESSION['user_cache'];
}

function refreshUserCache(): void {
    unset($_SESSION['user_cache']);
}

function login(string $username, string $password): bool {
    $user = queryOne('SELECT * FROM users WHERE username = ? OR email = ?', [$username, $username]);
    if (!$user || !password_verify($password, $user['password'])) return false;
    startSession();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    execute('UPDATE users SET last_active = CURDATE() WHERE id = ?', [$user['id']]);
    return true;
}

function logout(): void {
    startSession();
    session_unset();
    session_destroy();
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

function registerUser(string $username, string $email, string $password, string $fullName, string $dept): int|false {
    $existing = queryOne('SELECT id FROM users WHERE username = ? OR email = ?', [$username, $email]);
    if ($existing) return false;
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $initials = strtoupper(substr($fullName, 0, 1) . (strpos($fullName, ' ') !== false ? substr($fullName, strpos($fullName,' ')+1, 1) : ''));
    return execute(
        'INSERT INTO users (username, email, password, full_name, department, avatar_initials) VALUES (?,?,?,?,?,?)',
        [$username, $email, $hash, $fullName, $dept, $initials ?: strtoupper(substr($username,0,2))]
    );
}

function addXP(int $userId, int $amount): void {
    execute('UPDATE users SET total_xp = total_xp + ? WHERE id = ?', [$amount, $userId]);
    $user = queryOne('SELECT total_xp FROM users WHERE id = ?', [$userId]);
    $thresholds = unserialize(LEVEL_THRESHOLDS);
    $level = 1;
    foreach ($thresholds as $lvl => $xp) { if ($user['total_xp'] >= $xp) $level = $lvl; }
    execute('UPDATE users SET level = ? WHERE id = ?', [$level, $userId]);
    refreshUserCache();
}

function getLevelName(int $level): string {
    $names = unserialize(LEVEL_NAMES);
    return $names[$level] ?? 'CyberGuard';
}

function getNextLevelXP(int $currentXP): int {
    $thresholds = unserialize(LEVEL_THRESHOLDS);
    foreach ($thresholds as $lvl => $xp) {
        if ($currentXP < $xp) return $xp;
    }
    return max($thresholds) + 1000;
}

function awardBadge(int $userId, string $slug, string $name): void {
    try {
        execute('INSERT IGNORE INTO achievements (user_id, badge_slug, badge_name) VALUES (?,?,?)',
            [$userId, $slug, $name]);
    } catch (Exception $e) {}
}

function getUserBadges(int $userId): array {
    return query('SELECT * FROM achievements WHERE user_id = ? ORDER BY earned_at DESC', [$userId]);
}

function getBestScore(int $userId, int $gameId): ?array {
    return queryOne(
        'SELECT * FROM game_sessions WHERE user_id=? AND game_id=? AND completed=1 ORDER BY score DESC LIMIT 1',
        [$userId, $gameId]
    );
}
