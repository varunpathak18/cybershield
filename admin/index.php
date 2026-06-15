<?php
$pageTitle = 'Admin Dashboard';
require_once dirname(__DIR__) . '/includes/header.php';
requireAdmin();

$stats = queryOne('SELECT COUNT(*) as users, SUM(total_xp) as total_xp FROM users WHERE role="student"');
$sessions = queryOne('SELECT COUNT(*) as c FROM game_sessions WHERE completed=1');
$avgPct   = queryOne('SELECT AVG(percentage) as a FROM game_sessions WHERE completed=1');
$today    = queryOne('SELECT COUNT(DISTINCT user_id) as c FROM game_sessions WHERE DATE(completed_at)=CURDATE() AND completed=1');

$users = query('SELECT u.*,
    (SELECT COUNT(*) FROM game_sessions g WHERE g.user_id=u.id AND g.completed=1) as games_done,
    (SELECT AVG(g.percentage) FROM game_sessions g WHERE g.user_id=u.id AND g.completed=1) as avg_pct
    FROM users u WHERE u.role="student" ORDER BY u.total_xp DESC');

$gameSummary = query('SELECT g.title, g.slug,
    COUNT(DISTINCT gs.user_id) as players,
    AVG(gs.percentage) as avg_pct,
    MAX(gs.percentage) as best_pct,
    MIN(gs.percentage) as worst_pct
    FROM games g
    LEFT JOIN game_sessions gs ON g.id=gs.game_id AND gs.completed=1
    GROUP BY g.id ORDER BY g.sort_order');

$recent = query('SELECT gs.*, u.full_name, u.department, g.title as game_title
    FROM game_sessions gs
    JOIN users u ON u.id=gs.user_id
    JOIN games g ON g.id=gs.game_id
    WHERE gs.completed=1
    ORDER BY gs.completed_at DESC LIMIT 40');
?>
<div class="container">
  <div class="page-title">⚙️ Admin Dashboard</div>
  <p class="page-sub">Organisation-wide security training overview — all employee records</p>

  <div class="grid-4 mb-3">
    <div class="card stat-card"><div class="stat-label">Total Users</div><div class="stat-value c-accent"><?= (int)$stats['users'] ?></div><div class="stat-sub">registered employees</div></div>
    <div class="card stat-card"><div class="stat-label">Active Today</div><div class="stat-value c-green"><?= (int)$today['c'] ?></div><div class="stat-sub">completed a module today</div></div>
    <div class="card stat-card"><div class="stat-label">Total Sessions</div><div class="stat-value c-yellow"><?= (int)$sessions['c'] ?></div><div class="stat-sub">game completions</div></div>
    <div class="card stat-card"><div class="stat-label">Org Avg Score</div><div class="stat-value c-purple"><?= round($avgPct['a'] ?? 0) ?>%</div><div class="stat-sub">across all games</div></div>
  </div>

  <!-- GAME PERFORMANCE -->
  <div class="section-title">📊 Module Performance</div>
  <div class="card mb-3" style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;font-size:.84rem">
      <thead><tr style="border-bottom:1px solid var(--border);color:var(--muted)">
        <th style="text-align:left;padding:8px 12px">Module</th>
        <th style="text-align:center;padding:8px">Players</th>
        <th style="text-align:center;padding:8px">Avg</th>
        <th style="text-align:center;padding:8px">Best</th>
        <th style="text-align:center;padding:8px">Worst</th>
        <th style="text-align:left;padding:8px 12px;min-width:160px">Distribution</th>
      </tr></thead>
      <tbody>
        <?php foreach ($gameSummary as $g):
          $avg = round($g['avg_pct'] ?? 0);
          $c = $avg>=80?'var(--green)':($avg>=60?'var(--yellow)':'var(--red)');
        ?>
        <tr style="border-bottom:1px solid rgba(30,41,59,.4)">
          <td style="padding:10px 12px;font-weight:600"><?= htmlspecialchars($g['title']) ?></td>
          <td style="text-align:center;padding:8px;color:var(--accent)"><?= (int)$g['players'] ?></td>
          <td style="text-align:center;padding:8px;color:<?=$c?>;font-weight:700"><?= $avg ?>%</td>
          <td style="text-align:center;padding:8px;color:var(--green)"><?= round($g['best_pct']??0) ?>%</td>
          <td style="text-align:center;padding:8px;color:var(--red)"><?= round($g['worst_pct']??0) ?>%</td>
          <td style="padding:8px 12px"><div class="progress-wrap"><div class="progress-fill <?= $avg>=80?'green':($avg<50?'red':'') ?>" style="width:<?=$avg?>%"></div></div></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- USER TABLE -->
  <div class="section-title">👥 Employee Progress</div>
  <div class="card mb-3" style="overflow-x:auto">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
      <input type="text" id="user-search" class="form-control" placeholder="Search by name or department..." style="max-width:300px" oninput="filterUsers(this.value)">
      <a href="?export=csv" class="btn btn-ghost btn-sm">⬇ Export CSV</a>
    </div>
    <table style="width:100%;border-collapse:collapse;font-size:.82rem" id="user-table">
      <thead><tr style="border-bottom:1px solid var(--border);color:var(--muted)">
        <th style="text-align:left;padding:8px 10px">#</th>
        <th style="text-align:left;padding:8px 10px">Employee</th>
        <th style="text-align:left;padding:8px">Dept</th>
        <th style="text-align:center;padding:8px">Level</th>
        <th style="text-align:center;padding:8px">XP</th>
        <th style="text-align:center;padding:8px">Games</th>
        <th style="text-align:center;padding:8px">Avg Score</th>
        <th style="text-align:center;padding:8px">Basics</th>
        <th style="text-align:left;padding:8px">Last Active</th>
      </tr></thead>
      <tbody>
        <?php foreach ($users as $i => $u): ?>
        <tr class="user-row" style="border-bottom:1px solid rgba(30,41,59,.3)" data-name="<?= strtolower(htmlspecialchars($u['full_name'])) ?>" data-dept="<?= strtolower(htmlspecialchars($u['department'])) ?>">
          <td style="padding:10px;color:var(--muted);font-family:var(--mono)"><?= $i+1 ?></td>
          <td style="padding:8px 10px">
            <div style="display:flex;align-items:center;gap:8px">
              <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;flex-shrink:0"><?= htmlspecialchars($u['avatar_initials']) ?></div>
              <div><div style="font-weight:600"><?= htmlspecialchars($u['full_name']) ?></div><div style="color:var(--muted);font-size:.72rem"><?= htmlspecialchars($u['email']) ?></div></div>
            </div>
          </td>
          <td style="padding:8px;color:var(--muted);font-size:.78rem"><?= htmlspecialchars($u['department']) ?></td>
          <td style="text-align:center;padding:8px"><span class="tag tag-purple">Lv <?= $u['level'] ?> · <?= getLevelName($u['level']) ?></span></td>
          <td style="text-align:center;padding:8px;color:var(--accent);font-family:var(--mono);font-weight:700"><?= number_format($u['total_xp']) ?></td>
          <td style="text-align:center;padding:8px"><?= (int)$u['games_done'] ?>/6</td>
          <td style="text-align:center;padding:8px">
            <?php $ap=round($u['avg_pct']??0); ?>
            <span style="color:<?=$ap>=80?'var(--green)':($ap>=60?'var(--yellow)':'var(--red)')?>;font-weight:700"><?=$ap?>%</span>
          </td>
          <td style="text-align:center;padding:8px"><span class="tag <?= $u['awareness_completed']?'tag-green':'tag-red' ?>"><?= $u['awareness_completed']?'✓ Done':'✗ Pending' ?></span></td>
          <td style="padding:8px;color:var(--muted);font-size:.74rem"><?= $u['last_active'] ? date('d M Y',strtotime($u['last_active'])) : 'Never' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($users)): ?><tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--muted)">No users yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- RECENT SESSIONS -->
  <div class="section-title">📋 Recent Sessions</div>
  <div class="card" style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;font-size:.81rem">
      <thead><tr style="border-bottom:1px solid var(--border);color:var(--muted)">
        <th style="text-align:left;padding:8px 10px">Employee</th>
        <th style="text-align:left;padding:8px 10px">Module</th>
        <th style="text-align:center;padding:8px">Score</th>
        <th style="text-align:center;padding:8px">%</th>
        <th style="text-align:center;padding:8px">XP</th>
        <th style="text-align:left;padding:8px">Completed</th>
      </tr></thead>
      <tbody>
        <?php foreach ($recent as $r): $pct=round($r['percentage']); ?>
        <tr style="border-bottom:1px solid rgba(30,41,59,.3)">
          <td style="padding:8px 10px"><strong><?= htmlspecialchars($r['full_name']) ?></strong><br><span style="color:var(--muted);font-size:.72rem"><?= htmlspecialchars($r['department']) ?></span></td>
          <td style="padding:8px 10px"><?= htmlspecialchars($r['game_title']) ?></td>
          <td style="text-align:center;padding:8px;font-family:var(--mono)"><?= $r['score'] ?>/<?= $r['max_score'] ?></td>
          <td style="text-align:center;padding:8px"><span style="color:<?=$pct>=80?'var(--green)':($pct>=60?'var(--yellow)':'var(--red)')?>;font-weight:700"><?=$pct?>%</span></td>
          <td style="text-align:center;padding:8px;color:#a855f7;font-weight:600">+<?= $r['xp_earned'] ?></td>
          <td style="padding:8px;color:var(--muted);font-size:.74rem"><?= $r['completed_at'] ? date('d M Y H:i',strtotime($r['completed_at'])) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($recent)): ?><tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--muted)">No sessions yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>
function filterUsers(q) {
  document.querySelectorAll('.user-row').forEach(r => {
    r.style.display = (r.dataset.name.includes(q.toLowerCase()) || r.dataset.dept.includes(q.toLowerCase())) ? '' : 'none';
  });
}
</script>
</body>
</html>
