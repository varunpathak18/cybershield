<?php
$pageTitle = 'The Suspicious Call';
require_once dirname(__DIR__) . '/includes/header.php';
$gameRow = queryOne("SELECT * FROM games WHERE slug='phone-scam'");
$gameId  = $gameRow['id'];
$userId  = $user['id'];
$already = getBestScore($userId, $gameId);
?>
<div class="container" style="max-width:900px">
  <a href="<?= APP_URL ?>/dashboard.php" style="color:var(--muted);font-size:.85rem;display:inline-flex;align-items:center;gap:4px;margin-bottom:1rem">← Dashboard</a>
  <div class="page-title">📞 The Suspicious Call</div>
  <p class="page-sub">You will receive three simulated phone calls. Listen carefully — real audio will play via your speakers. Identify the manipulation tactics and choose the correct response.</p>

  <?php if ($already): ?>
    <div class="alert alert-success mb-2">
      <span class="alert-icon">✓</span>Best score: <strong><?= round($already['percentage']) ?>%</strong> — <?= number_format($already['xp_earned']) ?> XP earned. You can replay to improve.
    </div>
  <?php endif; ?>

  <div id="game-container">
    <!-- CALL SCENARIO DISPLAY -->
    <div id="scenario-area">
      <div id="pre-call">
        <!-- Scenarios played in sequence -->
        <div class="card mb-2">
          <div class="section-title">📋 Scenario <span id="scenario-num">1</span> of 3</div>
          <div id="scenario-context" class="room-story"></div>
        </div>

        <!-- Phone UI -->
        <div class="phone-screen" id="phone-ui">
          <div class="phone-status">
            <span>📶 4G</span><span id="clock-display">--:--</span><span>🔋 87%</span>
          </div>
          <div class="phone-body">
            <div class="phone-avatar" id="caller-avatar">👤</div>
            <div class="phone-caller" id="caller-name">Loading...</div>
            <div class="phone-sub" id="caller-detail">Unknown Number</div>
            <div class="phone-ring-wave" id="ring-wave">
              <span style="height:8px"></span><span style="height:8px"></span>
              <span style="height:8px"></span><span style="height:8px"></span>
              <span style="height:8px"></span><span style="height:8px"></span>
            </div>
            <div class="phone-incoming" id="call-status">Incoming call...</div>
            <div id="call-timer-display" style="display:none">
              <div class="call-timer" id="call-duration">00:00</div>
            </div>
          </div>
          <div class="phone-actions">
            <button class="phone-btn decline" id="btn-decline" onclick="declineCall()" title="Decline">📵</button>
            <button class="phone-btn answer" id="btn-answer" onclick="answerCall()" title="Answer">📞</button>
          </div>
        </div>

        <!-- Transcript appears during call -->
        <div class="card mt-2" id="transcript-card" style="display:none">
          <div class="section-title" style="font-size:.82rem">📄 Call Transcript (auto-generated)</div>
          <div class="call-transcript" id="transcript-area"></div>
          <div class="alert alert-warn mt-2" id="call-hint" style="display:none">
            <span class="alert-icon">⚠</span>
            <span id="call-hint-text"></span>
          </div>
        </div>
      </div>
    </div>

    <!-- QUESTIONS after call -->
    <div id="questions-area" style="display:none">
      <div class="card mb-2">
        <div style="font-size:1rem;font-weight:700;margin-bottom:1rem">📋 Call Assessment — Scenario <span id="q-scenario-num">1</span></div>
        <div id="question-container"></div>
        <button class="btn btn-primary mt-2" id="submit-q-btn" onclick="submitScenarioAnswers()" disabled>Submit Answers</button>
        <div id="q-result" style="margin-top:1rem"></div>
      </div>
    </div>

    <!-- FINAL RESULT -->
    <div id="final-result" style="display:none">
      <div class="result-box">
        <div style="font-size:3rem;margin-bottom:.5rem">📞</div>
        <div class="result-score" id="final-score-display"></div>
        <div class="result-pct" id="final-pct-display"></div>
        <div class="result-xp" id="final-xp-display"></div>
        <div class="result-msg" id="final-msg"></div>
        <div style="display:flex;gap:1rem;justify-content:center;margin-top:1.5rem">
          <button class="btn btn-ghost" onclick="restartGame()">🔄 Replay</button>
          <a href="<?= APP_URL ?>/dashboard.php" class="btn btn-primary">Dashboard →</a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="toast-wrap" id="toast-wrap"></div>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>
// ═══════════════════════════════════════════
// PHONE CALL GAME ENGINE
// ═══════════════════════════════════════════
const scenarios = [
  {
    id: 1,
    context: 'Your office phone rings. The display shows: <strong style="color:var(--yellow)">+44 0800 111 2468</strong> — an 0800 number you don\'t recognise.',
    callerName: 'IT Support Helpdesk',
    callerDetail: '+44 0800 111 2468',
    callerAvatar: '💻',
    callHint: 'Notice: The caller asks for your password and wants remote access. Your company\'s real IT team is at extension 2100.',
    script: [
      {speaker:'CALLER', text:"Hello, this is David from the IT Support Helpdesk. Am I speaking with an employee of Meridian Solutions?"},
      {speaker:'CALLER', text:"Perfect. I'm calling because our monitoring systems have detected some suspicious activity originating from your workstation. We're seeing signs of malware."},
      {speaker:'CALLER', text:"I need to act on this urgently — if we don't resolve this in the next 20 minutes, your account will be locked out and you could lose all your files."},
      {speaker:'CALLER', text:"What I need you to do is visit this website: remote-support-access dot net, and download the tool there. It'll give us access to clean your machine."},
      {speaker:'CALLER', text:"While that's downloading, can I also take your network username and password so we can verify your identity on our system?"},
      {speaker:'CALLER', text:"I understand you might be cautious, but I assure you this is legitimate. Every second we delay, the malware is spreading to more of your files."},
    ],
    questions: [
      {q:'What type of social engineering attack is this?', opts:['Phishing email','Vishing (voice phishing call)','Baiting','Tailgating'], ans:1},
      {q:'How many manipulation tactics did the caller use? Select the most complete answer.', opts:['1 — claiming there\'s a problem','2 — problem + urgency','4 — urgency, authority, fear, credential harvesting','Just urgency and authority'], ans:2},
      {q:'What should you do in this situation?', opts:['Install the software — IT called, so it\'s legitimate','Give your password — they need to verify identity','Hang up and call IT on the official internal number (ext. 2100)','Ask them to send an email instead'], ans:2},
      {q:'Why is "remote-support-access dot net" suspicious?', opts:['It has "remote" in the name','It\'s not your company\'s official domain — legitimate IT uses company systems','Remote access tools are always malware','It uses dots instead of slashes'], ans:1},
    ]
  },
  {
    id: 2,
    context: 'Your mobile rings. The screen shows: <strong style="color:var(--yellow)">HSBC Bank — 0800 520 3040</strong>. You don\'t remember contacting your bank today.',
    callerName: 'HSBC Fraud Team',
    callerDetail: 'HSBC Bank · 0800 520 3040',
    callerAvatar: '🏦',
    callHint: 'Be aware: Banks will never ask for your full PIN, password, or ask you to transfer money to a "safe account."',
    script: [
      {speaker:'CALLER', text:"Good afternoon. This is Sarah calling from HSBC Fraud Prevention. I hope you're well today."},
      {speaker:'CALLER', text:"I'm calling because we've detected a suspicious transaction on your account — a payment of £2,340 to an overseas retailer that was flagged by our systems."},
      {speaker:'CALLER', text:"Before I can tell you more, I need to verify your identity. Can you confirm your full account number and your 6-digit online banking password?"},
      {speaker:'CALLER', text:"Also, our security team has identified that your account may be compromised from the bank's side. We need to move your funds to a temporary safe account while we investigate."},
      {speaker:'CALLER', text:"If you can authorise that transfer now, your money will be protected. Our safe account number is 40-22-11, account 87654321."},
      {speaker:'CALLER', text:"This is urgent — if you don't act now, those suspicious transactions will continue to drain your account."},
    ],
    questions: [
      {q:'What type of fraud is this call?', opts:['Legitimate bank security call','Authorised Push Payment (APP) fraud / bank impersonation scam','Ransomware delivery','SQL injection'], ans:1},
      {q:'The biggest red flag in this call is:', opts:['They knew your bank name','They asked you to transfer money to a "safe account" — banks never do this','The call came from an 0800 number','They mentioned a transaction amount'], ans:1},
      {q:'If the bank calls you unexpectedly, what should you do?', opts:['Provide your details — caller ID shows it\'s from the bank','Hang up and call the number on the back of your bank card','Transfer your funds to stay safe','Ask them to send a letter instead'], ans:1},
      {q:'Can caller ID be trusted to verify a caller\'s identity?', opts:['Yes — if it shows the bank name it must be them','No — caller ID can be spoofed to show any number or name','Only on mobile phones, not landlines','Yes, always trust caller ID'], ans:1},
    ]
  },
  {
    id: 3,
    context: 'Someone knocks on your office door. It\'s an unfamiliar person in a high-visibility vest carrying a large box. They explain they have a delivery for the server room and their access badge isn\'t working.',
    callerName: 'Delivery Person',
    callerDetail: 'Arrived at reception — no appointment',
    callerAvatar: '📦',
    callHint: 'Physical security matters: Letting an unauthorised person into a secure area is a serious breach, regardless of how legitimate they appear.',
    script: [
      {speaker:'CALLER', text:"Hi there! Sorry to bother you. I've got an urgent hardware delivery for your server room from Dell Technologies."},
      {speaker:'CALLER', text:"My access badge reader keeps failing — I think the battery's dead. I've been trying for 20 minutes and I've got five more stops after this."},
      {speaker:'CALLER', text:"Could you just buzz me through? I'll sign the delivery note with you, it'll only take 2 minutes. My manager's going to kill me if I miss this slot."},
      {speaker:'CALLER', text:"Look, I can show you my employee ID if that helps. Dave Jenkins, ProDelivery Ltd. I do this route every week, the guys on floor 3 know me well."},
      {speaker:'CALLER', text:"Come on, it's just a box of network switches. What's the worst that could happen? I'm not going to steal anything, am I?"},
    ],
    questions: [
      {q:'What type of attack is this?', opts:['Phishing','Tailgating / Piggybacking (physical security breach)','Vishing','Business Email Compromise'], ans:1},
      {q:'The delivery person shows you a company ID card. Does this mean you should let them in?', opts:['Yes — they showed ID so they\'re verified','No — ID cards can be faked; verify through official channels','Yes if it has a photo','Only if you recognise the company name'], ans:1},
      {q:'What is the correct action?', opts:['Let them in and escort them yourself','Tell them to wait and call your IT/facilities team to verify the delivery','Accept the delivery at the door and don\'t let them in','Ask reception to check their credentials'], ans:3},
      {q:'Why is physical access to a server room particularly dangerous?', opts:['It isn\'t — servers are protected by encryption','An attacker with physical access can install hardware keyloggers, plug in rogue devices, or steal hard drives','Physical access is less dangerous than cyber access','Servers require login credentials so access doesn\'t matter'], ans:1},
    ]
  }
];

// ═══ GAME STATE ═══
let currentScenario = 0;
let scenarioAnswers = {};
let totalScore = 0;
let totalMaxScore = 0;
let callTimer = null;
let callSeconds = 0;
let speechSynthLoaded = false;

// ═══ INIT ═══
function initScenario(idx) {
  const s = scenarios[idx];
  scenarioAnswers = {};
  document.getElementById('scenario-num').textContent = s.id;
  document.getElementById('scenario-context').innerHTML = s.context;
  document.getElementById('caller-name').textContent = s.callerName;
  document.getElementById('caller-detail').textContent = s.callerDetail;
  document.getElementById('caller-avatar').textContent = s.callerAvatar;
  document.getElementById('call-status').textContent = 'Incoming call...';
  document.getElementById('ring-wave').style.display = 'flex';
  document.getElementById('call-timer-display').style.display = 'none';
  document.getElementById('btn-answer').style.display = 'block';
  document.getElementById('btn-decline').style.display = 'block';
  document.getElementById('transcript-card').style.display = 'none';
  document.getElementById('questions-area').style.display = 'none';
  document.getElementById('scenario-area').style.display = 'block';
  playRingtone();
}

// ═══ RINGTONE (Web Audio API) ═══
let audioCtx = null;
function playRingtone() {
  try {
    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    let t = audioCtx.currentTime;
    for (let r = 0; r < 3; r++) {
      [480, 440].forEach((freq, fi) => {
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain); gain.connect(audioCtx.destination);
        osc.frequency.value = freq;
        osc.type = 'sine';
        const start = t + r * 2 + fi * 0.2;
        gain.gain.setValueAtTime(0, start);
        gain.gain.linearRampToValueAtTime(0.18, start + 0.02);
        gain.gain.linearRampToValueAtTime(0, start + 0.35);
        osc.start(start); osc.stop(start + 0.4);
      });
    }
  } catch(e) {}
}

// ═══ ANSWER CALL ═══
function answerCall() {
  if (audioCtx) { try { audioCtx.close(); } catch(e){} }
  const s = scenarios[currentScenario];
  document.getElementById('ring-wave').style.display = 'none';
  document.getElementById('call-status').textContent = 'Connected';
  document.getElementById('call-status').style.color = 'var(--green)';
  document.getElementById('btn-answer').style.display = 'none';
  document.getElementById('btn-decline').innerHTML = '📵';
  document.getElementById('btn-decline').onclick = endCall;
  document.getElementById('call-timer-display').style.display = 'block';
  document.getElementById('transcript-card').style.display = 'block';
  document.getElementById('call-hint').style.display = 'flex';
  document.getElementById('call-hint-text').textContent = s.callHint;
  startCallTimer();
  playScript(s.script, 0);
  showToast('📞 Call connected — listen carefully!', 'info');
}

function declineCall() {
  if (audioCtx) { try { audioCtx.close(); } catch(e){} }
  showToast('You declined the call — in a real scenario, consider if that\'s always the right move.', 'info');
  setTimeout(() => moveToQuestions(), 1500);
}

// ═══ CALL TIMER ═══
function startCallTimer() {
  callSeconds = 0;
  callTimer = setInterval(() => {
    callSeconds++;
    const m = String(Math.floor(callSeconds/60)).padStart(2,'0');
    const s = String(callSeconds%60).padStart(2,'0');
    document.getElementById('call-duration').textContent = `${m}:${s}`;
  }, 1000);
}

function endCall() {
  clearInterval(callTimer);
  window.speechSynthesis && window.speechSynthesis.cancel();
  document.getElementById('call-status').textContent = 'Call ended';
  document.getElementById('call-status').style.color = 'var(--muted)';
  document.getElementById('btn-decline').style.display = 'none';
  addTranscriptLine('system', '📵 Call ended');
  setTimeout(() => moveToQuestions(), 800);
}

// ═══ TEXT TO SPEECH (actual audio!) ═══
function playScript(lines, idx) {
  if (idx >= lines.length) {
    setTimeout(() => {
      addTranscriptLine('system', '📵 Call ending...');
      endCall();
    }, 1000);
    return;
  }
  const line = lines[idx];
  addTranscriptLine('caller', line.text);

  if (!window.speechSynthesis) {
    setTimeout(() => playScript(lines, idx + 1), 2000);
    return;
  }

  const utterance = new SpeechSynthesisUtterance(line.text);
  utterance.rate = 0.92;
  utterance.pitch = 0.85;
  utterance.volume = 0.9;

  const voices = window.speechSynthesis.getVoices();
  const preferred = voices.find(v => v.name.includes('Google UK English Male') || v.name.includes('Daniel') || (v.lang === 'en-GB' && v.name.includes('Male')))
    || voices.find(v => v.lang === 'en-GB')
    || voices.find(v => v.lang.startsWith('en-'));
  if (preferred) utterance.voice = preferred;

  utterance.onend = () => setTimeout(() => playScript(lines, idx + 1), 600);
  utterance.onerror = () => setTimeout(() => playScript(lines, idx + 1), 2000);
  window.speechSynthesis.speak(utterance);
}

function addTranscriptLine(type, text) {
  const area = document.getElementById('transcript-area');
  if (!area) return;
  const div = document.createElement('div');
  div.className = `call-line ${type}`;
  if (type === 'caller') div.innerHTML = `<strong style="color:var(--yellow)">Caller:</strong> ${text}`;
  else div.innerHTML = `<em>${text}</em>`;
  area.appendChild(div);
  area.scrollTop = area.scrollHeight;
}

// ═══ QUESTIONS ═══
function moveToQuestions() {
  document.getElementById('scenario-area').style.display = 'none';
  document.getElementById('questions-area').style.display = 'block';
  document.getElementById('q-scenario-num').textContent = scenarios[currentScenario].id;
  buildQuestions();
}

let qAnswers = {};
function buildQuestions() {
  qAnswers = {};
  const qs = scenarios[currentScenario].questions;
  document.getElementById('question-container').innerHTML = qs.map((q, qi) => `
    <div style="background:var(--surface2);border-radius:10px;padding:1.2rem;margin-bottom:1rem" id="callq-${qi}">
      <div style="font-weight:600;margin-bottom:.8rem;font-size:.9rem">Q${qi+1}. ${q.q}</div>
      <div class="option-list">
        ${q.opts.map((o, oi) => `
          <div class="option-item" id="callqo-${qi}-${oi}" onclick="selectCallQ(${qi},${oi})">
            <div class="option-letter">${'ABCD'[oi]}</div>${o}
          </div>`).join('')}
      </div>
    </div>`).join('');
}

function selectCallQ(qi, oi) {
  if (qAnswers[qi] !== undefined) return;
  qAnswers[qi] = oi;
  document.querySelectorAll(`[id^="callqo-${qi}-"]`).forEach(el => el.classList.remove('selected'));
  document.getElementById(`callqo-${qi}-${oi}`).classList.add('selected');
  const qs = scenarios[currentScenario].questions;
  if (Object.keys(qAnswers).length === qs.length) {
    document.getElementById('submit-q-btn').disabled = false;
  }
}

function submitScenarioAnswers() {
  const qs = scenarios[currentScenario].questions;
  let correct = 0;
  qs.forEach((q, qi) => {
    const ans = qAnswers[qi];
    document.querySelectorAll(`[id^="callqo-${qi}-"]`).forEach(el => el.style.pointerEvents='none');
    if (ans === q.ans) { correct++; document.getElementById(`callqo-${qi}-${ans}`).classList.add('correct'); }
    else {
      if (ans !== undefined) document.getElementById(`callqo-${qi}-${ans}`).classList.add('wrong');
      document.getElementById(`callqo-${qi}-${q.ans}`).classList.add('correct');
    }
  });
  totalScore += correct;
  totalMaxScore += qs.length;
  document.getElementById('submit-q-btn').style.display = 'none';
  const pct = Math.round(correct / qs.length * 100);
  document.getElementById('q-result').innerHTML = `
    <div class="alert ${pct>=75?'alert-success':'alert-warn'}">
      <span class="alert-icon">${pct>=75?'✅':'⚠'}</span>
      <div><strong>${correct}/${qs.length} correct (${pct}%)</strong> on this call.
        ${currentScenario < scenarios.length-1
          ? '<button class="btn btn-primary btn-sm" style="margin-left:1rem" onclick="nextScenario()">Next Call →</button>'
          : '<button class="btn btn-primary btn-sm" style="margin-left:1rem" onclick="showFinalResult()">See Results →</button>'}
      </div>
    </div>`;
}

function nextScenario() {
  currentScenario++;
  document.getElementById('q-result').innerHTML = '';
  document.getElementById('submit-q-btn').style.display = 'block';
  document.getElementById('submit-q-btn').disabled = true;
  initScenario(currentScenario);
}

function showFinalResult() {
  document.getElementById('questions-area').style.display = 'none';
  const pct = Math.round(totalScore / totalMaxScore * 100);
  const xp = Math.round(pct / 100 * 250);
  document.getElementById('final-score-display').textContent = pct + '%';
  document.getElementById('final-score-display').style.color = pct>=75 ? 'var(--green)' : 'var(--yellow)';
  document.getElementById('final-pct-display').textContent = `${totalScore}/${totalMaxScore} correct`;
  document.getElementById('final-xp-display').textContent = `+${xp} XP Earned`;
  document.getElementById('final-msg').textContent = pct >= 80
    ? '📞 Excellent! You recognise vishing and social engineering tactics. You are a human firewall.'
    : pct >= 60
    ? '⚠️ Good effort. Remember: when in doubt, hang up and call back on a verified official number. Urgency is always a red flag.'
    : '❌ Social engineering by phone is one of the most effective attack vectors. The golden rule: NEVER give credentials or grant remote access to unexpected callers.';
  document.getElementById('final-result').style.display = 'block';

  fetch('<?= APP_URL ?>/api/save-score.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ game_slug:'phone-scam', score:totalScore, max_score:totalMaxScore, xp_earned:xp, percentage:pct })
  }).then(r=>r.json()).then(d => {
    if(d.badge) showToast('🏅 Badge earned: ' + d.badge, 'success');
  });
}

function restartGame() {
  currentScenario = 0;
  totalScore = 0;
  totalMaxScore = 0;
  document.getElementById('final-result').style.display = 'none';
  initScenario(0);
}

// ═══ CLOCK ═══
function updateClock() {
  const now = new Date();
  document.getElementById('clock-display').textContent =
    now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
}
updateClock(); setInterval(updateClock, 1000);

// Load voices then start
if (window.speechSynthesis) {
  window.speechSynthesis.getVoices();
  window.speechSynthesis.onvoiceschanged = () => {};
}
initScenario(0);
</script>
</body>
</html>
