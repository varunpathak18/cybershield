// ═══════════════════════════════════════
// CyberShield — Shared JavaScript
// ═══════════════════════════════════════

// ── TOAST NOTIFICATIONS ──
function showToast(message, type = 'info', duration = 3500) {
  const wrap = document.getElementById('toast-wrap');
  if (!wrap) return;
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.textContent = message;
  wrap.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px)';
    toast.style.transition = 'all .3s';
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

// ── XP GAIN ANIMATION ──
function showXPGain(amount) {
  const el = document.createElement('div');
  el.textContent = `+${amount} XP`;
  el.style.cssText = `
    position:fixed; bottom:5rem; right:2rem; z-index:9998;
    background:linear-gradient(135deg,#7c3aed,#a855f7);
    color:white; font-weight:800; font-size:1.1rem;
    padding:10px 20px; border-radius:12px;
    animation: xpFloat 2s ease forwards;
    pointer-events:none;
  `;
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 2000);
}

if (!document.getElementById('xp-keyframe')) {
  const style = document.createElement('style');
  style.id = 'xp-keyframe';
  style.textContent = `
    @keyframes xpFloat {
      0%  { opacity:0; transform:translateY(0); }
      20% { opacity:1; }
      80% { opacity:1; transform:translateY(-40px); }
      100%{ opacity:0; transform:translateY(-60px); }
    }
  `;
  document.head.appendChild(style);
}

// ── CONFIRM BEFORE LEAVING GAME ──
let gameInProgress = false;
window.addEventListener('beforeunload', e => {
  if (gameInProgress) {
    e.preventDefault();
    e.returnValue = '';
  }
});

// ── AJAX HELPER ──
async function postJSON(url, data) {
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  });
  return res.json();
}

// ── COUNTDOWN UTIL ──
function formatTime(seconds) {
  const m = Math.floor(seconds / 60);
  const s = seconds % 60;
  return `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
}

// ── COPY TO CLIPBOARD ──
function copyText(text) {
  navigator.clipboard?.writeText(text).then(() => showToast('Copied!', 'success', 1500));
}

// ── RIPPLE EFFECT ON BUTTONS ──
document.addEventListener('click', function(e) {
  const btn = e.target.closest('.btn');
  if (!btn || btn.disabled) return;
  const ripple = document.createElement('span');
  const rect = btn.getBoundingClientRect();
  ripple.style.cssText = `
    position:absolute; width:100px; height:100px; border-radius:50%;
    background:rgba(255,255,255,.15); pointer-events:none;
    left:${e.clientX - rect.left - 50}px; top:${e.clientY - rect.top - 50}px;
    transform:scale(0); animation:ripple .6s linear; z-index:0;
  `;
  btn.style.position = 'relative'; btn.style.overflow = 'hidden';
  btn.appendChild(ripple);
  setTimeout(() => ripple.remove(), 600);
});

if (!document.getElementById('ripple-kf')) {
  const s = document.createElement('style');
  s.id = 'ripple-kf';
  s.textContent = `@keyframes ripple { to { transform:scale(4); opacity:0; } }`;
  document.head.appendChild(s);
}
