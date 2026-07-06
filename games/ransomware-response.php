<?php
$pageTitle = 'Ransomware Response';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<style>
/* Desktop simulation */
.desktop-sim{background:linear-gradient(135deg,#1a237e 0%,#283593 100%);border-radius:12px;overflow:hidden;border:1px solid #333;box-shadow:0 20px 60px rgba(0,0,0,.7);position:relative;min-height:520px}
.taskbar{background:rgba(0,0,0,.7);backdrop-filter:blur(10px);padding:6px 16px;display:flex;align-items:center;justify-content:space-between;position:absolute;bottom:0;left:0;right:0;z-index:10}
.taskbar-start{background:rgba(255,255,255,.1);border:none;color:#fff;padding:5px 14px;border-radius:4px;font-size:.8rem;cursor:pointer}
.taskbar-time{color:#fff;font-size:.78rem}
.taskbar-icons{display:flex;gap:8px;align-items:center}
.taskbar-icon{width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:.9rem;cursor:pointer;border-radius:4px;transition:background .2s}
.taskbar-icon:hover{background:rgba(255,255,255,.15)}

/* Windows */
.win{position:absolute;background:#fff;border-radius:6px;box-shadow:0 8px 32px rgba(0,0,0,.5);overflow:hidden;font-size:.82rem;color:#202124}
.win-titlebar{background:#2b579a;padding:6px 10px;display:flex;align-items:center;gap:8px;color:#fff;font-size:.8rem;font-weight:600;cursor:move;user-select:none}
.win-btn{width:14px;height:14px;border-radius:50%;border:none;cursor:pointer;font-size:.6rem;display:flex;align-items:center;justify-content:center}
.win-close{background:#e74c3c}.win-min{background:#f39c12}.win-max{background:#27ae60}

/* Email client window */
.outlook-win{top:40px;left:40px;width:560px}
.outlook-toolbar{background:#f3f3f3;border-bottom:1px solid #ddd;padding:6px 12px;display:flex;gap:8px}
.ol-btn{background:#fff;border:1px solid #ccc;border-radius:3px;padding:3px 10px;font-size:.75rem;cursor:pointer}
.ol-btn:hover{background:#e1efff}
.email-view{padding:16px}
.email-field{display:flex;gap:8px;font-size:.78rem;margin-bottom:6px;border-bottom:1px solid #f0f0f0;padding-bottom:6px}
.email-field-label{color:#888;min-width:50px;font-weight:600}
.email-field-val{color:#202124}
.email-field-val.suspicious{color:#d32f2f;font-weight:600}
.email-body-text{font-size:.82rem;line-height:1.7;color:#202124;margin-top:12px}
.email-attachment{background:#f8f9fa;border:1px solid #ddd;border-radius:4px;padding:10px 14px;margin-top:12px;display:flex;align-items:center;gap:10px;cursor:pointer;transition:background .2s}
.email-attachment:hover{background:#e3f2fd}
.att-icon{font-size:1.8rem}
.att-name{font-size:.82rem;font-weight:600;color:#1565c0}
.att-size{font-size:.72rem;color:#888}

/* Download progress */
.download-bar{background:#e0e0e0;border-radius:4px;height:8px;overflow:hidden;margin:8px 0}
.download-fill{height:100%;background:#1565c0;border-radius:4px;width:0;transition:width .05s linear}

/* RANSOMWARE SCREEN */
.ransom-screen{position:absolute;inset:0;background:#000;display:flex;flex-direction:column;align-items:center;justify-content:center;z-index:100;padding:2rem;text-align:center;animation:ransomIn .3s ease}
@keyframes ransomIn{from{opacity:0;transform:scale(1.05)}to{opacity:1;transform:scale(1)}}
.ransom-skull{font-size:5rem;animation:pulse 1s ease infinite}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.1)}}
.ransom-title{font-size:1.8rem;font-weight:800;color:#ff3333;margin:.5rem 0;font-family:monospace;text-shadow:0 0 20px rgba(255,51,51,.5)}
.ransom-body{color:#cc0000;font-family:monospace;font-size:.82rem;line-height:1.8;max-width:420px;margin:.5rem auto}
.ransom-counter{font-size:2rem;font-weight:800;color:#ff6600;font-family:monospace;margin:1rem 0}
.ransom-bitcoin{background:#1a0000;border:1px solid #ff3333;border-radius:6px;padding:10px 16px;font-family:monospace;font-size:.75rem;color:#ff9999;margin:.5rem 0;word-break:break-all}

/* Stage panels */
.stage{display:none}
.stage.active{display:block}

/* Quiz */
.rq-option{display:flex;align-items:flex-start;gap:10px;padding:.75rem 1rem;border:1px solid var(--border);border-radius:8px;cursor:pointer;font-size:.86rem;background:var(--surface2);margin-bottom:8px;transition:all .2s}
.rq-option:hover{border-color:var(--accent);background:var(--surface3)}
.rq-option.correct{border-color:var(--green);background:rgba(34,197,94,.1);color:var(--green)}
.rq-option.wrong{border-color:var(--red);background:rgba(239,68,68,.08);color:var(--red)}
.rq-option.reveal{border-color:var(--green);background:rgba(34,197,94,.08)}
.rq-letter{width:26px;height:26px;border-radius:6px;background:var(--surface3);display:flex;align-items:center;justify-content:center;font-size:.74rem;font-weight:700;flex-shrink:0}

/* Notification popup */
.notif-popup{position:absolute;bottom:52px;right:16px;background:#fff;border-radius:8px;padding:12px 16px;width:280px;box-shadow:0 4px 20px rgba(0,0,0,.3);font-size:.78rem;color:#202124;animation:slideUp .3s ease;z-index:20}
@keyframes slideUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.notif-header{display:flex;align-items:center;gap:8px;font-weight:700;margin-bottom:6px;font-size:.8rem}

/* Recovery terminal */
.recovery-terminal{background:#0d1117;border-radius:8px;padding:1rem;font-family:monospace;font-size:.78rem;color:#58a6ff;line-height:2;margin:1rem 0;border:1px solid #30363d;max-height:200px;overflow-y:auto}
.rt-green{color:#3fb950}.rt-red{color:#f85149}.rt-yellow{color:#e3b341}.rt-white{color:#e6edf3}
</style>

<div class="container">
  <a href="<?= APP_URL ?>/dashboard.php" style="color:var(--muted);font-size:.85rem;display:inline-flex;align-items:center;gap:4px;margin-bottom:1rem">← Back to Dashboard</a>
  <div class="page-title">💀 Ransomware Attack Simulation</div>
  <p class="page-sub">Experience a ransomware attack from the victim's perspective. Every decision you make — or fail to make — has consequences.</p>

  <!-- PROGRESS -->
  <div style="display:flex;gap:8px;margin-bottom:1.5rem" id="stage-progress">
    <?php for($i=1;$i<=5;$i++): ?>
    <div style="flex:1;height:5px;border-radius:3px;background:var(--border);transition:background .3s" id="spip-<?=$i?>"></div>
    <?php endfor; ?>
  </div>

  <!-- ══ STAGE 1: THE EMAIL ══ -->
  <div class="stage active" id="stage-1">
    <div class="card mb-2" style="border-color:var(--yellow)">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:.5rem">
        <span style="font-size:1.4rem">📧</span>
        <div><strong>Monday morning. 8:47 AM.</strong><br><span style="color:var(--muted);font-size:.84rem">You sit down at your desk, open your email, and see this waiting in your inbox.</span></div>
      </div>
    </div>

    <div class="sim-chrome" style="margin-bottom:1.5rem">
      <div class="sim-titlebar" style="background:#2d2d2d;padding:8px 12px;display:flex;align-items:center;gap:8px">
        <div style="width:12px;height:12px;border-radius:50%;background:#ff5f57"></div>
        <div style="width:12px;height:12px;border-radius:50%;background:#febc2e"></div>
        <div style="width:12px;height:12px;border-radius:50%;background:#28c840"></div>
        <div style="flex:1;background:#3a3a3a;border-radius:6px;padding:4px 12px;font-size:.75rem;color:#aaa;margin:0 12px;font-family:monospace">🔒 mail.yourcompany.com</div>
      </div>
      <div style="background:#fff;color:#202124">
        <!-- Outlook-style header -->
        <div style="background:#2b579a;padding:8px 16px;color:#fff;font-size:.82rem;display:flex;align-items:center;gap:16px">
          <strong>Outlook</strong>
          <span style="opacity:.7">|</span>
          <span>Inbox (1 unread)</span>
        </div>
        <div style="display:flex;height:320px">
          <!-- Mini sidebar -->
          <div style="width:160px;background:#f3f3f3;border-right:1px solid #ddd;padding:8px 0;font-size:.75rem">
            <div style="padding:6px 14px;background:#d0e4ff;font-weight:700;color:#0a2463">📥 Inbox (1)</div>
            <div style="padding:6px 14px;color:#555">📤 Sent Items</div>
            <div style="padding:6px 14px;color:#555">🗑 Deleted</div>
            <div style="padding:6px 14px;color:#555">📁 Archive</div>
          </div>
          <!-- Email content -->
          <div style="flex:1;overflow-y:auto">
            <div style="padding:12px 16px;border-bottom:1px solid #e0e0e0;background:#fff">
              <div style="font-size:.9rem;font-weight:700;margin-bottom:8px">📎 Invoice_INV-2024-8821.zip — Requires your immediate attention</div>
              <div style="display:flex;gap:8px;font-size:.76rem;margin-bottom:4px"><span style="color:#888;min-width:40px">From:</span><span style="color:#d32f2f;font-weight:600">accounts@techniki-invoicing.net</span></div>
              <div style="display:flex;gap:8px;font-size:.76px;margin-bottom:4px"><span style="color:#888;min-width:40px;font-size:.76rem">To:</span><span style="font-size:.76rem">you@yourcompany.com</span></div>
              <div style="display:flex;gap:8px;font-size:.76px;margin-bottom:12px"><span style="color:#888;min-width:40px;font-size:.76rem">Date:</span><span style="font-size:.76rem">Mon 8:31 AM</span></div>
              <div style="font-size:.82rem;line-height:1.8;color:#333">
                <p>Dear Sir/Madam,</p>
                <p>Please find attached invoice <strong>INV-2024-8821</strong> for services rendered in October. Payment is overdue and must be settled <strong>within 24 hours</strong> to avoid service interruption.</p>
                <p>Please open the attached document to review the invoice details and process payment immediately.</p>
                <p>Kind regards,<br>Accounts Receivable Team<br>Techniki Finance Ltd</p>
              </div>
              <!-- Attachment -->
              <div id="attachment-btn" onclick="downloadFile()" style="background:#f0f7ff;border:1px solid #bbdefb;border-radius:4px;padding:10px 14px;margin-top:12px;display:flex;align-items:center;gap:10px;cursor:pointer;transition:background .2s;max-width:280px" onmouseover="this.style.background='#e3f2fd'" onmouseout="this.style.background='#f0f7ff'">
                <span style="font-size:1.8rem">📦</span>
                <div>
                  <div style="font-size:.82rem;font-weight:700;color:#1565c0">Invoice_INV-2024-8821.zip</div>
                  <div style="font-size:.72rem;color:#888">ZIP Archive · 847 KB · Click to open</div>
                </div>
              </div>
              <!-- Download progress (hidden) -->
              <div id="dl-progress" style="display:none;margin-top:10px;max-width:280px">
                <div style="font-size:.75rem;color:#555;margin-bottom:4px">Downloading...</div>
                <div style="background:#e0e0e0;border-radius:4px;height:6px;overflow:hidden">
                  <div id="dl-fill" style="height:100%;background:#1565c0;width:0;transition:width .08s linear"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card" style="border-color:var(--accent)">
      <div style="font-weight:700;margin-bottom:1rem;font-size:.95rem">🤔 What do you do with this email?</div>
      <div id="s1-options">
        <div class="rq-option" onclick="s1answer('open')"><div class="rq-letter">A</div>It's probably legitimate — open the ZIP file and view the invoice</div>
        <div class="rq-option" onclick="s1answer('verify')"><div class="rq-letter">B</div>Call the supplier on their known phone number to verify this invoice before opening anything</div>
        <div class="rq-option" onclick="s1answer('delete')"><div class="rq-letter">C</div>Delete it — I am not expecting an invoice from this company</div>
        <div class="rq-option" onclick="s1answer('report')"><div class="rq-letter">D</div>Report it to IT Security as suspicious without opening the attachment</div>
      </div>
    </div>
  </div>

  <!-- ══ STAGE 2: RANSOMWARE SCREEN ══ -->
  <div class="stage" id="stage-2">
    <div class="card mb-2" style="border-color:var(--red)">
      <div style="display:flex;align-items:center;gap:10px">
        <span style="font-size:1.4rem">💀</span>
        <div><strong style="color:var(--red)">You opened the attachment.</strong><br><span style="color:var(--muted);font-size:.84rem">The ZIP contained a malicious macro-enabled document. The moment it opened, code executed silently in the background.</span></div>
      </div>
    </div>

    <!-- Fake desktop then ransomware -->
    <div class="desktop-sim" id="desktop-sim" style="min-height:420px">
      <!-- Desktop icons -->
      <div style="padding:20px;display:grid;grid-template-columns:repeat(6,60px);gap:16px">
        <?php $icons = ['📁 Documents','🌐 Chrome','📊 Excel','📝 Word','🖨 Printer','📧 Outlook']; foreach($icons as $ic): ?>
        <div style="display:flex;flex-direction:column;align-items:center;gap:4px;cursor:default">
          <span style="font-size:1.8rem"><?= explode(' ',$ic)[0] ?></span>
          <span style="font-size:.62rem;color:#fff;text-shadow:1px 1px 2px #000;text-align:center"><?= explode(' ',$ic,2)[1] ?></span>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Notification popup (appears first) -->
      <div class="notif-popup" id="notif1" style="display:none">
        <div class="notif-header"><span>📊</span> Microsoft Excel</div>
        <div style="color:#555">Opening Invoice_INV-2024-8821.xlsx...<br><span style="color:#888;font-size:.72rem">Enable macros to view the document</span></div>
      </div>

      <!-- Second notification -->
      <div class="notif-popup" id="notif2" style="display:none">
        <div class="notif-header"><span>⚙️</span> Windows Security</div>
        <div style="color:#555">Macro script is running...<br><span style="color:#d32f2f;font-size:.72rem;font-weight:600">⚠ A script is attempting to access your files</span></div>
      </div>

      <!-- Ransomware overlay -->
      <div class="ransom-screen" id="ransom-screen" style="display:none">
        <div class="ransom-skull">💀</div>
        <div class="ransom-title">YOUR FILES HAVE BEEN ENCRYPTED</div>
        <div class="ransom-body">
          All your documents, photos, databases and other important files<br>
          have been encrypted with military-grade AES-256 encryption.<br><br>
          <strong style="color:#ff6600">DO NOT</strong> attempt to recover files yourself.<br>
          <strong style="color:#ff6600">DO NOT</strong> restart your computer.<br>
          <strong style="color:#ff6600">DO NOT</strong> contact law enforcement.
        </div>
        <div style="color:#ff9900;font-family:monospace;font-size:.78rem;margin:.5rem 0">Time remaining to pay ransom:</div>
        <div class="ransom-counter" id="ransom-timer">71:59:42</div>
        <div style="color:#ff9900;font-size:.8rem;font-family:monospace">Send <strong>0.058 BTC</strong> (~£3,200) to:</div>
        <div class="ransom-bitcoin">bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh</div>
        <div style="margin-top:.5rem;font-family:monospace;font-size:.72rem;color:#880000">
          Files affected: 4,847 &nbsp;|&nbsp; Encrypted: ██████████ 100%
        </div>
      </div>

      <div class="taskbar">
        <button class="taskbar-start">⊞ Start</button>
        <div class="taskbar-icons">
          <div class="taskbar-icon">📁</div>
          <div class="taskbar-icon">🌐</div>
          <div class="taskbar-icon">📧</div>
        </div>
        <div class="taskbar-time" id="taskbar-time">08:47</div>
      </div>
    </div>

    <div class="card mt-2" style="border-color:var(--red)">
      <div style="display:flex;align-items:flex-start;gap:12px">
        <span style="font-size:1.5rem;flex-shrink:0">🚨</span>
        <div>
          <div style="font-weight:700;color:var(--red);margin-bottom:.5rem">Ransomware has infected your machine. Your files are now encrypted.</div>
          <p style="font-size:.84rem;color:var(--muted);line-height:1.6">The malware is now spreading across the network. Every second counts. What should you do <strong style="color:var(--text)">immediately?</strong></p>
          <div id="s2-options" style="margin-top:1rem">
            <div class="rq-option" onclick="s2answer('pay')"><div class="rq-letter">A</div>Pay the ransom immediately to get the decryption key and recover files</div>
            <div class="rq-option" onclick="s2answer('disconnect')"><div class="rq-letter">B</div>Physically disconnect from the network (unplug ethernet, disable WiFi) and call IT immediately — do not shut down</div>
            <div class="rq-option" onclick="s2answer('restart')"><div class="rq-letter">C</div>Restart your computer to stop the encryption process</div>
            <div class="rq-option" onclick="s2answer('scan')"><div class="rq-letter">D</div>Run your antivirus to remove the malware and recover the files</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══ STAGE 3: PREVENTION ══ -->
  <div class="stage" id="stage-3">
    <div class="card mb-2" style="background:rgba(234,179,8,.06);border-color:var(--yellow)">
      <div style="font-size:1rem;font-weight:700;margin-bottom:.5rem">🔍 Post-Incident Analysis: What should have prevented this?</div>
      <p style="font-size:.84rem;color:var(--muted);line-height:1.6">The attack succeeded because of a chain of failures. Identify which single control would have <strong style="color:var(--text)">most effectively prevented</strong> this ransomware attack.</p>
    </div>

    <div id="s3-options">
      <div class="rq-option" onclick="s3answer('av')"><div class="rq-letter">A</div>Having antivirus software installed on the computer</div>
      <div class="rq-option" onclick="s3answer('training')"><div class="rq-letter">B</div>Security awareness training — recognising the email as phishing before ever opening the attachment</div>
      <div class="rq-option" onclick="s3answer('firewall')"><div class="rq-letter">C</div>Having a strong firewall configured on the network</div>
      <div class="rq-option" onclick="s3answer('password')"><div class="rq-letter">D</div>Using a stronger password on the email account</div>
    </div>
  </div>

  <!-- ══ STAGE 4: RECOVERY ══ -->
  <div class="stage" id="stage-4">
    <div class="card mb-2">
      <div style="font-size:1rem;font-weight:700;margin-bottom:.5rem">🔧 Recovery: IT are on site. What's the correct recovery order?</div>
      <p style="font-size:.84rem;color:var(--muted)">Rank the following recovery actions in the correct sequence by clicking them in order:</p>
    </div>

    <div id="recovery-steps">
      <?php
      $steps = [
        ['id'=>'isolate','txt'=>'Isolate all affected machines from the network','correct'=>1],
        ['id'=>'backup','txt'=>'Restore files from the most recent clean offline backup','correct'=>3],
        ['id'=>'forensics','txt'=>'Preserve evidence and conduct forensic analysis to identify the attack vector','correct'=>2],
        ['id'=>'notify','txt'=>'Notify affected stakeholders, regulators (if required by GDPR), and management','correct'=>4],
        ['id'=>'harden','txt'=>'Apply security patches, update configurations, and retrain staff before returning to production','correct'=>5],
      ];
      shuffle($steps);
      foreach($steps as $s): ?>
      <div class="rq-option" id="step-<?= $s['id'] ?>" onclick="selectStep('<?= $s['id'] ?>')" data-correct="<?= $s['correct'] ?>">
        <div class="rq-letter" id="sl-<?= $s['id'] ?>">?</div>
        <?= htmlspecialchars($s['txt']) ?>
      </div>
      <?php endforeach; ?>
    </div>
    <div id="recovery-feedback" style="display:none"></div>
    <button id="check-order-btn" style="display:none;margin-top:1rem" class="btn btn-primary" onclick="checkOrder()">Check Order</button>
  </div>

  <!-- ══ STAGE 5: RESULT ══ -->
  <div class="stage" id="stage-5">
    <div class="card" style="text-align:center;padding:2.5rem">
      <div style="font-size:3rem;margin-bottom:.5rem" id="r-emoji">🏆</div>
      <div style="font-size:2.5rem;font-weight:800;margin-bottom:.3rem" id="r-pct"></div>
      <div style="color:var(--muted);margin-bottom:.75rem" id="r-score"></div>
      <div style="display:inline-block;background:rgba(0,212,255,.1);border:1px solid rgba(0,212,255,.25);color:var(--accent);border-radius:20px;padding:4px 16px;font-size:.82rem;font-weight:700;margin-bottom:1.5rem" id="r-xp"></div>

      <div style="background:var(--surface2);border-radius:12px;padding:1.2rem;text-align:left;margin-bottom:1.5rem">
        <div style="font-weight:700;margin-bottom.75rem;font-size:.9rem">📚 Key Lessons from This Attack</div>
        <div style="font-size:.84rem;color:var(--muted);line-height:1.9;margin-top:.5rem">
          🚩 <strong style="color:var(--text)">Sender verification:</strong> The email came from techniki-invoicing.net, not a known supplier domain. Always check the sender domain character-by-character.<br>
          🚩 <strong style="color:var(--text)">Never open unexpected ZIP/EXE attachments</strong> — even from known-looking senders. Call and verify first.<br>
          🚩 <strong style="color:var(--text)">Never pay the ransom</strong> — there is no guarantee of getting files back, and it funds further attacks.<br>
          🚩 <strong style="color:var(--text)">Disconnect immediately, don't restart</strong> — restarting can trigger secondary payloads.<br>
          ✅ <strong style="color:var(--green)">Offline backups</strong> are the only reliable defence against ransomware file loss.
        </div>
      </div>

      <div style="display:flex;gap:10px;justify-content:center">
        <button onclick="location.reload()" style="background:var(--surface2);border:1px solid var(--border);color:var(--text);padding:10px 20px;border-radius:8px;cursor:pointer;font-weight:600">🔄 Replay</button>
        <a href="<?= APP_URL ?>/dashboard.php" style="background:linear-gradient(135deg,var(--accent),#0099cc);color:#000;padding:10px 20px;border-radius:8px;font-weight:600;text-decoration:none">← Dashboard</a>
      </div>
    </div>
  </div>
</div>

<div class="toast-wrap" id="toast-wrap"></div>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>
var score = 0, maxScore = 4;
var stepOrder = [], stepCount = 0;
var correctOrder = {isolate:1,forensics:2,backup:3,notify:4,harden:5};

function pip(n, state) {
  var el = document.getElementById('spip-'+n);
  if (!el) return;
  el.style.background = state==='done' ? 'var(--green)' : state==='active' ? 'var(--accent)' : 'var(--border)';
}

function goStage(from, to) {
  var s = document.getElementById('stage-'+from);
  var t = document.getElementById('stage-'+to);
  if(s) { s.style.display='none'; s.classList.remove('active'); }
  if(t) { t.style.display='block'; t.classList.add('active'); }
  pip(from, 'done'); pip(to, 'active');
  window.scrollTo(0,0);
}

// Stage 1
function s1answer(choice) {
  var opts = document.querySelectorAll('#s1-options .rq-option');
  opts.forEach(function(o){o.style.pointerEvents='none'});
  var labels = ['open','verify','delete','report'];
  var correct = 'report'; // D is best (B is also good — both give partial)
  if (choice === 'open') {
    opts[0].classList.add('wrong');
    opts[3].classList.add('reveal');
    showFeedback('s1', '❌ You opened the attachment. The invoice was a malicious macro — ransomware is now installing. The correct action is to report suspicious emails to IT without opening anything.', false);
    score += 0;
    setTimeout(function(){ goStage(1,2); setTimeout(startRansomware, 500); }, 2000);
  } else if (choice === 'verify') {
    opts[1].classList.add('correct');
    score += 1;
    showFeedback('s1', '✅ Good instinct! Calling to verify is smart. However, the best practice is also to report it to IT Security so they can investigate and block the sender for everyone.', true);
    setTimeout(function(){ skipToStage3(); }, 2200);
  } else if (choice === 'delete') {
    opts[2].classList.add('wrong');
    opts[3].classList.add('reveal');
    score += 0;
    showFeedback('s1', '⚠️ Deleting protects you, but the attacker's campaign continues. Reporting to IT Security allows them to block the sender and warn colleagues who may have received the same email.', false);
    setTimeout(function(){ skipToStage3(); }, 2200);
  } else {
    opts[3].classList.add('correct');
    score += 2;
    showFeedback('s1', '✅ Perfect! Reporting to IT Security without opening anything is the gold standard. IT can safely analyse the attachment and block the sender for the whole organisation.', true);
    setTimeout(function(){ skipToStage3(); }, 2200);
  }
}

function showFeedback(stage, msg, good) {
  var el = document.getElementById(stage+'-options');
  var fb = document.createElement('div');
  fb.style.cssText = 'margin-top:1rem;padding:.75rem 1rem;border-radius:8px;font-size:.84rem;line-height:1.6;background:'+(good?'rgba(34,197,94,.1)':'rgba(239,68,68,.08)')+';border:1px solid '+(good?'var(--green)':'var(--red)');
  fb.textContent = msg;
  el.parentNode.insertBefore(fb, el.nextSibling);
}

function skipToStage3() {
  goStage(1, 3);
  pip(2, 'done');
}

// Stage 2 — ransomware animation
function startRansomware() {
  var n1 = document.getElementById('notif1');
  var n2 = document.getElementById('notif2');
  var rs = document.getElementById('ransom-screen');
  if(n1) { n1.style.display='block'; }
  setTimeout(function(){ if(n1) n1.style.display='none'; if(n2) n2.style.display='block'; }, 2000);
  setTimeout(function(){ if(n2) n2.style.display='none'; if(rs) rs.style.display='flex'; startRansomTimer(); }, 4000);
}

var ransomSecs = 72*3600 - 18;
function startRansomTimer() {
  var t = document.getElementById('ransom-timer');
  setInterval(function(){
    ransomSecs--;
    var h = Math.floor(ransomSecs/3600);
    var m = Math.floor((ransomSecs%3600)/60);
    var s = ransomSecs%60;
    if(t) t.textContent = ('0'+h).slice(-2)+':'+('0'+m).slice(-2)+':'+('0'+s).slice(-2);
  }, 1000);
}

function s2answer(choice) {
  var opts = document.querySelectorAll('#s2-options .rq-option');
  opts.forEach(function(o){o.style.pointerEvents='none'});
  if (choice==='disconnect') {
    opts[1].classList.add('correct'); score+=2;
    showFeedback('s2','✅ Correct! Physically disconnecting stops the ransomware spreading to network shares and other machines. Calling IT immediately starts the incident response. Do NOT restart — this can trigger additional payloads.',true);
  } else if (choice==='pay') {
    opts[0].classList.add('wrong'); opts[1].classList.add('reveal');
    showFeedback('s2','❌ Never pay the ransom. Criminals rarely provide working decryption keys, and payment funds further attacks. You should disconnect from the network immediately and call IT.',false);
  } else if (choice==='restart') {
    opts[2].classList.add('wrong'); opts[1].classList.add('reveal');
    showFeedback('s2','❌ Restarting can trigger additional payloads and destroy forensic evidence. The correct action is to disconnect from the network immediately without shutting down.',false);
  } else {
    opts[3].classList.add('wrong'); opts[1].classList.add('reveal');
    showFeedback('s2','❌ Antivirus cannot decrypt already-encrypted files. Disconnect from the network immediately and call IT — every second the connection is open, the malware can spread further.',false);
  }
  setTimeout(function(){ goStage(2,3); }, 2200);
}

// Stage 3
function s3answer(choice) {
  var opts = document.querySelectorAll('#s3-options .rq-option');
  opts.forEach(function(o){o.style.pointerEvents='none'});
  if (choice==='training') {
    opts[1].classList.add('correct'); score+=1;
    showFeedback('s3','✅ Correct! Security awareness training is the single most effective control. AV, firewalls, and strong passwords are all useful layers — but if the user recognises the phishing email and never opens it, the attack never happens. Human judgement is the first and most powerful line of defence.',true);
  } else {
    var idx = {av:0,firewall:2,password:3}[choice];
    if(opts[idx]) opts[idx].classList.add('wrong');
    opts[1].classList.add('reveal');
    showFeedback('s3','⚠️ That helps, but it is not the most effective single control here. AV and firewalls can be bypassed by novel malware. Stronger passwords don\'t prevent opening attachments. Training the user to recognise phishing stops the attack before it starts.',false);
  }
  setTimeout(function(){ goStage(3,4); }, 2200);
}

// Stage 4 — recovery ordering
function selectStep(id) {
  if (stepOrder.indexOf(id) !== -1) return;
  stepOrder.push(id);
  stepCount++;
  var lbl = document.getElementById('sl-'+id);
  if(lbl) lbl.textContent = stepCount;
  var el = document.getElementById('step-'+id);
  if(el) { el.style.borderColor='var(--accent)'; el.style.background='rgba(0,212,255,.06)'; }
  if(stepCount === Object.keys(correctOrder).length) {
    document.getElementById('check-order-btn').style.display='inline-flex';
  }
}

function checkOrder() {
  var correct = 0;
  stepOrder.forEach(function(id, idx) {
    var el = document.getElementById('step-'+id);
    var lbl = document.getElementById('sl-'+id);
    if(correctOrder[id] === idx+1) {
      correct++;
      if(el) el.classList.add('correct');
    } else {
      if(el) el.classList.add('wrong');
      if(lbl) lbl.textContent = idx+1+' (should be '+correctOrder[id]+')';
    }
  });
  score += Math.round((correct/stepOrder.length)*1);
  document.getElementById('check-order-btn').style.display='none';
  setTimeout(function(){ showResult(); }, 1500);
}

function showResult() {
  goStage(4,5);
  var pct = Math.round(score/maxScore*100);
  var xp = pct>=80?400:pct>=50?250:100;
  document.getElementById('r-emoji').textContent = pct>=80?'🏆':pct>=60?'🎯':'📚';
  document.getElementById('r-pct').textContent = pct+'%';
  document.getElementById('r-pct').style.color = pct>=60?'var(--green)':'var(--red)';
  document.getElementById('r-score').textContent = score+' of '+maxScore+' points';
  document.getElementById('r-xp').textContent = '+'+xp+' XP Earned';
  fetch('<?= APP_URL ?>/api/save-score.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({game_slug:'ransomware-response',score:score,max_score:maxScore,xp_earned:xp,percentage:pct})});
}

// Init
pip(1,'active');
</script>
</body>
</html>
