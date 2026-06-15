<?php
$pageTitle = 'Phishing Email Detective';
require_once dirname(__DIR__) . '/includes/header.php';
$gameRow = queryOne("SELECT * FROM games WHERE slug='phishing-email'");
$gameId  = $gameRow['id'];
$userId  = $user['id'];
$already = getBestScore($userId, $gameId);
?>
<div class="container" style="max-width:1100px">
  <a href="<?= APP_URL ?>/dashboard.php" style="color:var(--muted);font-size:.85rem;display:inline-flex;align-items:center;gap:4px;margin-bottom:1rem">← Dashboard</a>
  <div class="page-title">🎣 Phishing Email Detective</div>
  <p class="page-sub">You have 7 emails in your inbox. Identify which are phishing attacks and which are legitimate. For phishing emails, find all the hidden red flags.</p>
  <?php if ($already): ?>
    <div class="alert alert-success mb-2"><span class="alert-icon">✓</span>Best score: <strong><?= round($already['percentage']) ?>%</strong>. Replay to beat your high score.</div>
  <?php endif; ?>

  <div class="card mb-2" style="padding:.8rem 1rem">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem">
      <div style="display:flex;align-items:center;gap:1rem;font-size:.82rem">
        <span>Answered: <strong id="answered-count" style="color:var(--accent)">0</strong>/7</span>
        <span>Flags found: <strong id="flags-found" style="color:var(--red)">0</strong></span>
      </div>
      <button class="btn btn-primary btn-sm" onclick="submitAll()" id="submit-all" disabled>Submit All Answers</button>
    </div>
  </div>

  <div class="email-client" id="email-client">
    <!-- EMAIL SIDEBAR -->
    <div class="email-sidebar">
      <div class="email-toolbar">📥 Inbox <span style="background:var(--accent2);color:white;border-radius:10px;padding:1px 7px;margin-left:4px;font-size:.7rem">7</span></div>
      <div id="email-list"></div>
    </div>
    <!-- EMAIL CONTENT PANE -->
    <div class="email-pane" id="email-pane">
      <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--muted);flex-direction:column;gap:8px;padding:2rem;text-align:center">
        <div style="font-size:2rem">📧</div>
        <div>Select an email from the inbox to read it</div>
        <div style="font-size:.78rem">For phishing emails: hover over red highlights to see why they're suspicious, then click "Mark as Phishing"</div>
      </div>
    </div>
  </div>

  <div id="results-area" style="margin-top:2rem;display:none">
    <div class="result-box" id="main-result"></div>
    <div id="breakdown" style="margin-top:1.5rem"></div>
  </div>
</div>

<div class="toast-wrap" id="toast-wrap"></div>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>
// ═══ EMAIL DATA ═══
const emails = [
  {
    id: 0, from: 'security@paypa1.com', displayFrom: 'PayPal Security <security@paypa1.com>',
    subject: '⚠️ Urgent: Your PayPal account has been limited',
    time: 'Fri 09:14', isPhishing: true,
    redFlags: [
      {text:'paypa1.com', tip:'Sender domain uses the number "1" instead of the letter "l" — a classic spoofed domain'},
      {text:'Your PayPal account has been limited', tip:'Creates alarm to trigger a panic response'},
      {text:'limited to 24 hours', tip:'Artificial time pressure to stop you thinking clearly'},
      {text:'http://paypa1-secure-verify.ru/account', tip:'Link goes to a .ru (Russian) domain, not paypal.com — hover always to see real URL'},
      {text:'permanent suspension', tip:'Threat of severe consequence to force compliance'},
    ],
    body: `<div class="email-logo" style="color:#003087">🅿 PayPal</div>
<p>Dear Customer,</p>
<p>We have detected <strong>suspicious activity</strong> on your account originating from a new device. Your <span class="rf-span" data-flag="1">PayPal account has been limited</span> and your access will be <span class="rf-span" data-flag="4">limited to 24 hours</span> if you do not verify your information.</p>
<p>To restore your account, please click the link below and confirm your details:</p>
<p style="text-align:center;margin:1.2rem 0"><a href="#" class="rf-span" data-flag="3" style="background:var(--accent2);color:white;padding:10px 20px;border-radius:6px;font-size:.88rem">Verify My Account Now</a></p>
<p style="font-size:.76rem;word-break:break-all;color:var(--muted)">Link: <span class="rf-span" data-flag="3">http://paypa1-secure-verify.ru/account</span></p>
<p>Failure to verify within 24 hours will result in <span class="rf-span" data-flag="4">permanent suspension</span> of your account.</p>
<p>PayPal Security Team</p>`,
    clues: ['Sender is paypa1.com (number 1, not L)','Link goes to .ru domain','Creates extreme urgency with 24h deadline','Generic "Dear Customer" — real PayPal uses your name','Threats of permanent suspension']
  },
  {
    id: 1, from: 'noreply@github.com', displayFrom: 'GitHub <noreply@github.com>',
    subject: 'Your monthly GitHub activity summary — June 2024',
    time: 'Fri 08:41', isPhishing: false, redFlags: [],
    body: `<div class="email-logo">🐙 GitHub</div>
<p>Hi <?= htmlspecialchars(explode(' ',$user['full_name'])[0]) ?>,</p>
<p>Here's your activity summary for June 2024:</p>
<ul style="padding-left:1.4rem;line-height:2">
  <li>28 commits pushed across 4 repositories</li>
  <li>6 pull requests merged</li>
  <li>3 issues closed</li>
  <li>2 repositories starred</li>
</ul>
<p>View your full contribution graph at <a href="#" style="color:var(--accent)">github.com/dashboard</a></p>
<p>Keep contributing — every commit counts!</p>
<p style="color:var(--muted);font-size:.78rem">You're receiving this because you opted in to GitHub activity summaries. Unsubscribe at github.com/settings/notifications</p>`,
    clues: []
  },
  {
    id: 2, from: 'it.support@corp-helpdesk.biz', displayFrom: 'IT Helpdesk <it.support@corp-helpdesk.biz>',
    subject: 'ACTION REQUIRED: Password expiry — reset NOW to avoid lockout',
    time: 'Fri 10:07', isPhishing: true,
    redFlags: [
      {text:'corp-helpdesk.biz', tip:'Your company\'s real IT domain would be on your official intranet. ".biz" is suspicious for a corporate IT department.'},
      {text:'PASSWORD EXPIRES TODAY', tip:'All caps to create panic and urgency — a key social engineering technique'},
      {text:'http://corp-helpdesk.biz/reset', tip:'Not your company\'s actual domain. Real IT uses your company\'s own systems.'},
      {text:'within the next 60 minutes', tip:'Extremely short timeframe designed to bypass rational thinking'},
      {text:'contact your line manager', tip:'Discouraging you from seeking verification — red flag!'},
    ],
    body: `<div class="email-logo" style="color:var(--accent)">🔒 IT Helpdesk</div>
<p>Dear Employee,</p>
<p>⚠️ <strong>IMPORTANT: <span class="rf-span" data-flag="1">PASSWORD EXPIRES TODAY</span></strong></p>
<p>Our systems show your corporate network password is due to expire. To avoid losing access to <strong>all company systems including email, Slack, and file storage</strong>, you MUST reset it <span class="rf-span" data-flag="3">within the next 60 minutes</span>.</p>
<p>Click below to reset your password:</p>
<p style="text-align:center;margin:1.2rem 0"><a href="#" class="rf-span" data-flag="2" style="background:var(--red);color:white;padding:10px 20px;border-radius:6px;font-size:.88rem">Reset Password Now</a></p>
<p style="font-size:.76rem;color:var(--muted)">Direct link: <span class="rf-span" data-flag="2">http://corp-helpdesk.biz/reset?token=emp7743</span></p>
<p>Do not reply to this email and do not <span class="rf-span" data-flag="4">contact your line manager</span> about this as they have already been notified.</p>
<p>IT Helpdesk — <span class="rf-span" data-flag="0">corp-helpdesk.biz</span></p>`,
    clues: ['Domain "corp-helpdesk.biz" is not company IT','ALL CAPS urgency language','Link goes to external domain','Unreasonably short 60-minute window','Specifically tells you not to verify with your manager']
  },
  {
    id: 3, from: 'orders@amazon.co.uk', displayFrom: 'Amazon <orders@amazon.co.uk>',
    subject: 'Your Amazon order #205-3947812-4829361 has dispatched',
    time: 'Thu 15:33', isPhishing: false, redFlags: [],
    body: `<div class="email-logo" style="color:#FF9900">📦 amazon</div>
<p>Hello,</p>
<p>Your order has been dispatched and is on its way!</p>
<div style="background:var(--surface2);border-radius:8px;padding:1rem;margin:1rem 0">
  <div style="font-size:.82rem;color:var(--muted)">Order #205-3947812-4829361</div>
  <div style="font-weight:600;margin:.4rem 0">Logitech MX Keys Wireless Keyboard</div>
  <div style="font-size:.82rem">Estimated delivery: <strong>Tomorrow, before 10pm</strong></div>
</div>
<p><a href="#" style="color:var(--accent)">Track your package →</a></p>
<p style="font-size:.78rem;color:var(--muted);margin-top:1.5rem">This email was sent to confirm your order. If you didn't make this purchase, visit amazon.co.uk/orders and contact us from the secure site.</p>`,
    clues: []
  },
  {
    id: 4, from: 'ceo@meridian-solutions.biz', displayFrom: 'Mark Davies (CEO) <ceo@meridian-solutions.biz>',
    subject: 'Confidential — Urgent wire transfer needed',
    time: 'Thu 14:22', isPhishing: true,
    redFlags: [
      {text:'meridian-solutions.biz', tip:'CEO\'s email should come from your company\'s official domain, not a .biz lookalike'},
      {text:'£14,750', tip:'Specific large amount — BEC attacks often target urgent wire transfers'},
      {text:'40-22-11 / 87654321', tip:'Providing bank details in email for immediate transfer is a major BEC red flag'},
      {text:"Don't discuss", tip:'Requesting secrecy to prevent you from verifying through proper channels'},
      {text:'in an important client meeting', tip:'Creates reason why CEO cannot be reached to verify — classic BEC setup'},
    ],
    body: `<div class="email-logo">👔 Mark Davies</div>
<p>Hi,</p>
<p>I'm <span class="rf-span" data-flag="4">in an important client meeting</span> and can't be reached by phone right now. I need you to process an urgent payment immediately.</p>
<p>Please transfer <span class="rf-span" data-flag="1">£14,750</span> to our new supplier for an outstanding invoice:</p>
<div style="background:var(--surface2);border-radius:8px;padding:1rem;margin:1rem 0;font-family:var(--mono);font-size:.82rem">
  Bank: Barclays Business<br>
  Sort Code: <span class="rf-span" data-flag="2">40-22-11</span><br>
  Account: <span class="rf-span" data-flag="2">87654321</span><br>
  Reference: INV-9912-SUPPLIER
</div>
<p>Please do this before 3pm. <span class="rf-span" data-flag="3">Don't discuss</span> with anyone else — this is commercially sensitive and I'll explain when I'm out of the meeting.</p>
<p>Thanks,<br>Mark<br>
<span style="font-size:.76rem;color:var(--muted)"><span class="rf-span" data-flag="0">ceo@meridian-solutions.biz</span></span></p>`,
    clues: ['CEO email domain is .biz, not your company domain','Business Email Compromise (BEC) wire fraud pattern','Requests secrecy — prevents verification','Uses urgency + authority combination','Cannot be reached to verify — classic BEC setup']
  },
  {
    id: 5, from: 'donotreply@microsoftonline.com', displayFrom: 'Microsoft 365 <donotreply@microsoftonline.com>',
    subject: 'Sign-in attempt blocked on your Microsoft account',
    time: 'Thu 11:05', isPhishing: false, redFlags: [],
    body: `<div class="email-logo" style="color:#0078d4">⊞ Microsoft</div>
<p>We blocked a sign-in attempt to your Microsoft account.</p>
<div style="background:var(--surface2);border-radius:8px;padding:1rem;margin:1rem 0;font-size:.84rem">
  <div>📍 <strong>Location:</strong> Lagos, Nigeria</div>
  <div style="margin-top:4px">💻 <strong>Device:</strong> Windows PC — Chrome browser</div>
  <div style="margin-top:4px">🕐 <strong>Time:</strong> Thursday 11:02 UTC</div>
</div>
<p>If this was you, you can ignore this email. If it wasn't, your account is safe — the sign-in was blocked. You may want to change your password as a precaution.</p>
<p><a href="#" style="color:#0078d4">Review recent activity →</a></p>
<p style="font-size:.76rem;color:var(--muted)">You can manage your account security at account.microsoft.com</p>`,
    clues: []
  },
  {
    id: 6, from: 'awards@nationalprize-winner.co', displayFrom: 'National Prize Draw <awards@nationalprize-winner.co>',
    subject: '🎉 CONGRATULATIONS! You\'ve won £50,000 — Claim now!',
    time: 'Wed 16:51', isPhishing: true,
    redFlags: [
      {text:'nationalprize-winner.co', tip:'Suspicious domain — legitimate prize organisations use established domains, not newly registered ones'},
      {text:'£50,000 PRIZE', tip:'Prize you never entered — if you didn\'t enter a draw, you can\'t have won'},
      {text:'£25 processing fee', tip:'Legitimate prize draws NEVER ask winners to pay a fee — this is the "advance fee fraud" scam'},
      {text:'Western Union or gift cards', tip:'Legitimate organisations never request payment via Western Union or gift cards — untraceable payment methods used by scammers'},
      {text:'expires in 48 hours', tip:'Artificial deadline on a prize you "won" — manufactured urgency'},
    ],
    body: `<div class="email-logo" style="color:var(--yellow)">🏆 National Prize Draw</div>
<p style="text-align:center;font-size:1.2rem;font-weight:800;color:var(--yellow)">🎉 CONGRATULATIONS! 🎉</p>
<p>You have been selected as the winner of our <span class="rf-span" data-flag="1">£50,000 PRIZE</span> in our National Customer Appreciation Draw!</p>
<p>Your email address was randomly selected from millions of entries. To claim your prize, you simply need to pay a small <span class="rf-span" data-flag="2">£25 processing fee</span> to cover administrative costs.</p>
<p>Payment can be made via <span class="rf-span" data-flag="3">Western Union or gift cards</span> (Amazon, iTunes, Google Play) to our processing agent.</p>
<div style="background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);border-radius:8px;padding:1rem;margin:1rem 0">
  ⏰ This offer <span class="rf-span" data-flag="4">expires in 48 hours</span>. You must claim before the deadline or your prize will be forfeited.
</div>
<p>Reply to this email with your full name, address, phone number, and date of birth to begin the claiming process.</p>
<p>From: <span class="rf-span" data-flag="0">nationalprize-winner.co</span></p>`,
    clues: ['Entered no draw but "won" — impossible','Domain "nationalprize-winner.co" is suspicious','Requires an upfront fee — advance fee fraud','Requests payment via gift cards — untraceable','Wants personal data (DOB, address) for identity theft']
  }
];

// ═══ GAME STATE ═══
let selectedEmail = null;
let userAnswers = {}; // id -> 'phishing'|'legit'
let flagsFound = {}; // id -> Set of found flag indices
let totalFlagsFound = 0;

// ═══ BUILD EMAIL LIST ═══
function buildEmailList() {
  const list = document.getElementById('email-list');
  list.innerHTML = emails.map(e => `
    <div class="email-item unread" id="ei-${e.id}" onclick="selectEmail(${e.id})">
      <div class="email-item-time">${e.time}</div>
      <div class="email-item-from">${e.from}</div>
      <div class="email-item-subject">${e.subject}</div>
    </div>`).join('');
}

function selectEmail(id) {
  selectedEmail = id;
  document.querySelectorAll('.email-item').forEach(el => el.classList.remove('active'));
  document.getElementById('ei-'+id).classList.add('active');
  renderEmail(id);
}

function renderEmail(id) {
  const e = emails[id];
  const answered = userAnswers[id];
  const foundFlags = flagsFound[id] || new Set();
  const totalFlags = e.redFlags.length;
  const flagsFoundCount = foundFlags.size;

  let body = e.body;
  // Apply found/unfound styles to red flag spans
  body = body.replace(/data-flag="(\d+)"/g, (match, fi) => {
    const flagIdx = parseInt(fi);
    const isFound = foundFlags.has(flagIdx);
    return `${match} class="rf-span${isFound?' found':''}" title="${e.redFlags[flagIdx]?.tip||''}"`;
  });

  const headerHTML = `
    <div class="email-header">
      <div class="email-subject-display">${e.subject}</div>
      <div class="email-meta">
        <p>From: <span>${e.displayFrom}</span></p>
        <p>To: <span><?= htmlspecialchars($user['email']) ?></span></p>
        <p>Date: <span>${e.time}</span></p>
      </div>
      ${e.isPhishing && !answered ? `<div class="alert alert-info mt-2" style="font-size:.78rem"><span class="alert-icon">🔍</span>Click on suspicious text/links to flag red flags. Found <strong id="flag-count-${id}">${flagsFoundCount}</strong>/${totalFlags} red flags.</div>` : ''}
      ${answered ? `<div class="alert ${answered==='phishing'?'alert-danger':'alert-success'} mt-2" style="font-size:.78rem"><span class="alert-icon">${answered==='phishing'?'🚩':'✅'}</span>You marked this as <strong>${answered==='phishing'?'Phishing':'Legitimate'}</strong>${answered===( e.isPhishing?'phishing':'legit') ? ' — Correct!' : ' — Incorrect'}</div>` : ''}
    </div>
    <div class="email-body-area">${body}</div>
    <div class="email-actions">
      <button class="flag-btn flag-phish" onclick="markEmail(${id},'phishing')" ${answered?'disabled':''}>🚩 Mark as Phishing</button>
      <button class="flag-btn flag-legit" onclick="markEmail(${id},'legit')" ${answered?'disabled':''}>✅ Mark as Legitimate</button>
    </div>`;
  document.getElementById('email-pane').innerHTML = headerHTML;

  // Add click handlers for red flags
  document.querySelectorAll('.rf-span').forEach(el => {
    el.addEventListener('click', function() {
      const fi = parseInt(this.getAttribute('data-flag'));
      handleFlagClick(id, fi, this);
    });
  });
}

function handleFlagClick(emailId, flagIdx, el) {
  if (!flagsFound[emailId]) flagsFound[emailId] = new Set();
  if (flagsFound[emailId].has(flagIdx)) return; // already found

  flagsFound[emailId].add(flagIdx);
  el.classList.add('found');
  totalFlagsFound++;
  document.getElementById('flags-found').textContent = totalFlagsFound;

  const count = flagsFound[emailId].size;
  const fc = document.getElementById('flag-count-'+emailId);
  if (fc) fc.textContent = count;

  const tip = emails[emailId].redFlags[flagIdx]?.tip || 'Red flag found!';
  showToast('🚩 Red flag: ' + tip, 'error');
}

function markEmail(id, verdict) {
  if (userAnswers[id]) return;
  userAnswers[id] = verdict;

  const el = document.getElementById('ei-'+id);
  el.style.borderLeft = verdict==='phishing' ? '3px solid var(--red)' : '3px solid var(--green)';

  const answered = Object.keys(userAnswers).length;
  document.getElementById('answered-count').textContent = answered;

  const correct = verdict === (emails[id].isPhishing ? 'phishing' : 'legit');
  showToast(correct ? '✅ Correct!' : '❌ Wrong — review the clues', correct ? 'success' : 'error');

  if (answered === emails.length) {
    document.getElementById('submit-all').disabled = false;
  }
  renderEmail(id);
}

function submitAll() {
  let correct = 0;
  let breakdown = '';
  let totalXP = 0;

  emails.forEach(e => {
    const verdict = userAnswers[e.id];
    const ok = verdict === (e.isPhishing ? 'phishing' : 'legit');
    if (ok) correct++;
    const flagsF = (flagsFound[e.id]?.size || 0);
    const flagsPossible = e.redFlags.length;
    const flagScore = flagsPossible > 0 ? `Flags found: ${flagsF}/${flagsPossible}` : 'No flags (legitimate email)';

    breakdown += `
      <div style="background:var(--surface);border:1px solid ${ok?'var(--green)':'var(--red)'};border-radius:10px;padding:1rem 1.2rem;margin-bottom:8px">
        <div style="display:flex;justify-content:space-between;align-items:flex-start">
          <div>
            <div style="font-weight:600;font-size:.88rem">${e.subject}</div>
            <div style="font-size:.76rem;color:var(--muted)">From: ${e.from}</div>
          </div>
          <span style="color:${ok?'var(--green)':'var(--red)'};font-weight:700;font-size:.85rem;white-space:nowrap">${ok?'✓ Correct':'✗ Wrong'}</span>
        </div>
        <div style="margin-top:8px;font-size:.78rem;color:var(--muted)">
          Was: <strong style="color:${e.isPhishing?'var(--red)':'var(--green)'}">${e.isPhishing?'Phishing':'Legitimate'}</strong> · ${flagScore}
          ${e.isPhishing && e.clues.length ? `<div style="margin-top:4px">Key clues: ${e.clues.slice(0,3).map(c=>`<span style="background:rgba(239,68,68,.1);border-radius:4px;padding:1px 6px;margin-right:4px">${c}</span>`).join('')}</div>` : ''}
        </div>
      </div>`;
  });

  const pct = Math.round(correct / emails.length * 100);
  const flagBonus = Math.min(50, totalFlagsFound * 5);
  const score = pct + (pct/100 * flagBonus);
  const xp = Math.round(score / 100 * 250);

  document.getElementById('results-area').style.display = 'block';
  document.getElementById('main-result').innerHTML = `
    <div style="font-size:3rem;margin-bottom:.5rem">🎣</div>
    <div class="result-score" style="color:${pct>=80?'var(--green)':pct>=60?'var(--yellow)':'var(--red)'}">${pct}%</div>
    <div class="result-pct">${correct}/${emails.length} emails correctly classified · ${totalFlagsFound} red flags found</div>
    <div class="result-xp">+${xp} XP Earned</div>
    <div class="result-msg">${pct>=80?'🛡 Sharp eyes! You can spot phishing attacks reliably — a critical skill.':pct>=60?'⚠️ Good effort. Pay extra attention to sender domains and link destinations.':'❌ Phishing is the #1 attack vector. Review the breakdown below and study the red flags carefully.'}</div>`;

  document.getElementById('breakdown').innerHTML = `<div class="section-title mt-2">📋 Detailed Results</div>${breakdown}`;
  document.getElementById('results-area').scrollIntoView({behavior:'smooth'});

  fetch('<?= APP_URL ?>/api/save-score.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ game_slug:'phishing-email', score:Math.round(score), max_score:100, xp_earned:xp, percentage:Math.round(score) })
  }).then(r=>r.json()).then(d => {
    if(d.badge) showToast('🏅 Badge: ' + d.badge, 'success');
  });
}

buildEmailList();
</script>
<style>
.rf-span { background:rgba(239,68,68,.1); border-bottom:2px solid rgba(239,68,68,.4); cursor:pointer; padding:0 2px; border-radius:2px; transition:background .2s; position:relative; }
.rf-span:hover { background:rgba(239,68,68,.25); }
.rf-span.found { background:rgba(239,68,68,.3); border-bottom-color:var(--red); }
.rf-span[title]:hover::after { content:attr(title); position:fixed; background:var(--surface); border:1px solid var(--red); color:var(--text); font-size:.76rem; padding:6px 12px; border-radius:8px; max-width:260px; z-index:999; white-space:normal; line-height:1.5; pointer-events:none; transform:translateY(-110%); box-shadow:0 4px 20px rgba(0,0,0,.4); }
</style>
</body>
</html>
