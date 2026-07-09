<?php
require_once __DIR__ . '/auth.php';
requireLogin();
$user = currentUser();
$thresholds = unserialize(LEVEL_THRESHOLDS);
$nextXP = getNextLevelXP($user['total_xp']);
$prevXP = $thresholds[$user['level']] ?? 0;
$xpPct  = $nextXP > $prevXP ? min(100, round(($user['total_xp'] - $prevXP) / ($nextXP - $prevXP) * 100)) : 100;
$levelName = getLevelName($user['level']);
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — CyberShield</title>
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar">
  <a class="navbar-brand" href="<?= APP_URL ?>/dashboard.php">
    <div class="brand-icon">🛡</div>
    <span>CyberShield</span>
  </a>

  <div class="nav-center">
    <div class="xp-bar-mini">
      <span class="level-chip">Lv <?= $user['level'] ?> · <?= $levelName ?></span>
      <div class="mini-bar"><div class="mini-fill" style="width:<?= $xpPct ?>%"></div></div>
      <span class="xp-text">⚡ <?= number_format($user['total_xp']) ?> XP</span>
    </div>
  </div>

  <div class="nav-right">
    <?php if($user['role']==='admin'): ?>
      <a href="<?= APP_URL ?>/admin/" class="nav-link <?= str_starts_with($currentPage,'admin')?'active':'' ?>">Admin</a>
    <?php endif; ?>
    <a href="<?= APP_URL ?>/dashboard.php" class="nav-link <?= $currentPage==='dashboard'?'active':'' ?>">Dashboard</a>
    <a href="<?= APP_URL ?>/simulator.php" class="nav-link <?= $currentPage==='simulator'?'active':'' ?>" style="<?= $currentPage==='simulator'?'':'color:var(--yellow)' ?>">🎮 Simulator</a>
    <div class="nav-avatar" title="<?= htmlspecialchars($user['full_name']) ?>" onclick="toggleUserMenu()">
      <?= htmlspecialchars($user['avatar_initials']) ?>
    </div>
    <div class="user-menu" id="user-menu">
      <div class="user-menu-name"><?= htmlspecialchars($user['full_name']) ?></div>
      <div class="user-menu-email"><?= htmlspecialchars($user['email']) ?></div>
      <hr style="border-color:#1e293b;margin:8px 0">
      <a href="<?= APP_URL ?>/logout.php" class="user-menu-link danger">🚪 Sign Out</a>
    </div>
  </div>
</nav>

<script>
function toggleUserMenu(){
  const m=document.getElementById('user-menu');
  m.classList.toggle('open');
  document.addEventListener('click',function h(e){if(!e.target.closest('.nav-avatar')&&!e.target.closest('#user-menu')){m.classList.remove('open');document.removeEventListener('click',h);}},{once:true});
}
</script>
