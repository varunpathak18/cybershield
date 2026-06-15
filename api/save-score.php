<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) { echo json_encode(['error'=>'Not authenticated']); exit; }

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || !isset($data['game_slug'])) {
    echo json_encode(['error' => 'Invalid data']); exit;
}

$userId = $_SESSION['user_id'];
$slug   = preg_replace('/[^a-z0-9-]/', '', $data['game_slug']);
$score  = (int)($data['score'] ?? 0);
$max    = (int)($data['max_score'] ?? 100);
$xp     = (int)($data['xp_earned'] ?? 0);
$pct    = round((float)($data['percentage'] ?? ($max>0 ? $score/$max*100 : 0)), 2);
$hints  = (int)($data['hints_used'] ?? 0);
$time   = (int)($data['time_taken'] ?? 0);

$game = queryOne('SELECT * FROM games WHERE slug = ?', [$slug]);
if (!$game) { echo json_encode(['error' => 'Game not found']); exit; }

// Insert session
$sessionId = execute(
    'INSERT INTO game_sessions (user_id, game_id, score, max_score, percentage, xp_earned, hints_used, time_taken, completed, completed_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())',
    [$userId, $game['id'], $score, $max, $pct, $xp, $hints, $time]
);

// Add XP to user
addXP($userId, $xp);

// Award badges
$badgeEarned = null;
$badgeMap = [
    'awareness'         => ['first-steps', 'First Steps'],
    'phishing-email'    => ['phish-hunter', 'Phish Hunter'],
    'phone-scam'        => ['call-wise', 'Call Wise'],
    'escape-room'       => ['escapist', 'Escapist'],
    'network-watchdog'  => ['watchdog', 'Watchdog'],
    'ransomware-response' => ['ransomware-buster', 'Ransomware Buster'],
];
if (isset($badgeMap[$slug])) {
    [$bslug, $bname] = $badgeMap[$slug];
    $existing = queryOne('SELECT id FROM achievements WHERE user_id=? AND badge_slug=?', [$userId, $bslug]);
    if (!$existing) {
        awardBadge($userId, $bslug, $bname);
        $badgeEarned = $bname;
    }
}

// Check for Ace badge (90%+ on any game)
if ($pct >= 90) {
    $existing = queryOne('SELECT id FROM achievements WHERE user_id=? AND badge_slug="ace"', [$userId]);
    if (!$existing) { awardBadge($userId, 'ace', 'Ace'); if (!$badgeEarned) $badgeEarned = 'Ace'; }
}

// Check all-clear badge
$completedCount = queryOne(
    'SELECT COUNT(DISTINCT game_id) as c FROM game_sessions WHERE user_id=? AND completed=1',
    [$userId]
);
if ($completedCount['c'] >= 6) {
    $existing = queryOne('SELECT id FROM achievements WHERE user_id=? AND badge_slug="all-clear"', [$userId]);
    if (!$existing) { awardBadge($userId, 'all-clear', 'All Clear'); if (!$badgeEarned) $badgeEarned = 'All Clear'; }
}

// Mark awareness_completed flag on user
if ($slug === 'awareness' && $pct >= 75) {
    execute('UPDATE users SET awareness_completed = 1 WHERE id = ?', [$userId]);
}

refreshUserCache();

echo json_encode([
    'success'  => true,
    'session'  => $sessionId,
    'xp_total' => queryOne('SELECT total_xp FROM users WHERE id=?',[$userId])['total_xp'],
    'level'    => getLevel($userId),
    'badge'    => $badgeEarned,
]);

function getLevel(int $id): int {
    $u = queryOne('SELECT level FROM users WHERE id=?',[$id]);
    return $u['level'] ?? 1;
}
