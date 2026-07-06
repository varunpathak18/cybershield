<?php
$pageTitle = 'Phishing Email Detective';
require_once dirname(__DIR__) . '/includes/header.php';
?>
<style>
.sim-chrome{background:#1a1a2e;border-radius:10px;overflow:hidden;border:1px solid #333;box-shadow:0 20px 60px rgba(0,0,0,.6);margin-bottom:1.5rem}
.sim-titlebar{background:#2d2d2d;padding:8px 12px;display:flex;align-items:center;gap:8px}
.sim-dot{width:12px;height:12px;border-radius:50%}
.sim-dot.red{background:#ff5f57}.sim-dot.yellow{background:#febc2e}.sim-dot.green{background:#28c840}
.sim-url{flex:1;background:#3a3a3a;border-radius:6px;padding:4px 12px;font-size:.75rem;color:#aaa;margin:0 12px;font-family:monospace}
.gmail-wrap{display:grid;grid-template-columns:190px 1fr;height:580px;background:#fff;color:#202124}
.gmail-sidebar{background:#f6f8fc;border-right:1px solid #e0e0e0;padding:8px 0}
.gmail-compose-btn{background:#c2e7ff;border:none;border-radius:20px;padding:10px 16px;font-size:.82rem;font-weight:600;cursor:default;display:flex;align-items:center;gap:8px;color:#001d35;width:calc(100% - 20px);margin:8px 10px 16px}
.gmail-nav-item{padding:6px 14px 6px 20px;border-radius:0 20px 20px 0;display:flex;align-items:center;gap:10px;color:#202124;font-size:.8rem}
.gmail-nav-item.active{background:#d3e3fd;font-weight:700}
.gmail-nav-badge{background:#c5221f;color:#fff;border-radius:10px;padding:1px 6px;font-size:.68rem;font-weight:700;margin-left:auto}
.gmail-list-wrap{border-right:1px solid #e0e0e0;overflow-y:auto;background:#fff}
.gmail-list-item{display:grid;grid-template-columns:30px 1fr auto;gap:6px;align-items:center;padding:8px 10px;border-bottom:1px solid #f1f3f4;cursor:pointer;transition:background .15s}
.gmail-list-item:hover{background:#f2f6fc}
.gmail-list-item.selected{background:#e8f0fe}
.gl-avatar{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;color:#fff;flex-shrink:0}
.gl-from{font-size:.78rem;color:#202124;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.gl-subject{font-size:.74rem;color:#444;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.gl-date{font-size:.7rem;color:#888;white-space:nowrap;align-self:start;padding-top:2px}
.gl-middle{min-width:0}
.gmail-pane{display:flex;flex-direction:column;height:580px;overflow-y:auto;background:#fff}
.gmail-pane-empty{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#888;font-size:.9rem;gap:8px}
.gmail-pane-header{padding:16px 20px 12px;border-bottom:1px solid #e0e0e0;flex-shrink:0}
.gmail-subject-line{font-size:1.1rem;font-weight:400;color:#202124;margin-bottom:10px}
.gmail-meta{display:flex;align-items:flex-start;gap:10px}
.gmail-avatar-lg{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;color:#fff;flex-shrink:0}
.gmail-from-name{font-size:.86rem;font-weight:600;color:#202124}
.gmail-from-addr{font-size:.73rem;color:#888;margin-top:1px}
.gmail-timestamp{font-size:.73rem;color:#888;margin-left:auto;white-space:nowrap}
.gmail-body{padding:16px 20px;font-size:.86rem;line-height:1.7;color:#202124;flex:1}
.gmail-body p{margin-bottom:10px}
.gmail-body ul{padding-left:1.4rem;margin:8px 0}
.rf{background:rgba(211,47,47,.09);border-bottom:2px solid #d32f2f;color:#b71c1c;cursor:help;position:relative;display:inline}
.rf-tip{display:none;position:absolute;bottom:calc(100% + 6px);left:0;background:#fff;border:1px solid #d32f2f;border-radius:8px;padding:10px 12px;font-size:.74rem;color:#202124;width:250px;z-index:50;box-shadow:0 4px 16px rgba(0,0,0,.25);line-height:1.5;white-space:normal;font-weight:400}
.rf-tip::before{content:'🚩 Red Flag: ';font-weight:700;color:#d32f2f;display:block;margin-bottom:4px}
.rf:hover .rf-tip{display:block}
.gmail-actions{padding:10px 20px;border-top:2px solid #e0e0e0;background:#fafafa;display:flex;gap:10px;align-items:center;flex-shrink:0;flex-wrap:wrap}
.btn-phish{background:#d32f2f;color:#fff;border:none;padding:9px 16px;border-radius:6px;font-size:.82rem;font-weight:600;cursor:pointer}
.btn-phish:hover{background:#b71c1c}
.btn-safe{background:#1e8e3e;color:#fff;border:none;padding:9px 16px;border-radius:6px;font-size:.82rem;font-weight:600;cursor:pointer}
.btn-safe:hover{background:#155724}
.verdict-correct{background:#e6f4ea;color:#137333;border:1px solid #137333;padding:5px 12px;border-radius:20px;font-size:.76rem;font-weight:700}
.verdict-wrong{background:#fce8e6;color:#c5221f;border:1px solid #c5221f;padding:5px 12px;border-radius:20px;font-size:.76rem;font-weight:700}
.score-panel{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:.9rem 1.4rem;display:flex;align-items:center;gap:2rem;margin-bottom:1rem}
.sp-val{font-size:1.5rem;font-weight:800;line-height:1}
.sp-lbl{font-size:.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-top:2px}
.result-overlay{position:fixed;inset:0;background:rgba(0,0,0,.8);display:flex;align-items:center;justify-content:center;z-index:1000}
.result-card{background:var(--surface);border-radius:18px;padding:2.5rem;max-width:520px;width:90%;text-align:center;border:1px solid var(--border)}
</style>
<div class="container">
  <a href="<?= APP_URL ?>/dashboard.php" style="color:var(--muted);font-size:.85rem;display:inline-flex;align-items:center;gap:4px;margin-bottom:1rem">&#8592; Back to Dashboard</a>
  <div class="page-title">&#x1F3A3; Phishing Email Detective</div>
  <p class="page-sub">You have inherited a suspicious inbox. Open each email, look for red flags (highlighted in red), then decide: <strong>Phishing or Legitimate?</strong></p>
  <div class="score-panel">
    <div><div class="sp-val" style="color:var(--green)" id="sp-correct">0</div><div class="sp-lbl">Correct</div></div>
    <div><div class="sp-val" style="color:var(--red)" id="sp-wrong">0</div><div class="sp-lbl">Wrong</div></div>
    <div><div class="sp-val" id="sp-remain">7</div><div class="sp-lbl">Remaining</div></div>
    <div style="flex:1"></div>
    <div style="font-size:.78rem;color:var(--muted)">&#x1F4A1; Hover red highlighted text for clues</div>
  </div>
  <div class="sim-chrome">
    <div class="sim-titlebar">
      <div class="sim-dot red"></div><div class="sim-dot yellow"></div><div class="sim-dot green"></div>
      <div class="sim-url">&#x1F512; mail.google.com/mail/u/0/#inbox</div>
    </div>
    <div class="gmail-wrap">
      <div class="gmail-sidebar">
        <button class="gmail-compose-btn">&#x270F;&#xFE0F; Compose</button>
        <div class="gmail-nav-item active">&#x1F4E5; Inbox <span class="gmail-nav-badge">7</span></div>
        <div class="gmail-nav-item">&#x2B50; Starred</div>
        <div class="gmail-nav-item">&#x1F4E4; Sent</div>
        <div class="gmail-nav-item">&#x1F5D1; Bin</div>
      </div>
      <div style="display:grid;grid-template-columns:280px 1fr;height:580px;overflow:hidden">
        <div class="gmail-list-wrap">
          <div style="padding:6px 10px;font-size:.7rem;color:#888;border-bottom:1px solid #e0e0e0;background:#fff">Primary &middot; 7 conversations</div>
          <div id="email-list"></div>
        </div>
        <div class="gmail-pane" id="email-pane">
          <div class="gmail-pane-empty"><span style="font-size:2rem">&#x1F4E7;</span><span>Select an email to read it</span></div>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="result-overlay" style="display:none" class="result-overlay"></div>
<div class="toast-wrap" id="toast-wrap"></div>
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<script>
var emails=[
{id:0,phishing:true,from:'PayPal Security',addr:'security@paypa1-alerts.com',to:'you@company.com',date:'Today, 09:14',subject:'⚠️ Urgent: Your PayPal account has been limited',color:'#e91e63',body:'<p>Dear <span class="rf">Valued Customer<span class="rf-tip">Legitimate companies address you by name. Generic greetings mean this was mass-sent to thousands of people.</span></span>,</p><p>We detected unusual activity. Verify your account within <span class="rf">24 hours<span class="rf-tip">Artificial urgency is a classic manipulation tactic designed to stop you thinking clearly before acting.</span></span> or your account will be permanently closed.</p><p>Click to restore access:<br><span class="rf"><a href="#" onclick="return false">http://paypal-secure-verify.account-login.com/restore</a><span class="rf-tip">The real domain is account-login.com, NOT paypal.com. Attackers put brand names before the real domain to fool you. Always look at the part immediately before .com</span></span></p><p>Regards,<br><span class="rf">PayPal Security Team<span class="rf-tip">Sender address is paypa1-alerts.com. The letter L is replaced with the number 1. Examine every character in email addresses carefully.</span></span></p>'},
{id:1,phishing:false,from:'Sarah Mitchell',addr:'s.mitchell@yourcompany.com',to:'you@company.com',date:'Today, 08:52',subject:'Q3 Team Meeting — Agenda attached',color:'#4caf50',body:'<p>Hi,</p><p>Hope you are well. Here is the agenda for Thursday Q3 review. Meeting room booked 10am Conference Room B.</p><ul><li>Q3 performance review (30 mins)</li><li>Budget planning Q4 (20 mins)</li><li>Team restructure update (10 mins)</li></ul><p>Please come prepared with your team numbers.</p><p>Best,<br>Sarah Mitchell<br>Head of Operations</p>'},
{id:2,phishing:true,from:'IT Support Desk',addr:'itsupport@micros0ft-helpdesk.net',to:'you@company.com',date:'Yesterday, 16:33',subject:'ACTION REQUIRED: Critical Windows Security Patch',color:'#2196f3',body:'<p>Dear <span class="rf">Employee<span class="rf-tip">Your real IT department addresses you by name. Generic "Employee" means this was mass-sent.</span></span>,</p><p>Your workstation is missing a critical patch. <span class="rf">Failure to install within 2 hours will quarantine your machine from the network<span class="rf-tip">Threatening immediate network disconnection is a pressure tactic. Real IT teams push patches automatically and never ask you to download manually.</span></span>.</p><p>Download:<br><span class="rf"><a href="#" onclick="return false">https://micros0ft-helpdesk.net/patch/security-fix.exe</a><span class="rf-tip">Domain is micros0ft with zero instead of O. Also never download .exe files from email links. This is malware disguised as a patch.</span></span></p><p><span class="rf">IT Support Desk<span class="rf-tip">Sender is micros0ft-helpdesk.net, nothing to do with Microsoft (microsoft.com). Always call your IT team directly to verify.</span></span></p>'},
{id:3,phishing:false,from:'LinkedIn',addr:'messages-noreply@linkedin.com',to:'you@company.com',date:'Yesterday, 11:20',subject:'James Patel sent you a connection request',color:'#0077b5',body:'<p>Hi,</p><p><strong>James Patel</strong>, Senior Product Manager at Accenture, wants to connect with you on LinkedIn.</p><blockquote style="border-left:3px solid #0077b5;padding-left:12px;margin:12px 0;color:#555;font-style:italic">Hi, I came across your profile and would love to connect. I am working on interesting projects in your space.</blockquote><p><a href="#" onclick="return false" style="background:#0077b5;color:#fff;padding:10px 20px;border-radius:4px;display:inline-block;margin:8px 0;text-decoration:none">Accept</a></p><p style="font-size:.75rem;color:#888">Sent because James Patel sent you a connection invitation. Sender domain: linkedin.com</p>'},
{id:4,phishing:true,from:'HMRC Gov UK',addr:'noreply@hmrc-taxrefund.co',to:'you@company.com',date:'Mon, 09:05',subject:'Tax Refund Notification — £842.50 awaiting you',color:'#009688',body:'<p>Dear Taxpayer,</p><p>You are eligible for a <span class="rf">tax refund of £842.50<span class="rf-tip">HMRC never initiates refunds by email. Legitimate refunds are processed automatically and notified by post, not via email links.</span></span>.</p><p>Submit your <span class="rf">personal and bank details here<span class="rf-tip">HMRC will NEVER ask for bank details via an email link. This is credential harvesting designed to steal your banking information.</span></span>:<br><span class="rf"><a href="#" onclick="return false">https://hmrc-taxrefund.co/claim</a><span class="rf-tip">Real HMRC domains are gov.uk (e.g. tax.service.gov.uk). The domain hmrc-taxrefund.co is fake, registered by attackers to impersonate HMRC.</span></span></p><p>This offer <span class="rf">expires in 48 hours<span class="rf-tip">Government agencies do not put expiry deadlines on tax refunds. This manufactured urgency is a pressure tactic.</span></span>.</p><p><span class="rf">HM Revenue and Customs<span class="rf-tip">Real HMRC emails only come from @hmrc.gov.uk, never from .co domains. Always verify at gov.uk directly.</span></span></p>'},
{id:5,phishing:false,from:'Dropbox',addr:'no-reply@dropbox.com',to:'you@company.com',date:'Sun, 15:14',subject:'Rachel shared "Project Brief_Final_v3.pdf" with you',color:'#0061fe',body:'<p>Hi,</p><p><strong>Rachel (r.chen@yourcompany.com)</strong> shared a file with you on Dropbox.</p><div style="background:#f7f7f7;border-radius:8px;padding:16px;margin:12px 0"><div style="font-size:.9rem;font-weight:600">📄 Project Brief_Final_v3.pdf</div><div style="font-size:.78rem;color:#888;margin-top:4px">Shared by Rachel Chen · 2.4 MB</div></div><p><a href="#" onclick="return false" style="background:#0061fe;color:#fff;padding:10px 20px;border-radius:6px;display:inline-block;text-decoration:none">View file</a></p><p style="font-size:.75rem;color:#888">Shared via Dropbox. Sender r.chen@yourcompany.com is a verified Dropbox user.</p>'},
{id:6,phishing:true,from:'CEO — David Clarke',addr:'d.clarke@yourcompanny.com',to:'you@company.com',date:'Fri, 17:58',subject:'Confidential — Urgent wire transfer needed',color:'#9c27b0',body:'<p>Hi,</p><p>I am in a <span class="rf">board meeting and cannot take calls<span class="rf-tip">Claiming unavailability to prevent phone verification is the hallmark of Business Email Compromise (BEC) attacks.</span></span>. I need you to urgently wire <strong>£18,500</strong> to a new supplier before close of business.</p><p>This is <span class="rf">strictly confidential — do not tell Finance or colleagues<span class="rf-tip">Requesting secrecy bypasses your company approval chain. Legitimate financial requests always follow proper authorisation procedures.</span></span>.</p><p>Account: Zenith Consulting Ltd · Sort: 20-18-53 · Acc: 83924710</p><p>Confirm when done.<br><span class="rf">David Clarke | CEO<span class="rf-tip">Sender email is yourcompanny.com with double N in company. This is a typosquat domain registered by attackers. ALWAYS verify financial requests by calling the sender on a known number.</span></span></p>'}
];

var answered={},correct=0,wrong=0;
function renderList(){
  var h='';
  emails.forEach(function(e){
    var done=answered[e.id]!==undefined;
    var t=done?(answered[e.id]===e.phishing?'✅ ':'❌ '):'';
    h+='<div class="gmail-list-item" id="li-'+e.id+'" onclick="openEmail('+e.id+')">'+
      '<div class="gl-avatar" style="background:'+e.color+'">'+e.from[0]+'</div>'+
      '<div class="gl-middle"><div class="gl-from">'+e.from+'</div><div class="gl-subject">'+t+e.subject+'</div></div>'+
      '<div class="gl-date">'+e.date.split(',')[0]+'</div></div>';
  });
  document.getElementById('email-list').innerHTML=h;
}
function openEmail(id){
  var e=emails[id];
  document.querySelectorAll('.gmail-list-item').forEach(function(el){el.classList.remove('selected')});
  var li=document.getElementById('li-'+id);if(li)li.classList.add('selected');
  var done=answered[id]!==undefined;
  var ok=done&&(answered[id]===e.phishing);
  document.getElementById('email-pane').innerHTML=
    '<div class="gmail-pane-header"><div class="gmail-subject-line">'+e.subject+'</div>'+
    '<div class="gmail-meta"><div class="gmail-avatar-lg" style="background:'+e.color+'">'+e.from[0]+'</div>'+
    '<div><div class="gmail-from-name">'+e.from+'</div><div class="gmail-from-addr">From: '+e.addr+' &nbsp;&middot;&nbsp; To: '+e.to+'</div></div>'+
    '<div class="gmail-timestamp">'+e.date+'</div></div></div>'+
    '<div class="gmail-body">'+e.body+'</div>'+
    '<div class="gmail-actions">'+(done?
      '<span class="'+(ok?'verdict-correct':'verdict-wrong')+'">'+(ok?'✅ Correct!':'❌ Wrong')+'</span>'+
      '<span style="font-size:.82rem;color:#555;margin-left:6px">This is <strong>'+(e.phishing?'PHISHING':'LEGITIMATE')+'</strong>. '+(e.phishing?'Hover red text to see all red flags.':'No phishing indicators.')+'</span>'
    :'<button class="btn-phish" onclick="answer('+id+',true)">🚩 Mark as Phishing</button>'+
      '<button class="btn-safe" onclick="answer('+id+',false)">✅ Mark as Legitimate</button>'+
      '<span style="font-size:.76rem;color:#888;margin-left:6px">Hover red text for clues</span>'
    )+'</div>';
}
function answer(id,mp){
  var e=emails[id];var ok=(mp===e.phishing);
  answered[id]=mp;if(ok)correct++;else wrong++;
  document.getElementById('sp-correct').textContent=correct;
  document.getElementById('sp-wrong').textContent=wrong;
  document.getElementById('sp-remain').textContent=emails.length-Object.keys(answered).length;
  renderList();openEmail(id);
  if(Object.keys(answered).length===emails.length)setTimeout(showResult,700);
}
function showResult(){
  var p=Math.round(correct/emails.length*100);var xp=p>=80?200:p>=60?100:50;
  var ov=document.getElementById('result-overlay');ov.style.display='flex';
  ov.innerHTML='<div class="result-card">'+
    '<div style="font-size:3rem;margin-bottom:.5rem">'+(p>=80?'🏆':p>=60?'🎯':'😬')+'</div>'+
    '<div style="font-size:2.5rem;font-weight:800;color:'+(p>=60?'var(--green)':'var(--red)')+'">'+p+'%</div>'+
    '<div style="color:var(--muted);margin:.4rem 0 1rem">'+correct+' of '+emails.length+' correctly identified</div>'+
    '<div style="background:rgba(0,212,255,.1);border:1px solid rgba(0,212,255,.25);color:var(--accent);border-radius:20px;padding:4px 16px;display:inline-block;font-size:.82rem;font-weight:700;margin-bottom:1.2rem">+'+xp+' XP Earned</div>'+
    '<p style="font-size:.88rem;color:var(--muted);line-height:1.7;margin-bottom:1.5rem">'+(p>=80?'Sharp eye! Always check sender domains character by character, never click links in urgent emails, and verify financial requests by phone.':'Key tells: check sender domains letter-by-letter, watch for urgency and secrecy, never provide credentials or process payments from email alone.')+'</p>'+
    '<div style="display:flex;gap:10px;justify-content:center">'+
      '<button onclick="location.reload()" style="background:var(--surface2);border:1px solid var(--border);color:var(--text);padding:10px 20px;border-radius:8px;cursor:pointer;font-weight:600">🔄 Retry</button>'+
      '<a href="<?= APP_URL ?>/dashboard.php" style="background:linear-gradient(135deg,var(--accent),#0099cc);color:#000;padding:10px 20px;border-radius:8px;font-weight:600;text-decoration:none">← Dashboard</a>'+
    '</div></div>';
  fetch('<?= APP_URL ?>/api/save-score.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({game_slug:'phishing-email',score:correct,max_score:emails.length,xp_earned:xp,percentage:p})});
}
renderList();
</script>
</body>
</html>