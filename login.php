<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) { header('Location: ' . APP_URL . '/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    if ($u && $p) {
        if (login($u, $p)) { header('Location: ' . APP_URL . '/dashboard.php'); exit; }
        else $error = 'Invalid username or password.';
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sign In — CyberShield</title>
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="logo-icon">🛡</div>
      <h1>CyberShield</h1>
      <p>Security Awareness Training Platform</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><span class="alert-icon">⚠</span><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="on">
      <div class="form-group">
        <label class="form-label">Username or Email</label>
        <input type="text" name="username" class="form-control" placeholder="Enter your username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Sign In →</button>
    </form>

    <div class="auth-footer">
      Don't have an account? <a href="<?= APP_URL ?>/register.php">Create one</a>
    </div>

    <div class="divider"></div>
    <div style="background:var(--surface2);border-radius:8px;padding:10px 14px;font-size:0.78rem;color:var(--muted)">
      <strong style="color:var(--text)">Demo Admin:</strong> admin / Admin@123
    </div>
  </div>
</div>
</body>
</html>
