<?php
$pageTitle = 'Cyber Escape Room: The Breach';
require_once dirname(__DIR__) . '/includes/header.php';
$gameRow = queryOne("SELECT * FROM games WHERE slug='escape-room'");
$gameId  = $gameRow['id'];
$userId  = $user['id'];
$already = getBestScore($userId, $gameId);
?>
<style>
.er-room{display:none;} .er-room.active{display:block;}
.er-drag-pool{display:flex;flex-direction:column;gap:6px;min-height:40px;}
.er-drop-zone{min-height:52px;background:var(--surface3);border:2px dashed var(--border);border-radius:8px;padding:6px;transition:border-color .2s;}
.er-drop-zone.over{border-color:var(--accent);background:rgba(0,212,255,.05);}
.er-drag-item{background:var(--surface2);border:1px solid var(--border);border-radius:6px;padding:10px 14px;cursor:grab;font-size:.83rem;user-select:none;display:flex;align-items:center;gap:8px;}
.er-drag-item:active{cursor:grabbing;opacity:.7;}
.flag-clickable{cursor:pointer;background:rgba(239,68,68,.12);border-bottom:2px solid rgba(239,68,68,.5);border-radius:2px;padding:0 2px;transition:all .2s;}
.flag-clickable:hover{background:rgba(239,68,68,.25);}
.flag-clickable.found{background:rgba(239,68,68,.35);border-bottom-color:var(--red);}
.terminal-line{display:block;white-space:pre;}
.hint-popup{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:var(--surface);border:1px solid var(--accent2);border-radius:14px;padding:1.5rem 2rem;max-width:440px;width:90%;z-index:200;box-shadow:0 20px 60px rgba(0,0,0,.6);}
.hint-overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:199;}
</style>

<div class="container" style="max-width:960px">
  <a href="<?= APP_URL ?>/dashboard.php" style="color:var(--muted);font-size:.85rem;display:inline-flex;align-items:center;gap:4px;margin-bottom:1rem">← Dashboard</a>
  <div class="page-title">🚪 Cyber Escape Room: The Breach</div>
  <p class="page-sub">It's Monday 9am. You arrive at work to an alert — the network was compromised over the weekend. Investigate, contain, and report the breach before time runs out.</p>

  <?php if ($already): ?>
    <div class="alert alert-success mb-2"><span class="alert-icon">✓</span>Best: <strong><?= round($already['percentage']) ?>%</strong> — <?= $already['xp_earned'] ?> XP. Beat your record.</div>
  <?php endif; ?>

  <!-- HUD -->
  <div class="escape-hud" id="escape-hud" style="display:none">
    <div>
      <div style="font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px">Time Left</div>
      <div class="hud-timer" id="hud-timer">30:00</div>
    </div>
    <div>
      <div style="font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Stage</div>
      <div class="hud-stage">
        <?php for ($i=1;$i<=5;$i++): ?><div class="stage-pip" id="pip-<?=$i?>"></div><?php endfor; ?>
      </div>
    </div>
    <div>
      <div style="font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px">Score</div>
      <div class="hud-score" id="hud-score">0 / 1000</div>
    </div>
    <div>
      <div style="font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px">Hints Used</div>
      <div style="font-weight:700;color:var(--yellow)" id="hud-hints">0</div>
    </div>
    <button class="btn btn-ghost btn-sm hint-btn" onclick="useHint()" id="hint-btn">💡 Hint (-30pts)</button>
  </div>

  <!-- INTRO -->
  <div id="er-intro" class="card card-lg">
    <div style="text-align:center;padding:1rem 0">
      <div style="font-size:3rem;margin-bottom:1rem">🚨</div>
      <div style="font-size:1.3rem;font-weight:800;margin-bottom:.5rem">SECURITY INCIDENT ALERT</div>
      <div style="color:var(--red);font-family:var(--mono);font-size:.85rem;margin-bottom:1.5rem">PRIORITY: CRITICAL | CLASSIFICATION: CONFIDENTIAL</div>
      <div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:1.2rem;text-align:left;font-size:.86rem;line-height:1.8;margin-bottom:1.5rem">
        <strong>FROM:</strong> SIEM Monitoring System<br>
        <strong>TO:</strong> Security Analyst On-Call<br>
        <strong>TIME:</strong> Saturday 03:47 UTC<br><br>
        <strong>ALERT:</strong> Anomalous network activity detected. Unauthorised external connection established to <code>192.168.1.10</code> (web server). Large data transfer observed (47GB outbound). Malicious process identified. Automatic containment failed — manual intervention required.<br><br>
        You have <strong style="color:var(--red)">30 minutes</strong> to investigate and contain the breach before the attacker completes data exfiltration.
      </div>
      <div class="grid-3 mb-2" style="gap:.8rem;text-align:left">
        <div style="background:var(--surface2);border-radius:8px;padding:.8rem;font-size:.8rem"><div style="color:var(--yellow);font-weight:600;margin-bottom:4px">Stage 1</div>The Phishing Email — identify how the breach started</div>
        <div style="background:var(--surface2);border-radius:8px;padding:.8rem;font-size:.8rem"><div style="color:var(--yellow);font-weight:600;margin-bottom:4px">Stage 2</div>The Voicemail — analyse the social engineering call</div>
        <div style="background:var(--surface2);border-radius:8px;padding:.8rem;font-size:.8rem"><div style="color:var(--yellow);font-weight:600;margin-bottom:4px">Stage 3</div>The Infected Terminal — find and stop the malware</div>
        <div style="background:var(--surface2);border-radius:8px;padding:.8rem;font-size:.8rem"><div style="color:var(--yellow);font-weight:600;margin-bottom:4px">Stage 4</div>The Password Vault — access the response protocol</div>
        <div style="background:var(--surface2);border-radius:8px;padding:.8rem;font-size:.8rem"><div style="color:var(--yellow);font-weight:600;margin-bottom:4px">Stage 5</div>Incident Report — contain and document the breach</div>
        <div style="background:var(--surface2);border-radius:8px;padding:.8rem;font-size:.8rem"><div style="color:var(--accent);font-weight:600;margin-bottom:4px">🏆 Scoring</div>Max 1000pts · Time bonuses · Hints cost 30pts each</div>
      </div>
      <button class="btn btn-primary btn-lg" onclick="startEscapeRoom()">🚪 Begin Investigation</button>
    </div>
  </div>

  <!-- ══════════ STAGE 1: THE PHISHING EMAIL ══════════ -->
  <div class="er-room" id="room-1">
    <div class="room-header">
      <div class="room-number">Stage 1 of 5</div>
      <div class="room-title">📧 The Phishing Email — Source of Breach</div>
    </div>
    <div class="room-story">
      You check your colleague Alex's inbox. Alex called in sick Monday. You find an email from Friday afternoon — sent just before the breach alert. Alex opened an attachment. <strong>Your job: identify ALL 5 red flags in this email.</strong> Click on the highlighted text to reveal each one.
    </div>
    <div class="card mb-2">
      <div style="background:var(--surface2);border-bottom:1px solid var(--border);padding:.8rem 1rem;font-size:.78rem;display:flex;gap:1.5rem;color:var(--muted)">
        <span><strong style="color:var(--text)">From:</strong> <span id="r1-from" class="flag-clickable" data-flag="0" onclick="r1flag(0)">it.support@corp-it-helpdesk.biz</span></span>
        <span><strong style="color:var(--text)">Subject:</strong> <span id="r1-sub" class="flag-clickable" data-flag="1" onclick="r1flag(1)">URGENT: Mandatory Security Update — Action Required Today</span></span>
        <span><strong style="color:var(--text)">Time:</strong> Fri 16:43</span>
      </div>
      <div style="padding:1.2rem;font-size:.86rem;line-height:1.9">
        <p>Dear Employee,</p>
        <p>Our security team has identified a <span class="flag-clickable" data-flag="2" onclick="r1flag(2)">critical vulnerability affecting all Windows workstations</span>. You MUST install the security patch <span class="flag-clickable" data-flag="3" onclick="r1flag(3)">within the next 2 hours</span> or your device will be automatically disconnected from the network.</p>
        <p>Download and run the patch from the link below. <span class="flag-clickable" data-flag="4" onclick="r1flag(4)">Do not contact IT about this — the team is overwhelmed and you will be guided through the process automatically by the tool.</span></p>
        <p style="text-align:center;margin:1rem 0">
          <a href="#" style="background:var(--red);color:white;padding:10px 20px;border-radius:6px;font-size:.85rem" onclick="return false">
            ⬇ Download: WindowsSecurityPatch_v3.exe (2.4MB)
          </a>
        </p>
        <p style="font-size:.74rem;color:var(--muted)">Patch server: <span class="flag-clickable" data-flag="0" onclick="r1flag(0)">http://corp-it-helpdesk.biz/patch/Win_Update_v3.exe</span></p>
        <p>IT Security Team</p>
      </div>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      <span style="font-size:.84rem">Red flags found: <strong id="r1-count" style="color:var(--red)">0</strong>/5</span>
      <div class="progress-wrap" style="flex:1;max-width:200px"><div class="progress-fill red" id="r1-bar" style="width:0%"></div></div>
      <button class="btn btn-primary btn-sm" onclick="completeRoom(1)" id="r1-btn" disabled>Found All Flags — Continue →</button>
    </div>
    <div class="alert alert-info mt-2" style="font-size:.78rem"><span class="alert-icon">💡</span>Click on the orange-highlighted text to reveal each red flag. Find all 5 to proceed.</div>
    <div id="r1-flags-list" style="margin-top:1rem"></div>
  </div>

  <!-- ══════════ STAGE 2: THE VOICEMAIL ══════════ -->
  <div class="er-room" id="room-2">
    <div class="room-header">
      <div class="room-number">Stage 2 of 5</div>
      <div class="room-title">📞 The Voicemail — Social Engineering Analysis</div>
    </div>
    <div class="room-story">
      Alex left a voicemail explaining they had received a suspicious call earlier on Friday. The call seems connected to the breach. Listen to the voicemail (audio will play automatically), then answer the questions.
    </div>
    <div class="card mb-2">
      <div style="background:var(--surface2);border-radius:10px;padding:1.2rem;margin-bottom:1.2rem">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:1rem">
          <div style="width:44px;height:44px;border-radius:50%;background:var(--surface3);border:2px solid var(--yellow);display:flex;align-items:center;justify-content:center;font-size:1.2rem">👤</div>
          <div>
            <div style="font-weight:600">Alex (Colleague)</div>
            <div style="font-size:.76rem;color:var(--muted)">Voicemail · Received Friday 16:05</div>
          </div>
          <div style="margin-left:auto">
            <button class="btn btn-primary btn-sm" onclick="playVoicemail()" id="vm-play-btn">▶ Play Voicemail</button>
            <span id="vm-timer" style="font-family:var(--mono);font-size:.85rem;color:var(--muted);margin-left:8px">0:00</span>
          </div>
        </div>
        <!-- Waveform animation -->
        <div style="display:flex;align-items:center;gap:2px;height:40px;padding:0 4px" id="vm-wave">
          <?php for($i=0;$i<40;$i++): $h=rand(6,32); ?>
          <div style="width:4px;background:var(--accent2);border-radius:2px;height:<?=$h?>px;opacity:.5" class="vm-bar"></div>
          <?php endfor; ?>
        </div>
        <!-- Transcript -->
        <div style="margin-top:1rem;background:rgba(0,0,0,.2);border-radius:8px;padding:.8rem 1rem;font-size:.84rem;line-height:1.7;font-style:italic;color:var(--muted)" id="vm-transcript">
          [Play the voicemail to see the transcript]
        </div>
      </div>

      <div id="r2-questions" style="display:none">
        <div style="font-weight:600;margin-bottom:1rem">📋 Answer these questions about the voicemail:</div>
        <?php
        $r2qs = [
          ['q'=>'What type of social engineering attack does Alex describe?','opts'=>['Phishing email','Vishing (voice phishing)','Tailgating','Baiting'],'ans'=>1],
          ['q'=>'Which manipulation tactic did the caller primarily use on Alex?','opts'=>['Bribery — offering money for access','Authority + Urgency — claiming to be IT and creating time pressure','Sympathy — claiming to be in trouble','Fear of missing out (FOMO)'],'ans'=>1],
          ['q'=>'What should Alex have done when the caller asked them to download a tool?','opts'=>['Downloaded it on a personal device instead','Hung up and called IT on the official internal number','Asked the caller to email the link','Downloaded it but scanned with antivirus first'],'ans'=>1],
          ['q'=>'The caller claimed to be from "Microsoft Threat Intelligence". Why is this suspicious?','opts'=>['Microsoft doesn\'t have a Threat Intelligence team','Microsoft never calls end users proactively — especially asking to install software','Microsoft only calls on Tuesdays','The team name is too long'],'ans'=>1],
        ];
        foreach ($r2qs as $qi => $q): ?>
          <div style="background:var(--surface2);border-radius:10px;padding:1rem;margin-bottom:1rem" id="r2q-<?=$qi?>">
            <div style="font-weight:600;font-size:.88rem;margin-bottom:.7rem">Q<?=$qi+1?>. <?= htmlspecialchars($q['q']) ?></div>
            <div class="option-list">
              <?php foreach ($q['opts'] as $oi => $opt): ?>
                <div class="option-item" id="r2qo-<?=$qi?>-<?=$oi?>" onclick="r2answer(<?=$qi?>,<?=$oi?>,<?=$q['ans']?>)">
                  <div class="option-letter"><?= 'ABCD'[$oi] ?></div><?= htmlspecialchars($opt) ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div id="r2-next" style="display:none">
      <button class="btn btn-primary" onclick="completeRoom(2)">Continue to Stage 3 →</button>
    </div>
  </div>

  <!-- ══════════ STAGE 3: THE INFECTED TERMINAL ══════════ -->
  <div class="er-room" id="room-3">
    <div class="room-header">
      <div class="room-number">Stage 3 of 5</div>
      <div class="room-title">💻 The Infected Terminal — Find the Malware</div>
    </div>
    <div class="room-story">
      You've gained access to Alex's workstation remotely. The terminal shows suspicious output. You must identify the malicious processes and network connections, then take the correct containment actions.
    </div>
    <div class="card mb-2">
      <div class="terminal-header">
        <div class="terminal-dot" style="background:#ef4444"></div>
        <div class="terminal-dot" style="background:#f59e0b"></div>
        <div class="terminal-dot" style="background:#10b981"></div>
        <span style="font-size:.75rem;color:var(--muted);margin-left:8px">alex@MERIDIAN-PC-04:~$</span>
      </div>
      <div class="terminal" style="min-height:280px;max-height:320px" id="r3-terminal">
        <span class="t-cyan">$ ps aux | grep -v grep</span><br>
        <span class="t-muted">USER       PID  CPU  MEM  COMMAND</span><br>
        <span class="t-white">alex      1024  0.1  0.3  /usr/bin/python3 /home/alex/.config/update-helper.py</span><br>
        <span class="t-white">alex      2048  0.0  0.1  /bin/bash</span><br>
        <span class="t-red">alex      3391 <b>47.2  8.9</b>  /tmp/.hidden/svchost32 --connect 185.220.101.55:4444 --silent</span><br>
        <span class="t-white">root      4012  0.0  0.2  /usr/sbin/sshd</span><br>
        <span class="t-red">alex      5517  <b>12.1  3.4</b>  /home/alex/Downloads/WindowsSecurityPatch_v3 --daemon --key=aG9zdG5hbWU=</span><br>
        <br>
        <span class="t-cyan">$ netstat -an | grep ESTABLISHED</span><br>
        <span class="t-white">tcp   192.168.1.5:52341   →  8.8.8.8:443       ESTABLISHED</span><br>
        <span class="t-red">tcp   192.168.1.5:58291   →  <b>185.220.101.55:4444</b>  ESTABLISHED  [svchost32]</span><br>
        <span class="t-red">tcp   192.168.1.5:58399   →  <b>185.220.101.33:8080</b>  ESTABLISHED  [WindowsSecurityPatch]</span><br>
        <span class="t-white">tcp   192.168.1.5:60012   →  172.217.16.4:443   ESTABLISHED</span><br>
        <br>
        <span class="t-yellow">$ ls /tmp/.hidden/</span><br>
        <span class="t-red">svchost32   keylogger.db   exfil_queue/   .cron_persist</span><br>
        <br>
        <span class="t-yellow">$ cat /tmp/.hidden/.cron_persist</span><br>
        <span class="t-red">*/5 * * * * /tmp/.hidden/svchost32 --reconnect 2>/dev/null</span><br>
        <br>
        <span class="t-cyan">Ready for commands...</span><br>
      </div>

      <!-- Terminal action quiz -->
      <div style="margin-top:1.2rem">
        <div style="font-weight:600;margin-bottom:.8rem;font-size:.9rem">🔐 Analyse the output above and answer:</div>
        <?php
        $r3qs = [
          ['q'=>'Which process is the active backdoor/C2 implant? (identify by PID)','opts'=>['PID 1024 — python3 update-helper.py','PID 3391 — svchost32 connecting to 185.220.101.55:4444','PID 4012 — sshd','PID 2048 — bash'],'ans'=>1],
          ['q'=>'The file WindowsSecurityPatch_v3 is running. What is it doing?','opts'=>['Installing a legitimate Windows patch','Connecting to an external IP (185.220.101.33:8080) — likely exfiltrating data','Scanning the local network','Updating antivirus signatures'],'ans'=>1],
          ['q'=>'What does the cron job entry mean?','opts'=>['Runs a backup every 5 minutes','Restarts the backdoor every 5 minutes — persistence mechanism to survive reboots','Sends an alert every 5 minutes','Schedules a Windows update'],'ans'=>1],
          ['q'=>'What is the FIRST containment action you should take?','opts'=>['Delete the /tmp/.hidden/ directory','Kill the malicious processes first','Disconnect the machine from the network immediately','Run antivirus software'],'ans'=>1],
        ];
        foreach ($r3qs as $qi => $q): ?>
          <div style="background:var(--surface2);border-radius:10px;padding:1rem;margin-bottom:.8rem">
            <div style="font-size:.86rem;font-weight:600;margin-bottom:.6rem">Q<?=$qi+1?>. <?= htmlspecialchars($q['q']) ?></div>
            <div class="option-list">
              <?php foreach ($q['opts'] as $oi => $opt): ?>
                <div class="option-item" id="r3qo-<?=$qi?>-<?=$oi?>" onclick="r3answer(<?=$qi?>,<?=$oi?>,<?=$q['ans']?>)">
                  <div class="option-letter"><?= 'ABCD'[$oi] ?></div><?= htmlspecialchars($opt) ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div id="r3-next" style="display:none">
      <button class="btn btn-primary" onclick="completeRoom(3)">Continue to Stage 4 →</button>
    </div>
  </div>

  <!-- ══════════ STAGE 4: THE PASSWORD VAULT ══════════ -->
  <div class="er-room" id="room-4">
    <div class="room-header">
      <div class="room-number">Stage 4 of 5</div>
      <div class="room-title">🔐 The Password Vault — Access Emergency Protocol</div>
    </div>
    <div class="room-story">
      The emergency incident response system requires authentication. You must create a password that meets the company's security policy. The policy document also contains a <strong>4-digit access code</strong> — read it carefully to find the code hidden within.
    </div>
    <div class="grid-2 mb-2" style="gap:1rem">
      <div class="card" style="overflow-y:auto;max-height:360px">
        <div style="font-size:.85rem;font-weight:700;margin-bottom:.8rem;color:var(--yellow)">📄 MERIDIAN SOLUTIONS — PASSWORD POLICY v2.4</div>
        <div style="font-size:.8rem;line-height:1.9;color:var(--text)">
          <p><strong>1. Minimum Length</strong><br>All passwords must be a minimum of 14 characters in length.</p>
          <p><strong>2. Complexity Requirements</strong><br>Passwords must contain at least one uppercase letter, one lowercase letter, one number, and one special character.</p>
          <p><strong>3. Emergency Access Protocol</strong><br>The emergency response system access code is updated weekly. This week's code is embedded in this document. The <strong style="color:var(--accent)">access code is: <span id="hidden-code" style="font-family:var(--mono);background:var(--surface3);padding:2px 8px;border-radius:4px">7 4 2 9</span></strong></p>
          <p><strong>4. Prohibited Patterns</strong><br>Passwords must not contain: the company name, common dictionary words, keyboard sequences (qwerty, 123456), or any date-based patterns.</p>
          <p><strong>5. Password Reuse</strong><br>The last 12 passwords cannot be reused.</p>
          <p><strong>6. MFA Requirement</strong><br>All emergency system access requires secondary authentication via the designated MFA token.</p>
        </div>
      </div>
      <div class="card">
        <div style="font-size:.85rem;font-weight:700;margin-bottom:1rem">🔓 Emergency Response System Login</div>
        <div class="form-group">
          <label class="form-label">Create Access Password</label>
          <input type="password" id="vault-pw" class="form-control" placeholder="Must meet policy requirements..." oninput="checkVaultPW()">
          <div style="margin-top:8px">
            <div class="progress-wrap" style="height:5px;margin-bottom:4px"><div id="vault-bar" class="progress-fill" style="width:0%"></div></div>
            <div id="vault-label" style="font-size:.75rem;color:var(--muted)">Enter a password</div>
          </div>
          <div style="font-size:.75rem;line-height:1.9;margin-top:8px" id="vault-criteria">
            <div><span id="vc-len">❌</span> 14+ characters</div>
            <div><span id="vc-up">❌</span> Uppercase letter</div>
            <div><span id="vc-low">❌</span> Lowercase letter</div>
            <div><span id="vc-num">❌</span> Number</div>
            <div><span id="vc-sym">❌</span> Special character</div>
            <div><span id="vc-dict">❌</span> No common words/company name</div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Enter 4-Digit Access Code (from policy document)</label>
          <input type="text" id="vault-code" class="form-control" maxlength="4" placeholder="____" style="font-family:var(--mono);letter-spacing:6px;font-size:1.2rem;text-align:center">
          <div class="form-hint">Hint: Read the policy document carefully.</div>
        </div>
        <button class="btn btn-primary" onclick="submitVault()" id="vault-submit" disabled>🔓 Unlock Emergency System</button>
        <div id="vault-result" style="margin-top:1rem"></div>
      </div>
    </div>
  </div>

  <!-- ══════════ STAGE 5: INCIDENT REPORT ══════════ -->
  <div class="er-room" id="room-5">
    <div class="room-header">
      <div class="room-number">Stage 5 of 5 — FINAL STAGE</div>
      <div class="room-title">📋 Incident Report — Contain and Document</div>
    </div>
    <div class="room-story">
      You've gathered all the evidence. Now you must demonstrate proper incident response procedure. Arrange the steps in the correct order, then answer the final assessment questions to complete the breach response and ESCAPE.
    </div>
    <div class="card mb-2">
      <div style="font-size:.9rem;font-weight:600;margin-bottom:1rem">1️⃣ Arrange the incident response steps in the correct order (drag and drop):</div>
      <div class="grid-2" style="gap:1rem">
        <div>
          <div style="font-size:.78rem;color:var(--muted);margin-bottom:6px">AVAILABLE STEPS (drag to order)</div>
          <div class="er-drag-pool" id="drag-pool"></div>
        </div>
        <div>
          <div style="font-size:.78rem;color:var(--muted);margin-bottom:6px">CORRECT ORDER (drop here)</div>
          <div id="drop-zones"></div>
        </div>
      </div>
      <button class="btn btn-ghost btn-sm mt-2" onclick="checkDragOrder()" id="check-order-btn">Check Order</button>
      <div id="drag-result" style="margin-top:.8rem"></div>
    </div>

    <div class="card mb-2" id="r5-q-card" style="display:none">
      <div style="font-size:.9rem;font-weight:600;margin-bottom:1rem">2️⃣ Final Assessment Questions:</div>
      <?php
      $r5qs = [
        ['q'=>'Under GDPR, when must a personal data breach be reported to the ICO (Information Commissioner\'s Office)?','opts'=>['Within 30 days of discovery','Within 72 hours of becoming aware of the breach','Within 7 working days','Immediately, within 1 hour'],'ans'=>1],
        ['q'=>'After an attacker has accessed your system, restoring from backup should happen:','opts'=>['Immediately — restore first, investigate later','Only after forensic image has been taken and evidence preserved','Never — the system must be rebuilt from scratch','After paying the ransom if applicable'],'ans'=>1],
        ['q'=>'Which of these is a valid Indicator of Compromise (IoC) from the investigation?','opts'=>['The cron job entry /tmp/.hidden/svchost32','Alex calling in sick on Monday','The web server IP 192.168.1.10','The policy document code 7429'],'ans'=>0],
        ['q'=>'Who should be notified internally FIRST when a serious data breach is detected?','opts'=>['Marketing and PR to prepare a statement','Finance to assess the cost','The Information Security Officer / CISO, then management','External media to maintain transparency'],'ans'=>2],
      ];
      foreach ($r5qs as $qi => $q): ?>
        <div style="background:var(--surface2);border-radius:10px;padding:1rem;margin-bottom:.8rem">
          <div style="font-size:.86rem;font-weight:600;margin-bottom:.6rem">Q<?=$qi+1?>. <?= htmlspecialchars($q['q']) ?></div>
          <div class="option-list">
            <?php foreach ($q['opts'] as $oi => $opt): ?>
              <div class="option-item" id="r5qo-<?=$qi?>-<?=$oi?>" onclick="r5answer(<?=$qi?>,<?=$oi?>,<?=$q['ans']?>)">
                <div class="option-letter"><?= 'ABCD'[$oi] ?></div><?= htmlspecialchars($opt) ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <button class="btn btn-primary mt-2" id="r5-submit" onclick="finishEscapeRoom()" disabled>🚪 Submit Report & ESCAPE!</button>
    </div>
  </div>

  <!-- ESCAPE SUCCESS -->
  <div id="escape-complete" style="display:none">
    <div class="card card-lg text-center">
      <div style="font-size:4rem;margin-bottom:1rem">🏆</div>
      <div style="font-size:1.5rem;font-weight:800;color:var(--green);margin-bottom:.5rem">BREACH CONTAINED — YOU ESCAPED!</div>
      <div class="result-score" id="er-final-score" style="font-size:3rem;margin:1rem 0"></div>
      <div id="er-score-breakdown" style="font-size:.85rem;color:var(--muted);margin-bottom:1rem"></div>
      <div class="result-xp" id="er-xp"></div>
      <div class="result-msg" id="er-msg" style="margin-top:1rem;max-width:500px;margin-left:auto;margin-right:auto"></div>
      <div style="margin-top:1.5rem;display:flex;gap:1rem;justify-content:center">
        <button class="btn btn-ghost" onclick="location.reload()">🔄 Play Again</button>
        <a href="<?= APP_URL ?>/dashboard.php" class="btn btn-primary">Dashboard →</a>
      </div>
    </div>
  </div>
</div>

<div id="hint-overlay" class="hint-overlay" style="display:none" onclick="closeHint()"></div>
<div class="hint-popup" id="hint-popup" style="display:none">
  <div style="font-size:1.1rem;font-weight:700;margin-bottom:.8rem">💡 Hint</div>
  <div id="hint-text" style="font-size:.88rem;line-height:1.7;color:var(--text)"></div>
  <button class="btn btn-ghost btn-sm mt-2" onclick="closeHint()">Close</button>
</div>

<div class="toast-wrap" id="toast-wrap"></div>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>
// ═══ STATE ═══
let timerInterval = null;
let secondsLeft = 1800;
let currentRoom = 0;
let roomScores = {1:0,2:0,3:0,4:0,5:0};
let hintsUsed = 0;
let r1Found = new Set();
let r2Answers = {};
let r3Answers = {};
let r5Answers = {};
let dragOrderCorrect = false;
let r2Correct = 0, r3Correct = 0, r5Correct = 0;

const hints = {
  1: 'Look at the sender\'s email domain carefully. Check the download URL\'s domain. Look for all-caps language. Notice the extremely short deadline. Notice they tell you NOT to contact IT.',
  2: 'The caller claims to be from "Microsoft Threat Intelligence." Microsoft never calls end users unsolicited. The caller created urgency and asked for software to be installed. This is vishing + pretexting.',
  3: 'The process at PID 3391 has an unusually high CPU and is connecting to an external IP on port 4444 — a common C2 (command and control) port. The cron entry shows persistence.',
  4: 'Read the policy document carefully — the access code is explicitly stated in section 3. Your password needs 14+ characters, uppercase, lowercase, number, and a special character.',
  5: 'Correct IR order: 1.Identify, 2.Contain/Isolate, 3.Eradicate, 4.Recover, 5.Preserve Evidence, 6.Report. GDPR requires breach notification to ICO within 72 hours.'
};

// ═══ TIMER ═══
function updateTimer() {
  secondsLeft--;
  if (secondsLeft <= 0) { clearInterval(timerInterval); timeUp(); return; }
  const m = Math.floor(secondsLeft/60);
  const s = secondsLeft % 60;
  const display = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
  document.getElementById('hud-timer').textContent = display;
  if (secondsLeft <= 300) document.getElementById('hud-timer').classList.add('warning');
}

function timeUp() {
  showToast('⏱ Time is up! Submitting your current progress...', 'error');
  finishEscapeRoom(true);
}

// ═══ START ═══
function startEscapeRoom() {
  document.getElementById('er-intro').style.display = 'none';
  document.getElementById('escape-hud').style.display = 'flex';
  timerInterval = setInterval(updateTimer, 1000);
  goToRoom(1);
}

function goToRoom(n) {
  document.querySelectorAll('.er-room').forEach(r => r.classList.remove('active'));
  document.querySelectorAll('.stage-pip').forEach((p,i) => {
    p.className = 'stage-pip ' + (i+1<n ? 'done' : i+1===n ? 'active' : '');
  });
  document.getElementById('room-'+n).classList.add('active');
  currentRoom = n;
  window.scrollTo(0,80);
  if (n === 2) initVoicemail();
  if (n === 5) initDragDrop();
}

function updateHUD() {
  const total = Object.values(roomScores).reduce((a,b)=>a+b,0);
  document.getElementById('hud-score').textContent = `${total} / 1000`;
  document.getElementById('hud-hints').textContent = hintsUsed;
}

// ═══ HINTS ═══
function useHint() {
  hintsUsed++;
  const penalty = hintsUsed * 30;
  roomScores[currentRoom] = Math.max(0, (roomScores[currentRoom] || 0) - 30);
  document.getElementById('hint-text').innerHTML = hints[currentRoom] || 'No hint available for this stage.';
  document.getElementById('hint-overlay').style.display = 'block';
  document.getElementById('hint-popup').style.display = 'block';
  updateHUD();
  showToast('💡 Hint used — 30 points deducted', 'error');
}
function closeHint() {
  document.getElementById('hint-overlay').style.display = 'none';
  document.getElementById('hint-popup').style.display = 'none';
}

// ═══ ROOM 1 ═══
const r1Flags = [
  {label:'Suspicious sender domain', detail:'corp-it-helpdesk.biz is NOT your company\'s IT domain. Real IT uses your company\'s own email domain.'},
  {label:'All-caps urgency subject line', detail:'"URGENT" and "Action Required" in all caps — manufactured panic to stop rational thinking.'},
  {label:'Vague threat about vulnerability', detail:'"Critical vulnerability" without specifying which CVE or patch KB — legitimate IT is specific.'},
  {label:'Extreme 2-hour deadline', detail:'Real IT departments give reasonable notice for patches. 2 hours is designed to bypass judgment.'},
  {label:'Told NOT to contact IT', detail:'Attackers disable your verification path. This is the most important red flag — legitimate IT never tells you this.'},
];

function r1flag(idx) {
  if (r1Found.has(idx)) return;
  r1Found.add(idx);
  document.querySelectorAll(`[data-flag="${idx}"]`).forEach(el => el.classList.add('found'));
  const count = r1Found.size;
  document.getElementById('r1-count').textContent = count;
  document.getElementById('r1-bar').style.width = (count/5*100)+'%';

  const list = document.getElementById('r1-flags-list');
  const div = document.createElement('div');
  div.style.cssText = 'display:flex;gap:8px;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:8px;padding:8px 12px;margin-bottom:6px;font-size:.8rem;animation:toastIn .3s ease';
  div.innerHTML = `<span style="color:var(--red);font-weight:700">#${count}</span><div><strong style="color:var(--red)">${r1Flags[idx].label}</strong> — ${r1Flags[idx].detail}</div>`;
  list.appendChild(div);
  showToast(`🚩 Red flag #${count} found!`, 'error');
  roomScores[1] = count * 40; // 40 pts per flag, max 200
  updateHUD();
  if (count >= 5) { document.getElementById('r1-btn').disabled = false; }
}

function completeRoom(n) {
  if (n === 1) roomScores[1] = r1Found.size * 40;
  goToRoom(n+1);
}

// ═══ ROOM 2: VOICEMAIL ═══
let vmPlaying = false;
let vmTimer = null;
let vmSecs = 0;
let r2Count = 0;

const voicemailScript = [
  "Hey, it's Alex. Sorry I'm not coming in Monday — I've been feeling off all weekend and I think I know why.",
  "On Friday afternoon I got a call from someone claiming to be from Microsoft Threat Intelligence.",
  "They said they'd detected malware on my workstation and needed remote access to clean it up within the hour or it would spread to the whole network.",
  "The guy sounded really professional and urgent, so I... I downloaded a tool they sent me a link to. WindowsSecurityPatch underscore v3.",
  "It ran for a few minutes and then everything seemed fine. But then I started getting weird pop-ups and my computer was really slow.",
  "I should have just hung up and called the IT helpdesk. I know that now. I'm so sorry.",
  "Please let me know what's happening — I'm worried I've caused a serious problem. Call me on my mobile."
];

function playVoicemail() {
  if (vmPlaying) return;
  vmPlaying = true;
  document.getElementById('vm-play-btn').textContent = '▶ Playing...';
  document.getElementById('vm-play-btn').disabled = true;
  document.getElementById('vm-transcript').innerHTML = '';
  animateVMBars();
  vmSecs = 0;
  vmTimer = setInterval(() => {
    vmSecs++;
    document.getElementById('vm-timer').textContent = `0:${String(vmSecs).padStart(2,'0')}`;
  }, 1000);
  playVMLine(0);
}

function animateVMBars() {
  document.querySelectorAll('.vm-bar').forEach(b => {
    b.style.height = (Math.random()*28+6)+'px';
    b.style.opacity = '1';
  });
}

function playVMLine(idx) {
  if (idx >= voicemailScript.length) {
    clearInterval(vmTimer);
    vmPlaying = false;
    document.getElementById('vm-play-btn').textContent = '▶ Replay';
    document.getElementById('vm-play-btn').disabled = false;
    document.querySelectorAll('.vm-bar').forEach(b => { b.style.height='8px'; b.style.opacity='.3'; });
    document.getElementById('r2-questions').style.display = 'block';
    return;
  }
  const text = voicemailScript[idx];
  const p = document.createElement('p');
  p.style.cssText = 'font-size:.84rem;line-height:1.7;margin-bottom:.5rem;color:var(--text);font-style:normal';
  p.textContent = text;
  document.getElementById('vm-transcript').appendChild(p);
  document.getElementById('vm-transcript').scrollTop = 9999;

  if (window.speechSynthesis) {
    const utt = new SpeechSynthesisUtterance(text);
    utt.rate = 0.9; utt.pitch = 0.95;
    const voices = window.speechSynthesis.getVoices();
    const v = voices.find(v => v.lang==='en-GB') || voices.find(v => v.lang.startsWith('en-'));
    if (v) { utt.voice = v; }
    setInterval(animateVMBars, 300);
    utt.onend = () => setTimeout(() => playVMLine(idx+1), 400);
    utt.onerror = () => setTimeout(() => playVMLine(idx+1), 1500);
    window.speechSynthesis.speak(utt);
  } else {
    setTimeout(() => playVMLine(idx+1), 2000);
  }
}

function r2answer(qi, oi, correct) {
  if (r2Answers[qi] !== undefined) return;
  r2Answers[qi] = oi;
  document.querySelectorAll(`[id^="r2qo-${qi}-"]`).forEach(el => el.style.pointerEvents='none');
  if (oi === correct) {
    document.getElementById(`r2qo-${qi}-${oi}`).classList.add('correct');
    r2Correct++; roomScores[2] = r2Correct * 37;
  } else {
    document.getElementById(`r2qo-${qi}-${oi}`).classList.add('wrong');
    document.getElementById(`r2qo-${qi}-${correct}`).classList.add('correct');
  }
  updateHUD();
  if (Object.keys(r2Answers).length === 4) {
    document.getElementById('r2-next').style.display = 'block';
  }
}

function initVoicemail() {
  if (window.speechSynthesis) window.speechSynthesis.getVoices();
}

// ═══ ROOM 3 ═══
function r3answer(qi, oi, correct) {
  if (r3Answers[qi] !== undefined) return;
  r3Answers[qi] = oi;
  document.querySelectorAll(`[id^="r3qo-${qi}-"]`).forEach(el => el.style.pointerEvents='none');
  if (oi === correct) {
    document.getElementById(`r3qo-${qi}-${oi}`).classList.add('correct');
    r3Correct++; roomScores[3] = r3Correct * 37;
  } else {
    document.getElementById(`r3qo-${qi}-${oi}`).classList.add('wrong');
    document.getElementById(`r3qo-${qi}-${correct}`).classList.add('correct');
  }
  updateHUD();
  if (Object.keys(r3Answers).length === 4) {
    document.getElementById('r3-next').style.display = 'block';
  }
}

// ═══ ROOM 4: PASSWORD VAULT ═══
function checkVaultPW() {
  const pw = document.getElementById('vault-pw').value;
  const c = {
    len: pw.length >= 14,
    up: /[A-Z]/.test(pw),
    low: /[a-z]/.test(pw),
    num: /[0-9]/.test(pw),
    sym: /[^a-zA-Z0-9]/.test(pw),
    dict: pw.length > 0 && !/meridian|password|admin|company|qwerty|123/i.test(pw)
  };
  const score = Object.values(c).filter(Boolean).length;
  const bar = document.getElementById('vault-bar');
  const lbl = document.getElementById('vault-label');
  bar.style.width = (score/6*100)+'%';
  if (score<3){bar.style.background='var(--red)';lbl.textContent='Too weak';lbl.style.color='var(--red)';}
  else if(score<5){bar.style.background='var(--yellow)';lbl.textContent='Getting stronger';lbl.style.color='var(--yellow)';}
  else if(score<6){bar.style.background='var(--green)';lbl.textContent='Strong';lbl.style.color='var(--green)';}
  else{bar.style.background='var(--accent)';lbl.textContent='Excellent — meets policy!';lbl.style.color='var(--accent)';}
  Object.entries(c).forEach(([k,v]) => {
    const el = document.getElementById('vc-'+k);
    if(el) el.textContent = v ? '✅' : '❌';
  });
  document.getElementById('vault-submit').disabled = score < 6;
}

function submitVault() {
  const code = document.getElementById('vault-code').value.replace(/\s/g,'');
  if (code === '7429') {
    roomScores[4] = 150;
    updateHUD();
    document.getElementById('vault-result').innerHTML = `<div class="alert alert-success"><span class="alert-icon">✅</span>Access granted! Emergency response system unlocked.</div>`;
    setTimeout(() => completeRoom(4), 1500);
  } else if (code === '') {
    document.getElementById('vault-result').innerHTML = `<div class="alert alert-warn"><span class="alert-icon">⚠</span>Enter the 4-digit code from the policy document.</div>`;
  } else {
    document.getElementById('vault-result').innerHTML = `<div class="alert alert-danger"><span class="alert-icon">❌</span>Incorrect code. Read the policy document carefully — section 3.</div>`;
    roomScores[4] = 50; // partial credit for correct password
    updateHUD();
  }
}

// ═══ ROOM 5: DRAG AND DROP ═══
const irSteps = [
  {id:0, text:'🔍 Identify — detect and confirm the security incident', correct:1},
  {id:1, text:'🔌 Contain — isolate affected systems from the network', correct:2},
  {id:2, text:'🧹 Eradicate — remove malware, close vulnerabilities, rotate credentials', correct:3},
  {id:3, text:'💾 Preserve Evidence — forensic image before remediation', correct:2.5}, // same level as contain
  {id:4, text:'📈 Recover — restore systems from clean backups, verify integrity', correct:4},
  {id:5, text:'📋 Report — notify stakeholders, regulatory bodies (GDPR 72h)', correct:5},
  {id:6, text:'📚 Lessons Learned — post-incident review and improvement plan', correct:6},
];
const correctOrder = [0,1,3,2,4,5,6]; // Identify, Contain, Preserve, Eradicate, Recover, Report, Lessons

let dragOrder = [];

function initDragDrop() {
  const shuffled = [...irSteps].sort(() => Math.random()-0.5);
  const pool = document.getElementById('drag-pool');
  pool.innerHTML = shuffled.map(s =>
    `<div class="er-drag-item" draggable="true" data-id="${s.id}" id="drag-${s.id}">${s.text}</div>`
  ).join('');

  const zones = document.getElementById('drop-zones');
  zones.innerHTML = Array(7).fill(0).map((_,i) =>
    `<div class="er-drop-zone" id="dz-${i}" data-pos="${i}">
      <span style="font-size:.72rem;color:var(--muted)">Step ${i+1}</span>
    </div>`
  ).join('');

  document.querySelectorAll('.er-drag-item').forEach(el => {
    el.addEventListener('dragstart', e => { e.dataTransfer.setData('id', el.dataset.id); });
  });
  document.querySelectorAll('.er-drop-zone').forEach(zone => {
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('over'));
    zone.addEventListener('drop', e => {
      e.preventDefault();
      zone.classList.remove('over');
      const id = parseInt(e.dataTransfer.getData('id'));
      const existing = zone.querySelector('.er-drag-item');
      if (existing) { document.getElementById('drag-pool').appendChild(existing); }
      const item = document.getElementById('drag-'+id);
      zone.appendChild(item);
    });
  });
}

function checkDragOrder() {
  const placed = [];
  document.querySelectorAll('.er-drop-zone').forEach((zone,i) => {
    const item = zone.querySelector('.er-drag-item');
    placed.push(item ? parseInt(item.dataset.id) : -1);
  });
  let correct = 0;
  placed.forEach((id,i) => { if (id === correctOrder[i]) correct++; });
  const pct = Math.round(correct/7*100);
  roomScores[5] = Math.round(pct/100 * 100);
  dragOrderCorrect = pct >= 70;
  document.getElementById('drag-result').innerHTML = `
    <div class="alert ${pct>=70?'alert-success':'alert-warn'}">
      <span class="alert-icon">${pct>=70?'✅':'⚠'}</span>
      ${correct}/7 steps in correct order (${pct}%). ${pct>=70
        ? 'Good incident response sequence!'
        : `Correct order: Identify → Contain → Preserve Evidence → Eradicate → Recover → Report → Lessons Learned`}
    </div>`;
  if (pct >= 50) {
    document.getElementById('r5-q-card').style.display = 'block';
  }
  updateHUD();
}

let r5QAnswered = 0;
function r5answer(qi, oi, correct) {
  if (r5Answers[qi] !== undefined) return;
  r5Answers[qi] = oi;
  document.querySelectorAll(`[id^="r5qo-${qi}-"]`).forEach(el => el.style.pointerEvents='none');
  if (oi === correct) {
    document.getElementById(`r5qo-${qi}-${oi}`).classList.add('correct');
    r5Correct++; r5QAnswered++;
    roomScores[5] = Math.min(200, (roomScores[5]||0) + 25);
  } else {
    document.getElementById(`r5qo-${qi}-${oi}`).classList.add('wrong');
    document.getElementById(`r5qo-${qi}-${correct}`).classList.add('correct');
    r5QAnswered++;
  }
  updateHUD();
  if (r5QAnswered === 4) { document.getElementById('r5-submit').disabled = false; }
}

// ═══ FINISH ═══
function finishEscapeRoom(timedOut = false) {
  clearInterval(timerInterval);
  const elapsed = 1800 - secondsLeft;
  const timeBonus = timedOut ? 0 : Math.max(0, Math.round((1800 - elapsed) / 1800 * 200));
  const hintPenalty = hintsUsed * 30;
  const baseScore = Object.values(roomScores).reduce((a,b)=>a+b,0);
  const finalScore = Math.max(0, Math.min(1000, baseScore + timeBonus - hintPenalty));
  const pct = Math.round(finalScore/1000*100);
  const xp = Math.round(pct/100*500);

  document.querySelectorAll('.er-room').forEach(r=>r.classList.remove('active'));
  document.getElementById('escape-hud').style.display='none';
  document.getElementById('escape-complete').style.display='block';
  document.getElementById('er-final-score').textContent = finalScore + ' / 1000';
  document.getElementById('er-final-score').style.color = pct>=80?'var(--green)':pct>=60?'var(--yellow)':'var(--red)';
  document.getElementById('er-score-breakdown').innerHTML = `Base: ${baseScore} pts · Time bonus: +${timeBonus} pts · Hints: -${hintPenalty} pts`;
  document.getElementById('er-xp').textContent = `+${xp} XP Earned`;
  document.getElementById('er-msg').textContent = pct>=80
    ? '🛡 Outstanding! You contained a sophisticated breach, preserved evidence, and followed proper incident response procedure. You are a true defender.'
    : pct>=60
    ? '⚠️ Good effort — you contained the breach but some gaps remain. Review the PICERL incident response framework.'
    : '❌ The attacker completed their mission this time. Study the NIST Cybersecurity Framework and try again.';

  document.getElementById('escape-complete').scrollIntoView({behavior:'smooth'});
  fetch('<?= APP_URL ?>/api/save-score.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ game_slug:'escape-room', score:finalScore, max_score:1000, xp_earned:xp, percentage:pct, hints_used:hintsUsed, time_taken:elapsed })
  }).then(r=>r.json()).then(d => {
    if(d.badge) showToast('🏅 Badge: '+d.badge,'success');
  });
}
</script>
</body>
</html>
