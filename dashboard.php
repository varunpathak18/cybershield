<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$games = query('SELECT * FROM games ORDER BY sort_order');
$userId = $user['id'];

// Per-game best scores
$bestScores = [];
foreach ($games as $g) {
    $best = getBestScore($userId, $g['id']);
    $bestScores[$g['slug']] = $best;
}

$totalCompleted = count(array_filter($bestScores, fn($s) => $s !== null));
$badges = getUserBadges($userId);
$thresholds = unserialize(LEVEL_THRESHOLDS);
$nextXP = getNextLevelXP($user['total_xp']);
$prevXP = $thresholds[$user['level']] ?? 0;
$xpPct = $nextXP > $prevXP ? min(100, round(($user['total_xp'] - $prevXP) / ($nextXP - $prevXP) * 100)) : 100;

// Overall accuracy
$accRow = queryOne(
    'SELECT SUM(score) as ts, SUM(max_score) as tm FROM game_sessions WHERE user_id=? AND completed=1',
    [$userId]
);
$accuracy = ($accRow && $accRow['tm'] > 0) ? round($accRow['ts'] / $accRow['tm'] * 100) : null;

// Leaderboard rank
$rank = queryOne(
    'SELECT COUNT(*)+1 as r FROM users WHERE total_xp > ? AND role="student"',
    [$user['total_xp']]
);
$awarenessGame = queryOne("SELECT id FROM games WHERE slug='awareness'");
$awarenessCompleted = $awarenessGame ? (bool)getBestScore($userId, $awarenessGame['id']) : false;

$diff_labels = ['beginner'=>'Beginner','easy'=>'Easy','medium'=>'Medium','hard'=>'Hard','expert'=>'Expert'];
?>
<div class="container">
  <div class="page-title">Welcome back, <span><?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?></span> 👋</div>
  <p class="page-sub">
    <?php if (!$awarenessCompleted): ?>
      <span style="color:var(--yellow)">⚠ Complete the <strong>Cyber Hygiene Basics</strong> module first to unlock all games.</span>
    <?php else: ?>
      You're on track — keep completing modules to level up your security rank.
    <?php endif; ?>
  </p>

  <!-- STATS GRID -->
  <div class="grid-4 mb-3">
    <div class="card stat-card">
      <div class="stat-label">Total XP</div>
      <div class="stat-value c-accent"><?= number_format($user['total_xp']) ?></div>
      <div class="stat-sub">Level <?= $user['level'] ?> · <?= getLevelName($user['level']) ?></div>
    </div>
    <div class="card stat-card">
      <div class="stat-label">Modules Done</div>
      <div class="stat-value c-green"><?= $totalCompleted ?></div>
      <div class="stat-sub">of <?= count($games) ?> available</div>
    </div>
    <div class="card stat-card">
      <div class="stat-label">Accuracy</div>
      <div class="stat-value c-yellow"><?= $accuracy !== null ? $accuracy.'%' : '—' ?></div>
      <div class="stat-sub">across all completed games</div>
    </div>
    <div class="card stat-card">
      <div class="stat-label">Leaderboard</div>
      <div class="stat-value c-purple">#<?= $rank['r'] ?></div>
      <div class="stat-sub"><?= htmlspecialchars($user['department']) ?> department</div>
    </div>
  </div>

  <!-- XP PROGRESS -->
  <div class="card mb-3">
    <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:8px">
      <span>Progress to Level <?= $user['level']+1 ?> — <strong><?= getLevelName($user['level']+1) ?></strong></span>
      <span style="color:var(--muted)"><?= number_format($user['total_xp']) ?> / <?= number_format($nextXP) ?> XP</span>
    </div>
    <div class="progress-wrap"><div class="progress-fill" style="width:<?= $xpPct ?>%"></div></div>
  </div>

  <!-- GAME GRID -->
  <div class="section-title">🎮 Training Modules</div>
  <div class="grid-3 mb-3">
    <?php foreach ($games as $g):
      $best = $bestScores[$g['slug']];
      $pct  = $best ? round($best['percentage']) : 0;
      $isLocked = $g['requires_awareness'] && !$awarenessCompleted && $g['slug'] !== 'awareness';
      $isDone   = $best !== null;
    ?>
    <div class="card card-hover card-accent-top game-card" onclick="<?= $isLocked ? "showToast('Complete Cyber Hygiene Basics first!','error')" : "window.location='" . APP_URL . "/games/{$g['slug']}.php'" ?>">
      <?php if ($isDone): ?>
        <div class="badge-done">✓ Completed</div>
      <?php elseif ($isLocked): ?>
        <div class="badge-locked">🔒 Locked</div>
      <?php endif; ?>
      <div class="game-icon"><?= match($g['slug']){
        'awareness'=>'🛡','phishing-email'=>'🎣','phone-scam'=>'📞',
        'escape-room'=>'🚪','network-watchdog'=>'🌐','ransomware-response'=>'💀',
        default=>'🎮'} ?></div>
      <div class="game-title"><?= htmlspecialchars($g['title']) ?></div>
      <div class="game-desc"><?= htmlspecialchars($g['description']) ?></div>

      <?php if ($isDone): ?>
        <div style="margin:4px 0">
          <div style="display:flex;justify-content:space-between;font-size:.75rem;margin-bottom:4px">
            <span>Best score</span><span style="color:<?= $pct>=80?'var(--green)':($pct>=60?'var(--yellow)':'var(--red)') ?>"><?= $pct ?>%</span>
          </div>
          <div class="progress-wrap"><div class="progress-fill <?= $pct>=80?'green':($pct<50?'red':'') ?>" style="width:<?= $pct ?>%"></div></div>
        </div>
      <?php endif; ?>

      <div class="game-meta">
        <span class="diff-badge diff-<?= $g['difficulty'] ?>"><?= $diff_labels[$g['difficulty']] ?></span>
        <span style="display:flex;align-items:center;gap:8px">
          <span style="color:var(--muted);font-size:.75rem">⏱ <?= $g['estimated_mins'] ?>m</span>
          <span class="xp-tag">+<?= $g['xp_reward'] ?> XP</span>
        </span>
      </div>

      <?php if ($isLocked): ?>
        <div class="game-locked-overlay"><span style="font-size:1.5rem">🔒</span><span>Complete Basics first</span></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- LEADERBOARD + BADGES -->
  <div class="grid-2 mb-3">
    <!-- LEADERBOARD PREVIEW -->
    <div class="card">
      <div class="section-title">🏆 Top Performers</div>
      <?php
      $lb = query('SELECT u.full_name, u.department, u.total_xp, u.level FROM users u WHERE u.role="student" ORDER BY u.total_xp DESC LIMIT 8');
      $medals = ['🥇','🥈','🥉'];
      $mClass = ['lb-gold','lb-silver','lb-bronze'];
      foreach ($lb as $i => $p):
        $isMe = $p['full_name'] === $user['full_name'];
      ?>
        <div class="lb-row" style="<?= $isMe ? 'border-color:var(--accent);background:rgba(0,212,255,.04)' : '' ?>">
          <div class="lb-rank <?= $mClass[$i] ?? '' ?>"><?= $medals[$i] ?? '#'.($i+1) ?></div>
          <div>
            <div class="lb-name"><?= htmlspecialchars($p['full_name']) ?><?= $isMe ? ' 👈' : '' ?></div>
            <div class="lb-dept"><?= htmlspecialchars($p['department']) ?> · Level <?= $p['level'] ?></div>
          </div>
          <div class="lb-xp">⚡ <?= number_format($p['total_xp']) ?></div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($lb)): ?>
        <p style="color:var(--muted);font-size:.85rem;text-align:center;padding:1rem">No scores yet — be the first!</p>
      <?php endif; ?>
    </div>

    <!-- BADGES -->
    <div class="card">
      <div class="section-title">🎖 My Badges</div>
      <?php
      $allBadges = [
        ['slug'=>'first-steps','name'=>'First Steps','icon'=>'👣','desc'=>'Completed Cyber Hygiene Basics'],
        ['slug'=>'phish-hunter','name'=>'Phish Hunter','icon'=>'🎣','desc'=>'Completed Phishing Detector'],
        ['slug'=>'call-wise','name'=>'Call Wise','icon'=>'📞','desc'=>'Completed Phone Scam game'],
        ['slug'=>'escapist','name'=>'Escapist','icon'=>'🚪','desc'=>'Completed the Escape Room'],
        ['slug'=>'watchdog','name'=>'Watchdog','icon'=>'🌐','desc'=>'Completed Network Analysis'],
        ['slug'=>'ransomware-buster','name'=>'Ransomware Buster','icon'=>'💀','desc'=>'Completed Ransomware Response'],
        ['slug'=>'ace','name'=>'Ace','icon'=>'🏆','desc'=>'Scored 90%+ on any game'],
        ['slug'=>'all-clear','name'=>'All Clear','icon'=>'⭐','desc'=>'Completed all 6 modules'],
      ];
      $earnedSlugs = array_column($badges, 'badge_slug');
      ?>
      <div class="badge-grid" style="grid-template-columns:repeat(4,1fr);gap:.8rem">
        <?php foreach ($allBadges as $b):
          $unlocked = in_array($b['slug'], $earnedSlugs);
        ?>
          <div class="badge-card <?= $unlocked ? 'unlocked' : 'locked' ?>">
            <div class="badge-icon"><?= $b['icon'] ?></div>
            <div class="badge-name"><?= $b['name'] ?></div>
            <div class="badge-desc"><?= $unlocked ? '✅ Earned' : $b['desc'] ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<div class="toast-wrap" id="toast-wrap"></div>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
</body>
</html>
