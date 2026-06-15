<?php
$pageTitle = 'Cyber Hygiene Basics';
require_once dirname(__DIR__) . '/includes/header.php';
$gameRow = queryOne("SELECT * FROM games WHERE slug='awareness'");
$gameId  = $gameRow['id'];
$userId  = $user['id'];
$already = getBestScore($userId, $gameId);
?>
<div class="container">
  <a href="<?= APP_URL ?>/dashboard.php" style="color:var(--muted);font-size:.85rem;display:inline-flex;align-items:center;gap:4px;margin-bottom:1rem">← Back to Dashboard</a>
  <div class="page-title">🛡 Cyber Hygiene Basics</div>
  <p class="page-sub">Foundation module — complete this first to unlock all other training games. (~15 minutes)</p>

  <!-- STEP PROGRESS -->
  <div class="awareness-progress" id="aw-progress">
    <?php for($i=1;$i<=6;$i++): ?>
      <div class="aw-pip <?= $i===1?'active':'' ?>" id="pip-<?=$i?>"></div>
    <?php endfor; ?>
  </div>

  <!-- ═══ STEP 1: WHY IT MATTERS ═══ -->
  <div class="awareness-step active" id="step-1">
    <div class="card card-lg mb-2">
      <div style="font-size:1.1rem;font-weight:700;margin-bottom:1rem">📌 Why Cyber Awareness Matters</div>
      <div class="grid-2 mb-2" style="gap:1rem">
        <div style="background:var(--surface2);border-radius:10px;padding:1.2rem">
          <div style="font-size:2rem;margin-bottom:6px">📧</div>
          <div style="font-weight:700;margin-bottom:4px">3.4 Billion</div>
          <div style="font-size:.82rem;color:var(--muted)">Phishing emails are sent every single day</div>
        </div>
        <div style="background:var(--surface2);border-radius:10px;padding:1.2rem">
          <div style="font-size:2rem;margin-bottom:6px">💰</div>
          <div style="font-weight:700;margin-bottom:4px">$4.45 Million</div>
          <div style="font-size:.82rem;color:var(--muted)">Average cost of a data breach in 2023</div>
        </div>
        <div style="background:var(--surface2);border-radius:10px;padding:1.2rem">
          <div style="font-size:2rem;margin-bottom:6px">👤</div>
          <div style="font-weight:700;margin-bottom:4px">82%</div>
          <div style="font-size:.82rem;color:var(--muted)">Of breaches involve a human element</div>
        </div>
        <div style="background:var(--surface2);border-radius:10px;padding:1.2rem">
          <div style="font-size:2rem;margin-bottom:6px">⏱</div>
          <div style="font-weight:700;margin-bottom:4px">207 Days</div>
          <div style="font-size:.82rem;color:var(--muted)">Average time to identify a breach</div>
        </div>
      </div>
      <div class="alert alert-warn">
        <span class="alert-icon">💡</span>
        <div><strong>The most important fact:</strong> Technology alone cannot protect an organisation. Attackers target <em>people</em> because humans are predictable, trusting, and can be manipulated. <strong>You</strong> are the most important security control.</div>
      </div>

      <!-- VIDEO LESSON 1 -->
      <div class="section-title mt-2">📺 Watch: The Human Element in Cybersecurity</div>
      <div class="video-lesson mb-2">
        <div class="lesson-header">🎬 What is Cybersecurity? (3 min overview)</div>
        <iframe src="https://www.youtube.com/embed/inWWhr5tnEA" allowfullscreen title="Cybersecurity overview"></iframe>
      </div>
      <p style="font-size:.8rem;color:var(--muted)">* If the video doesn't load, continue to the next section — the content is covered in the lessons below.</p>
    </div>
    <div style="display:flex;justify-content:flex-end">
      <button class="btn btn-primary" onclick="nextStep(1)">Next: Top Threats →</button>
    </div>
  </div>

  <!-- ═══ STEP 2: TOP THREATS ═══ -->
  <div class="awareness-step" id="step-2">
    <div class="card card-lg mb-2">
      <div style="font-size:1.1rem;font-weight:700;margin-bottom:1.5rem">🎯 The Top Threats You'll Face at Work</div>

      <?php
      $threats = [
        ['icon'=>'🎣','name'=>'Phishing','colour'=>'var(--red)',
         'desc'=>'Fraudulent emails, texts, or websites designed to steal your credentials or install malware.',
         'signs'=>['Urgent or threatening language','Sender address slightly misspelled','Generic greetings ("Dear Customer")','Suspicious links or unexpected attachments','Requests for passwords or personal information'],
         'video'=>'XBkzBrXlle0','vdesc'=>'Phishing Explained (5 min)'],
        ['icon'=>'📞','name'=>'Social Engineering','colour'=>'var(--yellow)',
         'desc'=>'Psychological manipulation to trick you into giving up information or access. Phone calls, in-person visits, or messages.',
         'signs'=>['Creates urgency ("must act NOW")','Claims authority ("I\'m from IT / the CEO")','Requests secrecy ("don\'t tell your manager")','Too good to be true offers','Asks you to bypass security procedures'],
         'video'=>'lc7scxvKQOo','vdesc'=>'Social Engineering Attacks (4 min)'],
        ['icon'=>'🔑','name'=>'Weak Passwords','colour'=>'var(--yellow)',
         'desc'=>'Simple or reused passwords are cracked in seconds. Attackers use automated tools that try millions of combinations.',
         'signs'=>['Using names, birthdays, or dictionary words','Short passwords (under 12 characters)','Reusing the same password across sites','Never changing passwords','Not using multi-factor authentication'],
         'video'=>'aEmXF4P_RQ0','vdesc'=>'Password Security (3 min)'],
        ['icon'=>'💀','name'=>'Ransomware','colour'=>'var(--red)',
         'desc'=>'Malware that encrypts your files and demands payment to restore them. Often arrives via phishing emails.',
         'signs'=>['Unexpected email attachments','Files suddenly becoming inaccessible','Unusual system slowdown','Strange pop-ups or warnings','Connections to unknown external servers'],
         'video'=>'WqD-ATqw3js','vdesc'=>'Ransomware Explained (4 min)'],
      ];
      foreach ($threats as $t): ?>
      <div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:1.2rem;margin-bottom:1rem">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
          <span style="font-size:1.6rem"><?= $t['icon'] ?></span>
          <span style="font-size:1rem;font-weight:700;color:<?= $t['colour'] ?>"><?= $t['name'] ?></span>
        </div>
        <p style="font-size:.86rem;line-height:1.6;margin-bottom:10px"><?= $t['desc'] ?></p>
        <div style="font-size:.78rem;font-weight:600;color:var(--muted);margin-bottom:6px">WARNING SIGNS:</div>
        <ul style="padding-left:1.2rem;font-size:.82rem;line-height:1.8;color:var(--text)">
          <?php foreach($t['signs'] as $s): ?><li><?= htmlspecialchars($s) ?></li><?php endforeach; ?>
        </ul>
        <div class="video-lesson mt-2" style="border-radius:8px">
          <div class="lesson-header" style="font-size:.76rem">🎬 <?= $t['vdesc'] ?></div>
          <iframe src="https://www.youtube.com/embed/<?= $t['video'] ?>" allowfullscreen title="<?= $t['name'] ?>"></iframe>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="display:flex;justify-content:space-between">
      <button class="btn btn-ghost" onclick="prevStep(2)">← Back</button>
      <button class="btn btn-primary" onclick="nextStep(2)">Next: Cyber Hygiene →</button>
    </div>
  </div>

  <!-- ═══ STEP 3: CYBER HYGIENE CHECKLIST ═══ -->
  <div class="awareness-step" id="step-3">
    <div class="card card-lg mb-2">
      <div style="font-size:1.1rem;font-weight:700;margin-bottom:.5rem">✅ Your Cyber Hygiene Checklist</div>
      <p style="font-size:.85rem;color:var(--muted);margin-bottom:1.2rem">Check off each habit — these are the fundamentals every employee must practice.</p>

      <?php
      $habits = [
        ['title'=>'Use a unique, strong password for every account','detail'=>'Use a password manager (Bitwarden, 1Password) to generate and store unique passwords. Minimum 14 characters.'],
        ['title'=>'Enable Multi-Factor Authentication (MFA) everywhere','detail'=>'MFA adds a second verification step. Even if your password is stolen, attackers cannot log in without your phone/token.'],
        ['title'=>'Lock your screen when stepping away from your desk','detail'=>'Press Win+L (Windows) or Cmd+Ctrl+Q (Mac). An unlocked PC is a data breach waiting to happen.'],
        ['title'=>'Never plug in unknown USB drives or devices','detail'=>'USB drives can contain malware that installs automatically. Even one from the car park could compromise the whole network.'],
        ['title'=>'Verify unexpected requests through a separate channel','detail'=>'If someone emails asking for a payment or access, call them on a known number to confirm. Never trust only the email.'],
        ['title'=>'Keep software and OS updated promptly','detail'=>'90% of successful exploits target known vulnerabilities that already have patches available. Update notifications exist for a reason.'],
        ['title'=>'Use company VPN when working remotely or on public WiFi','detail'=>'Public WiFi is inherently untrusted. A VPN encrypts your traffic so attackers on the same network cannot intercept it.'],
        ['title'=>'Report suspicious emails using the official process','detail'=>'Don\'t just delete phishing emails — report them to IT so the sender can be blocked and others warned.'],
        ['title'=>'Never share credentials, even with "IT Support"','detail'=>'Legitimate IT staff will never ask for your password. If they do, it is either a test or an attack.'],
        ['title'=>'Use approved cloud storage — not personal accounts — for work files','detail'=>'Personal Google Drive/Dropbox accounts lack company security controls and create data compliance risks.'],
      ];
      ?>
      <div id="hygiene-list">
        <?php foreach ($habits as $i => $h): ?>
          <div class="hygiene-item" id="hi-<?=$i?>" onclick="toggleHygiene(<?=$i?>)">
            <div class="hygiene-check" id="hc-<?=$i?>"></div>
            <div class="hygiene-text">
              <strong><?= htmlspecialchars($h['title']) ?></strong>
              <?= htmlspecialchars($h['detail']) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div style="margin-top:1rem;display:flex;align-items:center;gap:10px">
        <div class="progress-wrap" style="flex:1"><div class="progress-fill green" id="hygiene-bar" style="width:0%"></div></div>
        <span id="hygiene-count" style="font-size:.82rem;color:var(--muted)">0/<?= count($habits) ?></span>
      </div>
      <div id="hygiene-note" class="alert alert-info mt-2" style="display:none">
        <span class="alert-icon">💡</span> Great! You understand the key hygiene habits. Check all boxes to continue.
      </div>
    </div>
    <div style="display:flex;justify-content:space-between">
      <button class="btn btn-ghost" onclick="prevStep(3)">← Back</button>
      <button class="btn btn-primary" id="hygiene-next" onclick="nextStep(3)" disabled>Next: Password Security →</button>
    </div>
  </div>

  <!-- ═══ STEP 4: PASSWORD DEEP DIVE ═══ -->
  <div class="awareness-step" id="step-4">
    <div class="card card-lg mb-2">
      <div style="font-size:1.1rem;font-weight:700;margin-bottom:1rem">🔐 Password Security Deep Dive</div>

      <div class="grid-2 mb-2" style="gap:1rem">
        <div>
          <div class="section-title" style="font-size:.85rem">How Fast Can Passwords Be Cracked?</div>
          <?php
          $pws = [
            ['pw'=>'password','time'=>'Instant','colour'=>'var(--red)'],
            ['pw'=>'Password1','time'=>'< 1 minute','colour'=>'var(--red)'],
            ['pw'=>'P@ssw0rd!','time'=>'3 hours','colour'=>'var(--orange)'],
            ['pw'=>'Tr0ub4dour&3','time'=>'1.5 years','colour'=>'var(--yellow)'],
            ['pw'=>'correct horse battery staple','time'=>'550 years','colour'=>'var(--green)'],
            ['pw'=>'X9#mK!qL2@pN4&vB','time'=>'Billions of years','colour'=>'var(--accent)'],
          ];
          foreach ($pws as $p): ?>
          <div style="background:var(--surface2);border-radius:6px;padding:8px 12px;margin-bottom:6px;display:flex;justify-content:space-between;align-items:center">
            <code style="font-size:.8rem;color:var(--text)"><?= htmlspecialchars($p['pw']) ?></code>
            <span style="font-size:.75rem;font-weight:700;color:<?= $p['colour'] ?>"><?= $p['time'] ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <div>
          <div class="section-title" style="font-size:.85rem">The Golden Rules</div>
          <div style="font-size:.84rem;line-height:2">
            ✅ Use a <strong>passphrase</strong>: 4 random words are stronger than a complex short password<br>
            ✅ Minimum <strong>14 characters</strong><br>
            ✅ <strong>Never reuse</strong> passwords across sites<br>
            ✅ Use a <strong>password manager</strong> — you only need to remember one master password<br>
            ✅ Enable <strong>MFA</strong> on every account that supports it<br>
            ✅ <strong>Never share</strong> your password with anyone — including IT<br>
            ❌ No dictionary words, names, or dates<br>
            ❌ No simple substitutions (@ for a, 3 for e) — attackers know these<br>
            ❌ No company name + year combinations<br>
          </div>

          <!-- Live password tester -->
          <div style="margin-top:1rem;background:var(--surface3);border-radius:10px;padding:1rem">
            <div style="font-size:.82rem;font-weight:600;margin-bottom:8px">🔬 Test a Password</div>
            <input type="password" id="pw-test" class="form-control" placeholder="Type a password to analyse..." oninput="analysePW(this.value)" style="font-family:var(--mono)">
            <div style="margin-top:8px">
              <div class="progress-wrap" style="height:6px;margin-bottom:6px"><div id="pw-bar" class="progress-fill" style="width:0%;transition:all .4s"></div></div>
              <div id="pw-label" style="font-size:.8rem;font-weight:600;color:var(--muted)">Enter a password above</div>
            </div>
            <div id="pw-criteria" style="margin-top:8px;font-size:.76rem;line-height:1.9">
              <div><span id="cr-len">⬜</span> 14+ characters</div>
              <div><span id="cr-up">⬜</span> Uppercase letter</div>
              <div><span id="cr-low">⬜</span> Lowercase letter</div>
              <div><span id="cr-num">⬜</span> Number</div>
              <div><span id="cr-sym">⬜</span> Symbol (!@#$...)</div>
              <div><span id="cr-dict">⬜</span> No common words</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div style="display:flex;justify-content:space-between">
      <button class="btn btn-ghost" onclick="prevStep(4)">← Back</button>
      <button class="btn btn-primary" onclick="nextStep(4)">Next: Phishing Awareness →</button>
    </div>
  </div>

  <!-- ═══ STEP 5: PHISHING AWARENESS ═══ -->
  <div class="awareness-step" id="step-5">
    <div class="card card-lg mb-2">
      <div style="font-size:1.1rem;font-weight:700;margin-bottom:1rem">🎣 Spotting Phishing — Before You Click</div>
      <div class="alert alert-danger mb-2">
        <span class="alert-icon">⚠</span>
        <div><strong>Phishing causes 36% of all data breaches.</strong> An attacker only needs ONE employee to click to gain a foothold in your entire network. Your judgement is your organisation's first line of defence.</div>
      </div>

      <div class="section-title" style="font-size:.85rem">The STOP — THINK — VERIFY Framework</div>
      <div class="grid-3 mb-2" style="gap:1rem">
        <div style="background:var(--surface2);border-radius:10px;padding:1.2rem;text-align:center">
          <div style="font-size:2rem;margin-bottom:8px">🛑</div>
          <div style="font-weight:700;color:var(--red);margin-bottom:6px">STOP</div>
          <div style="font-size:.8rem;line-height:1.6">Pause before clicking any link or opening any attachment, especially if it's unexpected.</div>
        </div>
        <div style="background:var(--surface2);border-radius:10px;padding:1.2rem;text-align:center">
          <div style="font-size:2rem;margin-bottom:8px">🤔</div>
          <div style="font-weight:700;color:var(--yellow);margin-bottom:6px">THINK</div>
          <div style="font-size:.8rem;line-height:1.6">Ask yourself: Was this expected? Does the sender address match? Does the link destination look right?</div>
        </div>
        <div style="background:var(--surface2);border-radius:10px;padding:1.2rem;text-align:center">
          <div style="font-size:2rem;margin-bottom:8px">✅</div>
          <div style="font-weight:700;color:var(--green);margin-bottom:6px">VERIFY</div>
          <div style="font-size:.8rem;line-height:1.6">Contact the sender through a separate, known channel (phone call) to confirm the request is genuine.</div>
        </div>
      </div>

      <div class="section-title" style="font-size:.85rem">8 Red Flags to Always Check</div>
      <?php
      $flags = [
        ['Sender domain is wrong','paypa1.com instead of paypal.com — one character change is enough'],
        ['Generic salutation','"Dear Customer" instead of your actual name'],
        ['Urgency & threats','"Your account will be closed in 24 hours" — creating panic to stop you thinking'],
        ['Suspicious link URL','Hover before clicking — destination should match what the email claims'],
        ['Unexpected attachment','PDF, ZIP, DOCX, or .EXE you weren\'t expecting'],
        ['Requests for credentials','Legitimate companies never ask for your password via email'],
        ['Poor grammar/spelling','Often a sign of rushed or automated attack email'],
        ['Mismatched branding','Logos look slightly wrong, colours off, fonts inconsistent'],
      ];
      foreach ($flags as $i => $f): ?>
        <div style="display:flex;gap:10px;background:var(--surface2);border-radius:8px;padding:10px 12px;margin-bottom:6px;font-size:.83rem">
          <span style="color:var(--red);font-weight:700;min-width:20px"><?= $i+1 ?>.</span>
          <div><strong><?= htmlspecialchars($f[0]) ?></strong> — <?= htmlspecialchars($f[1]) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
    <div style="display:flex;justify-content:space-between">
      <button class="btn btn-ghost" onclick="prevStep(5)">← Back</button>
      <button class="btn btn-primary" onclick="nextStep(5)">Final Quiz →</button>
    </div>
  </div>

  <!-- ═══ STEP 6: FINAL QUIZ ═══ -->
  <div class="awareness-step" id="step-6">
    <div class="card card-lg mb-2">
      <div style="font-size:1.1rem;font-weight:700;margin-bottom:.4rem">📝 Knowledge Check</div>
      <p style="font-size:.85rem;color:var(--muted);margin-bottom:1.5rem">Answer all 8 questions. You need 75% (6/8) to pass and unlock the other games.</p>
      <div id="awareness-quiz"></div>
    </div>
    <div style="display:flex;justify-content:space-between">
      <button class="btn btn-ghost" onclick="prevStep(6)">← Back</button>
      <button class="btn btn-primary" id="quiz-submit" onclick="submitAwarenessQuiz()" disabled>Submit Answers</button>
    </div>
    <div id="quiz-result" style="margin-top:1.5rem"></div>
  </div>
</div>

<div class="toast-wrap" id="toast-wrap"></div>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>
// ── STEP NAV ──
let currentStep = 1;
function nextStep(from) {
  document.getElementById('step-'+from).classList.remove('active');
  document.getElementById('pip-'+from).classList.remove('active');
  document.getElementById('pip-'+from).classList.add('done');
  currentStep = from + 1;
  document.getElementById('step-'+currentStep).classList.add('active');
  document.getElementById('pip-'+currentStep).classList.add('active');
  window.scrollTo(0,0);
  if (currentStep === 6) buildAwarenessQuiz();
}
function prevStep(from) {
  document.getElementById('step-'+from).classList.remove('active');
  document.getElementById('pip-'+from).classList.remove('active');
  document.getElementById('pip-'+(from-1)).classList.remove('done');
  document.getElementById('pip-'+(from-1)).classList.add('active');
  currentStep = from - 1;
  document.getElementById('step-'+currentStep).classList.add('active');
  window.scrollTo(0,0);
}

// ── HYGIENE CHECKLIST ──
let hygieneChecked = new Set();
const hygieneTotal = 10;
function toggleHygiene(i) {
  const el = document.getElementById('hi-'+i);
  const ch = document.getElementById('hc-'+i);
  if (hygieneChecked.has(i)) {
    hygieneChecked.delete(i);
    el.classList.remove('checked');
    ch.textContent = '';
  } else {
    hygieneChecked.add(i);
    el.classList.add('checked');
    ch.textContent = '✓';
  }
  const pct = (hygieneChecked.size / hygieneTotal) * 100;
  document.getElementById('hygiene-bar').style.width = pct + '%';
  document.getElementById('hygiene-count').textContent = hygieneChecked.size + '/' + hygieneTotal;
  if (hygieneChecked.size >= hygieneTotal) {
    document.getElementById('hygiene-next').disabled = false;
    document.getElementById('hygiene-note').style.display = 'flex';
  }
}

// ── PASSWORD ANALYSER ──
function analysePW(pw) {
  const checks = {
    len: pw.length >= 14,
    up: /[A-Z]/.test(pw),
    low: /[a-z]/.test(pw),
    num: /[0-9]/.test(pw),
    sym: /[^a-zA-Z0-9]/.test(pw),
    dict: pw.length > 0 && !/password|admin|login|welcome|qwerty|abc|123|company|iloveyou/i.test(pw)
  };
  const score = Object.values(checks).filter(Boolean).length;
  const pct = (score / 6) * 100;
  const bar = document.getElementById('pw-bar');
  const lbl = document.getElementById('pw-label');
  bar.style.width = pct + '%';
  if (pct < 34) { bar.style.background='var(--red)'; lbl.textContent='❌ Very Weak'; lbl.style.color='var(--red)'; }
  else if (pct < 67) { bar.style.background='var(--yellow)'; lbl.textContent='⚠ Moderate'; lbl.style.color='var(--yellow)'; }
  else if (pct < 100) { bar.style.background='var(--green)'; lbl.textContent='🟢 Strong'; lbl.style.color='var(--green)'; }
  else { bar.style.background='var(--accent)'; lbl.textContent='💪 Excellent!'; lbl.style.color='var(--accent)'; }
  Object.entries(checks).forEach(([k,v]) => {
    const el = document.getElementById('cr-'+k);
    if (el) el.textContent = v ? '✅' : '❌';
  });
}

// ── AWARENESS QUIZ ──
const quizQs = [
  {q:'What percentage of data breaches involve a human element?', opts:['22%','45%','82%','99%'], ans:2, exp:'82% of breaches involve phishing, social engineering, or human error — technology alone cannot protect you.'},
  {q:'You receive an unexpected email from "paypa1.com" asking you to verify your account urgently. What do you do?', opts:['Click the link and verify','Reply asking for more info','Delete it and report it to IT as phishing','Call PayPal on the number in the email'], ans:2, exp:'The sender domain "paypa1.com" is spoofed. Report to IT without clicking anything. Never call numbers given in suspect emails.'],
  {q:'Which password is strongest?', opts:['P@ssword123','correct-horse-battery-staple-77','MyC0mpanyN4me!','Adm1n#2024'], ans:1, exp:'Long passphrases with random words are far stronger than short complex passwords. Length beats complexity every time.'},
  {q:'Your colleague calls asking for your password to finish an urgent project while you\'re out. What do you do?', opts:['Give it to them — you trust them','Email it encrypted','Refuse — offer to log in yourself remotely','Tell them to ask IT for temporary access'], ans:3, exp:'Never share credentials with anyone. The correct answer is directing them to IT for proper temporary access procedures.'],
  {q:'You find a USB drive labelled "Salary Information 2024" in the car park. What do you do?', opts:['Plug it in to find who it belongs to','Hand it to IT without plugging it in','Put it in the bin','Leave it where it is'], ans:1, exp:'USB drives can be weaponised. Never plug in unknown media. Hand it to IT/Security who have safe methods to investigate.'},
  {q:'What does MFA stand for and what does it protect against?', opts:['Multi-File Access — protects against ransomware','Maximum Firewall Authentication — blocks DDoS','Multi-Factor Authentication — protects against stolen passwords','Mandatory Firewall Access — protects network perimeter'], ans:2, exp:'MFA requires a second verification step (phone/token) meaning stolen passwords alone are not enough to log in.'},
  {q:'The STOP → THINK → VERIFY framework is used for:', opts:['Network monitoring','Evaluating suspicious emails and requests before responding','Setting up MFA','Password creation'], ans:1, exp:'STOP-THINK-VERIFY is the mental framework for handling any unexpected email, message, or request: pause, assess, then independently confirm.'},
  {q:'An email from your CEO asks you to urgently wire £10,000 to a new supplier and keep it confidential. You should:', opts:['Process it — the CEO asked','Call the CEO on their known phone number to verify','Reply asking for an invoice first','Forward to finance to handle'], ans:1, exp:'This is a Business Email Compromise (BEC) attack. CEOs\' email accounts are often spoofed or hacked. Always call the person on a known, verified number to confirm any financial requests.'],
];

let quizAnswers = {};
function buildAwarenessQuiz() {
  const container = document.getElementById('awareness-quiz');
  if (!container) return;
  container.innerHTML = quizQs.map((q,qi) => `
    <div style="background:var(--surface2);border-radius:10px;padding:1.2rem;margin-bottom:1rem" id="qbox-${qi}">
      <div style="font-weight:600;margin-bottom:1rem;font-size:.92rem;line-height:1.5">Q${qi+1}. ${q.q}</div>
      <div class="option-list">
        ${q.opts.map((o,oi) => `
          <div class="option-item" id="qo-${qi}-${oi}" onclick="selectAwarenessOpt(${qi},${oi})">
            <div class="option-letter">${'ABCD'[oi]}</div>${o}
          </div>`).join('')}
      </div>
    </div>`).join('');
}

function selectAwarenessOpt(qi, oi) {
  if (quizAnswers[qi] !== undefined) return;
  quizAnswers[qi] = oi;
  document.querySelectorAll(`[id^="qo-${qi}-"]`).forEach(el => el.classList.remove('selected'));
  document.getElementById(`qo-${qi}-${oi}`).classList.add('selected');
  if (Object.keys(quizAnswers).length === quizQs.length) {
    document.getElementById('quiz-submit').disabled = false;
  }
}

function submitAwarenessQuiz() {
  let correct = 0;
  quizQs.forEach((q, qi) => {
    const ans = quizAnswers[qi];
    document.querySelectorAll(`[id^="qo-${qi}-"]`).forEach(el => el.style.pointerEvents = 'none');
    if (ans === q.ans) {
      correct++;
      document.getElementById(`qo-${qi}-${ans}`).classList.add('correct');
    } else {
      if (ans !== undefined) document.getElementById(`qo-${qi}-${ans}`).classList.add('wrong');
      document.getElementById(`qo-${qi}-${q.ans}`).classList.add('correct');
    }
    document.getElementById('qbox-'+qi).insertAdjacentHTML('beforeend',
      `<div style="background:rgba(0,0,0,.2);border-radius:6px;padding:8px 12px;margin-top:8px;font-size:.78rem;color:var(--muted)">💡 ${q.exp}</div>`);
  });
  document.getElementById('quiz-submit').disabled = true;
  const pct = Math.round(correct / quizQs.length * 100);
  const passed = pct >= 75;
  const xp = passed ? 100 : 30;

  document.getElementById('quiz-result').innerHTML = `
    <div class="result-box">
      <div class="result-score" style="color:${passed?'var(--green)':'var(--red)'}">${pct}%</div>
      <div class="result-pct">${correct}/${quizQs.length} correct</div>
      <div class="result-xp">+${xp} XP Earned</div>
      <div class="result-msg">${passed
        ? '🛡 Excellent! You have a solid foundation in cyber awareness. The other training modules are now unlocked on your dashboard.'
        : '⚠️ You need 75% to pass. Review the sections above and try the quiz again. Don\'t worry — learning from mistakes is part of training!'
      }</div>
      ${passed
        ? '<a href="<?= APP_URL ?>/dashboard.php" class="btn btn-primary mt-2">Continue to Dashboard →</a>'
        : '<button class="btn btn-ghost mt-2" onclick="retryQuiz()">Retry Quiz</button>'
      }
    </div>`;

  // Save score
  fetch('<?= APP_URL ?>/api/save-score.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ game_slug:'awareness', score:correct, max_score:quizQs.length, xp_earned:xp, percentage:pct })
  }).then(r => r.json()).then(d => {
    if (d.badge) showToast('🏅 Badge earned: ' + d.badge, 'success');
  });
}

function retryQuiz() {
  quizAnswers = {};
  document.getElementById('quiz-result').innerHTML = '';
  buildAwarenessQuiz();
  document.getElementById('quiz-submit').disabled = true;
}
</script>
</body>
</html>
