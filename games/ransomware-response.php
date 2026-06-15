<?php
$pageTitle = 'Ransomware Response';
require_once dirname(__DIR__) . '/includes/header.php';
$gameRow = queryOne("SELECT * FROM games WHERE slug='ransomware-response'");
$gameId  = $gameRow['id'];
$userId  = $user['id'];
$already = getBestScore($userId, $gameId);
?>
<style>
.ransom-note { background:#0a0000; border:2px solid var(--red); border-radius:12px; padding:1.5rem; font-family:var(--mono); margin-bottom:1.5rem; }
.ransom-title { color:var(--red); font-size:1.1rem; font-weight:800; text-align:center; margin-bottom:.8rem; animation:pulseTxt .8s infinite; }
.infected-node { position:absolute; background:var(--surface); border:2px solid var(--border); border-radius:8px; padding:6px 12px; font-size:.74rem; text-align:center; transition:all .5s; }
.infected-node.infected { border-color:var(--red); background:rgba(239,68,68,.12); animation:pulseRed 1s infinite; }
.infected-node.safe { border-color:var(--green); background:rgba(16,185,129,.1); }
.infected-node.isolated { border-color:var(--muted); opacity:.5; }
@keyframes pulseRed{ 0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,.5);}50%{box-shadow:0 0 0 8px rgba(239,68,68,0);} }
@keyframes pulseTxt{ 0%,100%{opacity:1;}50%{opacity:.5;} }
.stage-nav { display:flex; gap:6px; margin-bottom:1.5rem; }
.stage-dot { flex:1; height:5px; border-radius:3px; background:var(--border); transition:background .5s; }
.stage-dot.done{background:var(--green);} .stage-dot.active{background:var(--red);}
</style>

<div class="container" style="max-width:900px">
  <a href="<?= APP_URL ?>/dashboard.php" style="color:var(--muted);font-size:.85rem;display:inline-flex;align-items:center;gap:4px;margin-bottom:1rem">← Dashboard</a>
  <div class="page-title">💀 Ransomware Response</div>
  <p class="page-sub">Your organisation's files are being encrypted RIGHT NOW. Race the clock through 4 stages to contain the attack, recover data, and prevent future incidents.</p>
  <?php if ($already): ?><div class="alert alert-success mb-2"><span class="alert-icon">✓</span>Best: <strong><?= round($already['percentage']) ?>%</strong>. Replay to improve.</div><?php endif; ?>

  <!-- STAGE PROGRESS -->
  <div class="stage-nav" id="stage-nav">
    <?php for($i=1;$i<=4;$i++): ?><div class="stage-dot <?=$i===1?'active':''?>" id="sdot-<?=$i?>"></div><?php endfor; ?>
  </div>

  <!-- INFECTION SPREAD VISUAL -->
  <div style="background:#04080f;border:1px solid var(--border);border-radius:12px;padding:1rem;margin-bottom:1.5rem;position:relative;height:180px;overflow:hidden" id="network-map">
    <div class="infected-node infected" style="left:5%;top:30%" id="n-pc1">💻 PC-01<br><span style="font-size:.62rem;color:var(--red)">ENCRYPTED</span></div>
    <div class="infected-node" style="left:25%;top:10%" id="n-pc2">💻 PC-02</div>
    <div class="infected-node" style="left:25%;top:60%" id="n-pc3">💻 PC-03</div>
    <div class="infected-node" style="left:50%;top:30%" id="n-srv1">🖥 FileServer</div>
    <div class="infected-node" style="left:70%;top:10%" id="n-bkp">💾 Backup</div>
    <div class="infected-node" style="left:70%;top:60%" id="n-dc">🔑 DomainCtrl</div>
    <div style="position:absolute;bottom:8px;right:12px;font-family:var(--mono);font-size:.75rem;color:var(--red)" id="spread-status">Encrypting: 1 system</div>
    <div class="infected-node" style="left:90%;top:30%;border-color:var(--red)" id="n-attacker">😈 Attacker</div>
  </div>

  <!-- RANSOM NOTE -->
  <div class="ransom-note" id="ransom-note">
    <div class="ransom-title">⚠ YOUR FILES HAVE BEEN ENCRYPTED ⚠</div>
    <div style="color:#fca5a5;font-size:.8rem;line-height:1.8">
      All files on this network have been encrypted with military-grade AES-256 encryption.<br>
      Your backups have also been targeted. Recovery without our key is <strong>mathematically impossible</strong>.<br><br>
      <span style="color:var(--yellow)">Send 3.5 BTC to: <code>1A2b3CbTC4ddress5E6f7G8h9I0jKlM</code></span><br><br>
      <span style="color:var(--red);animation:pulseTxt 1s infinite;display:block">Files permanently deleted in: <strong id="ransom-countdown">23:59:47</strong></span>
    </div>
  </div>

  <!-- STAGES -->
  <div id="stage-area"></div>
  <div id="final-result" style="display:none">
    <div class="result-box">
      <div style="font-size:3rem;margin-bottom:.5rem">💀</div>
      <div class="result-score" id="final-pct"></div>
      <div class="result-pct" id="final-detail"></div>
      <div class="result-xp" id="final-xp"></div>
      <div class="result-msg" id="final-msg"></div>
      <div style="display:flex;gap:1rem;justify-content:center;margin-top:1.5rem">
        <button class="btn btn-ghost" onclick="location.reload()">🔄 Replay</button>
        <a href="<?= APP_URL ?>/dashboard.php" class="btn btn-primary">Dashboard →</a>
      </div>
    </div>
  </div>
</div>

<div class="toast-wrap" id="toast-wrap"></div>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>
const stages = [
  {
    title:'Stage 1: Immediate Response',
    desc:'You discover the ransom note at 9:02am Monday. Files on PC-01 are actively being encrypted and spreading. What is your FIRST action?',
    opts:[
      {t:'Disconnect all systems from the network immediately to stop the spread', c:true,  f:'Correct! Network isolation is always step 1. It prevents the ransomware from spreading to other machines and blocks the attacker\'s C2 connection.'},
      {t:'Pay the ransom to get the decryption key before more files are lost',   c:false, f:'Wrong. FBI and NCSC strongly advise against paying ransoms. Payment doesn\'t guarantee recovery and funds further attacks. You\'re 12x more likely to be targeted again.'},
      {t:'Run an antivirus scan to remove the malware while files are still accessible', c:false, f:'Too late for prevention. Real-time encryption cannot be stopped by AV. Isolation first.'},
      {t:'Restart all systems — this will interrupt the encryption process',      c:false, f:'Wrong. Restarting may trigger the malware to encrypt remaining files and makes forensic investigation harder. Disconnect network first.'},
    ], nodeToUpdate:'n-pc1', nodeState:'isolated', spreadTo:'n-pc2'
  },
  {
    title:'Stage 2: Source Investigation',
    desc:'With the spread contained, forensics shows Patient Zero is PC-01 (Alex\'s machine). The malware was delivered last Friday. What was the most likely initial infection vector?',
    opts:[
      {t:'A phishing email with a malicious file attachment that Alex opened',     c:true,  f:'Correct! Phishing emails with malicious attachments account for over 75% of ransomware infections. In this case, a fake "security patch" .exe file.'},
      {t:'A brute force attack on the Remote Desktop Protocol (RDP) service',     c:false, f:'RDP attacks are a real vector but less common than phishing. Our logs show the initial entry was an email-delivered payload.'},
      {t:'An infected USB drive plugged into PC-01',                              c:false, f:'USB attacks happen but are far less common. Email-delivered malware is the dominant vector.'},
      {t:'A zero-day exploit in the web browser',                                 c:false, f:'Zero-days are rare. Attackers prefer low-cost, high-success methods — phishing is cheaper and more reliable than exploiting zero-days.'},
    ], nodeToUpdate:'n-pc2', nodeState:'safe', spreadTo:'n-srv1'
  },
  {
    title:'Stage 3: Recovery Strategy',
    desc:'The malware has been eradicated. You need to restore operations. Your backup strategy is: daily incremental to network storage (also encrypted) and weekly full backup to offline tape. What is the correct recovery approach?',
    opts:[
      {t:'Restore from the offline weekly tape backup — the only clean copy',     c:true,  f:'Correct! The network-attached backups were also encrypted. Only offline backups survive ransomware. This is why the 3-2-1 rule (3 copies, 2 media types, 1 offsite/offline) is critical.'},
      {t:'Restore from the network-attached incremental backups from last night', c:false, f:'Wrong. Ransomware specifically targets network shares including backup drives. The incremental backups are also encrypted.'},
      {t:'Try a ransomware decryption tool from the internet',                   c:false, f:'Unlikely to work. Professional ransomware uses correctly implemented asymmetric encryption. Free decryptors only work on poorly coded variants.'},
      {t:'Rebuild all systems from scratch without restoring any backup',        c:false, f:'Partial credit thinking, but throwing away all data is not the answer — the offline backup is clean and should be used.'},
    ], nodeToUpdate:'n-srv1', nodeState:'safe', spreadTo:'n-bkp'
  },
  {
    title:'Stage 4: Prevention for the Future',
    desc:'Operations are restored. Now you must brief the board on prevention measures. Which set of controls would have MOST LIKELY prevented this attack?',
    opts:[
      {t:'Email macro blocking, EDR on endpoints, offline backups, and user awareness training', c:true,  f:'Correct! A layered defence: blocking the delivery mechanism (macro/attachment), detecting the payload (EDR), and ensuring recovery (offline backup) + reducing click rate (training).'},
      {t:'Stronger passwords and mandatory MFA on all systems',                                  c:false, f:'Good controls but insufficient alone. MFA doesn\'t stop someone from clicking a malicious email attachment.'},
      {t:'A better firewall at the network perimeter',                                           c:false, f:'Perimeter security helps but ransomware was delivered via email (which passes through legitimate mail gateways). Endpoint and user controls are key.'},
      {t:'Encrypting all company data proactively',                                              c:false, f:'Encrypting your own data doesn\'t prevent ransomware from encrypting it again. The attacker controls the key.'},
    ], nodeToUpdate:'n-bkp', nodeState:'safe', spreadTo:null
  }
];

let currentStage = 0;
let stageScores = [];
let spreadTimer = null;

function renderStage(idx) {
  const s = stages[idx];
  document.getElementById('stage-area').innerHTML = `
    <div class="card mb-2">
      <div style="font-size:.8rem;color:var(--red);font-weight:600;margin-bottom:.3rem">ACTIVE THREAT — ${4-idx} stages remaining</div>
      <div style="font-size:1rem;font-weight:700;margin-bottom:.8rem">${s.title}</div>
      <div class="room-story" style="margin-bottom:1rem">${s.desc}</div>
      <div class="option-list" id="opts-${idx}">
        ${s.opts.map((o,oi)=>`
          <div class="option-item" id="opt-${idx}-${oi}" onclick="answerStage(${idx},${oi})">
            <div class="option-letter">${'ABCD'[oi]}</div>${o.t}
          </div>`).join('')}
      </div>
      <div id="stage-fb-${idx}" style="margin-top:1rem"></div>
    </div>`;
}

function answerStage(si, oi) {
  const s = stages[si];
  const opt = s.opts[oi];
  document.querySelectorAll(`[id^="opt-${si}-"]`).forEach(el => el.style.pointerEvents='none');
  document.getElementById(`opt-${si}-${oi}`).classList.add(opt.c?'correct':'wrong');
  if (!opt.c) {
    const correct = s.opts.findIndex(o=>o.c);
    document.getElementById(`opt-${si}-${correct}`).classList.add('correct');
  }
  stageScores.push(opt.c ? 75 : 0);

  // Update network map
  if (s.nodeToUpdate) {
    const node = document.getElementById(s.nodeToUpdate);
    if (node) { node.classList.remove('infected'); node.classList.add(opt.c ? 'safe' : 'infected'); }
  }

  document.getElementById(`stage-fb-${si}`).innerHTML = `
    <div class="alert ${opt.c?'alert-success':'alert-danger'}">
      <span class="alert-icon">${opt.c?'✅':'❌'}</span>
      <div>${opt.f}</div>
    </div>
    ${si < stages.length-1
      ? `<button class="btn btn-primary btn-sm mt-1" onclick="nextStage()">Next Stage →</button>`
      : `<button class="btn btn-primary btn-sm mt-1" onclick="finish()">See Final Results →</button>`}`;
}

function nextStage() {
  currentStage++;
  // Update stage dots
  document.getElementById('sdot-'+currentStage).classList.remove('active');
  document.getElementById('sdot-'+currentStage).classList.add('done');
  document.getElementById('sdot-'+(currentStage+1)).classList.add('active');

  // Spread to next node
  const prev = stages[currentStage-1];
  if (prev.spreadTo) {
    const node = document.getElementById(prev.spreadTo);
    if (node) { node.classList.add('infected'); }
    document.getElementById('spread-status').textContent = `Encrypting: ${currentStage+1} system(s)`;
  }
  renderStage(currentStage);
  window.scrollTo(0,0);
}

function finish() {
  const correct = stageScores.filter(s=>s>0).length;
  const pct = Math.round(correct/stages.length*100);
  const xp = Math.round(pct/100*400);
  document.getElementById('stage-area').style.display = 'none';
  document.getElementById('final-result').style.display = 'block';
  document.getElementById('final-pct').textContent = pct+'%';
  document.getElementById('final-pct').style.color = pct>=75?'var(--green)':'var(--yellow)';
  document.getElementById('final-detail').textContent = `${correct}/${stages.length} stages handled correctly`;
  document.getElementById('final-xp').textContent = `+${xp} XP Earned`;
  document.getElementById('final-msg').textContent = pct>=75
    ? '🛡 Excellent incident response! You contained the ransomware, identified the source, recovered correctly, and implemented the right prevention controls.'
    : '⚠️ Some gaps in your response. Key rules: 1) Isolate immediately 2) Never pay 3) Maintain offline backups 4) Train users not to click.';

  // Stop all animations
  document.querySelectorAll('.infected-node.infected').forEach(n => n.classList.add('isolated'));

  fetch('<?= APP_URL ?>/api/save-score.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ game_slug:'ransomware-response', score:correct, max_score:stages.length, xp_earned:xp, percentage:pct })
  }).then(r=>r.json()).then(d => { if(d.badge) showToast('🏅 Badge: '+d.badge,'success'); });
}

// Ransom countdown (fake)
let countdownSecs = 86387;
setInterval(() => {
  countdownSecs--;
  const h = Math.floor(countdownSecs/3600);
  const m = Math.floor((countdownSecs%3600)/60);
  const s = countdownSecs%60;
  const el = document.getElementById('ransom-countdown');
  if (el) el.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
}, 1000);

// Init
renderStage(0);
</script>
</body>
</html>
