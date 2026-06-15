<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) { header('Location: ' . APP_URL . '/dashboard.php'); exit; }

$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u    = trim($_POST['username'] ?? '');
    $e    = trim($_POST['email'] ?? '');
    $p    = $_POST['password'] ?? '';
    $p2   = $_POST['password2'] ?? '';
    $name = trim($_POST['full_name'] ?? '');
    $dept = trim($_POST['department'] ?? 'General');

    if (!$u || !$e || !$p || !$name) {
        $error = 'All fields are required.';
    } elseif (strlen($u) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (!filter_var($e, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($p) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($p !== $p2) {
        $error = 'Passwords do not match.';
    } else {
        $id = registerUser($u, $e, $p, $name, $dept);
        if ($id) {
            $success = 'Account created! You can now sign in.';
        } else {
            $error = 'Username or email is already taken.';
        }
    }
}

$departments = ['IT','HR','Finance','Marketing','Operations','Legal','Engineering','Sales','General'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Register — CyberShield</title>
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
<div class="auth-page">
  <div class="auth-card" style="max-width:480px">
    <div class="auth-logo">
      <div class="logo-icon">🛡</div>
      <h1>Create Account</h1>
      <p>Join your organisation's security training programme</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><span class="alert-icon">⚠</span><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><span class="alert-icon">✓</span><?= htmlspecialchars($success) ?> <a href="<?= APP_URL ?>/login.php">Sign in →</a></div>
    <?php endif; ?>

    <form method="POST">
      <div class="grid-2" style="gap:.8rem">
        <div class="form-group" style="margin:0">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" placeholder="Jane Smith" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" placeholder="jsmith" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
        </div>
      </div>
      <div class="form-group mt-2">
        <label class="form-label">Work Email</label>
        <input type="email" name="email" class="form-control" placeholder="jane@company.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Department</label>
        <select name="department" class="form-control">
          <?php foreach ($departments as $d): ?>
            <option value="<?= $d ?>" <?= (($_POST['department'] ?? 'General') === $d) ? 'selected' : '' ?>><?= $d ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="grid-2" style="gap:.8rem">
        <div class="form-group" style="margin:0">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required>
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="password2" class="form-control" placeholder="Repeat password" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary mt-2" style="width:100%;justify-content:center">Create Account →</button>
    </form>

    <div class="auth-footer">Already have an account? <a href="<?= APP_URL ?>/login.php">Sign in</a></div>
  </div>
</div>
</body>
</html>
