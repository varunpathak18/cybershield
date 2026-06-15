<?php
$pageTitle = 'Network Watchdog';
require_once dirname(__DIR__) . '/includes/header.php';
$gameRow = queryOne("SELECT * FROM games WHERE slug='network-watchdog'");
$gameId  = $gameRow['id'];
$userId  = $user['id'];
$already = getBestScore($userId, $gameId);
?>
<div class="container" style="max-width:1000px">
  <a href="<?= APP_URL ?>/dashboard.php" style="color:var(--muted);font-size:.85rem;display:inline-flex;align-items:center;gap:4px;margin-bottom:1rem">← Dashboard</a>
  <div class="page-title">🌐 Network Watchdog</div>
  <p class="page-sub">Live network traffic is streaming from your organisation's firewall. Flag every suspicious or malicious packet before data is exfiltrated. Click a row to flag/unflag it.</p>
  <?php if ($already): ?><div class="alert alert-success mb-2"><span class="alert-icon">✓</span>Best: <strong><?= round($already['percentage']) ?>%</strong>. Replay to improve.</div><?php endif; ?>

  <div class="card mb-2" style="padding:.8rem 1rem">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem">
      <div style="display:flex;gap:1.5rem;font-size:.82rem">
        <span>Flagged: <strong id="flag-count" style="color:var(--red)">0</strong></span>
        <span>Packets: <span id="pkt-count" style="color:var(--muted)">0</span>/20</span>
        <span style="color:var(--muted);font-size:.76rem">🔴 Danger &nbsp; 🟡 Suspicious &nbsp; ⚫ Normal</span>
      </div>
      <button class="btn btn-primary btn-sm" onclick="submitWatchdog()" id="submit-net" disabled>Submit Analysis</button>
    </div>
  </div>

  <div class="card mb-2" style="padding:.6rem">
    <div style="font-family:var(--mono);font-size:.72rem;color:var(--muted);padding:4px 8px;display:grid;grid-template-columns:75px 110px 20px 110px 60px 1fr auto;gap:6px;border-bottom:1px solid var(--border);margin-bottom:4px">
      <span>Time</span><span>Source IP</span><span></span><span>Destination</span><span>Proto</span><span>Info</span><span>Flag?</span>
    </div>
    <div class="pkt-stream" id="pkt-stream" style="min-height:400px;max-height:500px"></div>
  </div>

  <div id="net-result" style="margin-top:1rem"></div>
</div>

<div class="toast-wrap" id="toast-wrap"></div>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>
const packets = [
  {t:'10:04:01',src:'192.168.1.5',  dst:'8.8.8.8',           proto:'DNS',   info:'Query: google.com',                          risk:'normal',  susp:false},
  {t:'10:04:02',src:'185.220.101.55',dst:'192.168.1.10',      proto:'SSH',   info:'Connection attempt — port 22',               risk:'danger',  susp:true,  why:'External IP bruteforcing SSH (port 22)'},
  {t:'10:04:03',src:'192.168.1.8',  dst:'172.217.16.4',       proto:'HTTPS', info:'GET /search?q=weather',                      risk:'normal',  susp:false},
  {t:'10:04:04',src:'192.168.1.10', dst:'185.220.101.55',     proto:'SSH',   info:'Auth failed (attempt 1 of many)',             risk:'danger',  susp:true,  why:'Server responding to brute force attack'},
  {t:'10:04:05',src:'192.168.1.3',  dst:'192.168.1.1',        proto:'ARP',   info:'Who has 192.168.1.1? Tell 192.168.1.3',      risk:'normal',  susp:false},
  {t:'10:04:06',src:'10.0.0.99',   dst:'192.168.1.0/24',      proto:'ICMP',  info:'Ping sweep — scanning 254 hosts',            risk:'danger',  susp:true,  why:'Network reconnaissance / internal port scan'},
  {t:'10:04:07',src:'192.168.1.7',  dst:'smtp.gmail.com',     proto:'SMTP',  info:'Send: to colleague@company.com',             risk:'normal',  susp:false},
  {t:'10:04:08',src:'192.168.1.10', dst:'185.220.101.33',     proto:'TCP',   info:'Data transfer — 47.2 GB outbound',           risk:'danger',  susp:true,  why:'Massive outbound data transfer — likely exfiltration'},
  {t:'10:04:09',src:'192.168.1.2',  dst:'192.168.1.1',        proto:'DNS',   info:'Query: intranet.company.local',              risk:'normal',  susp:false},
  {t:'10:04:10',src:'192.168.1.6',  dst:'192.168.1.6',        proto:'SQL',   info:"SELECT * FROM users; DROP TABLE users;--",   risk:'danger',  susp:true,  why:'SQL injection payload detected in internal traffic'},
  {t:'10:04:11',src:'192.168.1.9',  dst:'192.168.1.1',        proto:'DHCP',  info:'DHCP Request from 00:1A:2B:3C:4D:5E',       risk:'normal',  susp:false},
  {t:'10:04:12',src:'192.168.1.5',  dst:'updates.microsoft.com',proto:'HTTPS','info':'Windows Update check',                   risk:'normal',  susp:false},
  {t:'10:04:13',src:'192.168.1.10', dst:'192.168.1.10',       proto:'TCP',   info:'Port scan SYN to :3306,:5432,:6379,:27017', risk:'warning', susp:true,  why:'Internal port scan targeting database ports'},
  {t:'10:04:14',src:'192.168.1.4',  dst:'teams.microsoft.com', proto:'HTTPS', info:'Video call — 1.2 Mbps upload',             risk:'normal',  susp:false},
  {t:'10:04:15',src:'192.168.1.11', dst:'pastebin.com',        proto:'HTTPS', info:'POST /api/v1/pastes — 2.1 MB body',        risk:'warning', susp:true,  why:'Data being pasted to public site — potential data leak'},
  {t:'10:04:16',src:'192.168.1.8',  dst:'192.168.1.1',         proto:'DNS',   info:'Query: www.bbc.co.uk',                     risk:'normal',  susp:false},
  {t:'10:04:17',src:'0.0.0.0',     dst:'255.255.255.255',      proto:'DHCP',  info:'DHCP Discover broadcast',                  risk:'normal',  susp:false},
  {t:'10:04:18',src:'192.168.1.10', dst:'185.220.101.55',      proto:'TCP',   info:'Reverse shell connection — port 4444',     risk:'danger',  susp:true,  why:'Reverse shell — active attacker C2 channel'},
  {t:'10:04:19',src:'192.168.1.3',  dst:'192.168.1.7',         proto:'SMB',   info:'File share access: \\\\server\\HR\\Salaries.xlsx', risk:'warning', susp:true, why:'Unusual inter-departmental file access (HR data)'},
  {t:'10:04:20',src:'192.168.1.5',  dst:'api.github.com',      proto:'HTTPS', info:'GET /repos/user — developer API call',     risk:'normal',  susp:false},
];

const flagged = new Set();
let streaming = false;
let pktIndex = 0;
let streamTimer = null;

function startStream() {
  if (streaming) return;
  streaming = true;
  streamTimer = setInterval(() => {
    if (pktIndex >= packets.length) {
      clearInterval(streamTimer);
      document.getElementById('submit-net').disabled = false;
      showToast('✅ Stream complete — submit your analysis', 'success');
      return;
    }
    addPacket(pktIndex);
    pktIndex++;
    document.getElementById('pkt-count').textContent = pktIndex;
  }, 600);
}

function addPacket(i) {
  const p = packets[i];
  const stream = document.getElementById('pkt-stream');
  const row = document.createElement('div');
  row.id = 'pkt-'+i;
  row.className = `pkt-row pkt-${p.risk}`;
  row.onclick = () => toggleFlag(i);
  const dot = p.risk==='danger' ? '🔴' : p.risk==='warning' ? '🟡' : '⚫';
  row.innerHTML = `
    <span class="pkt-time">${p.t}</span>
    <span class="pkt-src">${p.src}</span>
    <span style="color:var(--muted)">→</span>
    <span class="pkt-dst" style="font-size:.74rem">${p.dst}</span>
    <span class="pkt-proto">${p.proto}</span>
    <span class="pkt-info">${p.info}</span>
    <span style="font-size:.9rem">${dot}</span>`;
  stream.appendChild(row);
  stream.scrollTop = stream.scrollHeight;
}

function toggleFlag(i) {
  const row = document.getElementById('pkt-'+i);
  if (!row) return;
  if (flagged.has(i)) {
    flagged.delete(i);
    row.classList.remove('flagged');
  } else {
    flagged.add(i);
    row.classList.add('flagged');
  }
  document.getElementById('flag-count').textContent = flagged.size;
}

function submitWatchdog() {
  clearInterval(streamTimer);
  const suspicious = packets.map((p,i) => p.susp ? i : -1).filter(i=>i>=0);
  const suspSet = new Set(suspicious);
  let tp=0,fp=0,fn=0;
  flagged.forEach(i => { if(suspSet.has(i)) tp++; else fp++; });
  suspSet.forEach(i => { if(!flagged.has(i)) fn++; });

  const precision = flagged.size>0 ? Math.round(tp/flagged.size*100) : 0;
  const recall    = Math.round(tp/suspicious.length*100);
  const f1 = (precision+recall)>0 ? Math.round(2*precision*recall/(precision+recall)) : 0;
  const xp = Math.round(f1/100*300);

  // Show explanations for each suspicious packet
  let breakdown = '<div class="section-title mt-2" style="font-size:.85rem">📋 Suspicious Packets Breakdown</div>';
  packets.forEach((p,i) => {
    if (!p.susp) return;
    const caught = flagged.has(i);
    breakdown += `<div style="background:var(--surface);border:1px solid ${caught?'var(--green)':'var(--red)'};border-radius:8px;padding:8px 12px;margin-bottom:6px;font-size:.8rem;display:flex;gap:10px;align-items:flex-start">
      <span style="font-size:1rem">${caught?'✅':'❌'}</span>
      <div><strong>${p.src} → ${p.dst} [${p.proto}]</strong><br><span style="color:var(--muted)">${p.why}</span></div>
    </div>`;
  });

  document.getElementById('net-result').innerHTML = `
    <div class="result-box">
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1rem">
        <div><div style="font-size:1.8rem;font-weight:800;color:var(--green)">${tp}/${suspicious.length}</div><div style="font-size:.75rem;color:var(--muted)">Threats Caught</div></div>
        <div><div style="font-size:1.8rem;font-weight:800;color:var(--red)">${fp}</div><div style="font-size:.75rem;color:var(--muted)">False Positives</div></div>
        <div><div style="font-size:1.8rem;font-weight:800;color:var(--yellow)">${fn}</div><div style="font-size:.75rem;color:var(--muted)">Threats Missed</div></div>
      </div>
      <div class="result-score" style="color:${f1>=70?'var(--green)':'var(--yellow)'}">${f1}%</div>
      <div class="result-pct">F1 Score · Precision: ${precision}% · Recall: ${recall}%</div>
      <div class="result-xp">+${xp} XP Earned</div>
      <div class="result-msg">💡 F1 score balances precision (avoiding false alarms) with recall (catching real threats). Real SOC analysts aim for high recall to ensure no threat slips through.</div>
    </div>${breakdown}`;

  fetch('<?= APP_URL ?>/api/save-score.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ game_slug:'network-watchdog', score:f1, max_score:100, xp_earned:xp, percentage:f1 })
  }).then(r=>r.json()).then(d => { if(d.badge) showToast('🏅 Badge: '+d.badge,'success'); });
}

startStream();
</script>
</body>
</html>
