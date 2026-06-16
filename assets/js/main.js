/* CyberShield — Shared JS */

// ── TOAST NOTIFICATIONS ──
function showToast(msg, type = 'info', duration = 4000) {
  const wrap = document.getElementById('toast-wrap');
  if (!wrap) return;
  const t = document.createElement('div');
  t.className = 'toast ' + type;
  t.textContent = msg;
  wrap.appendChild(t);
  setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .4s'; setTimeout(() => t.remove(), 400); }, duration);
}

// ── CONFETTI (lightweight) ──
function fireConfetti() {
  const colors = ['#00d4ff','#22c55e','#7c3aed','#eab308','#ef4444'];
  for (let i = 0; i < 60; i++) {
    const el = document.createElement('div');
    el.style.cssText = `position:fixed;top:0;left:${Math.random()*100}vw;width:8px;height:8px;border-radius:2px;background:${colors[Math.floor(Math.random()*colors.length)]};z-index:99999;animation:confettiFall ${1+Math.random()*2}s ease forwards;transform:rotate(${Math.random()*360}deg)`;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3000);
  }
}
const confettiStyle = document.createElement('style');
confettiStyle.textContent = '@keyframes confettiFall{to{transform:translateY(100vh) rotate(720deg);opacity:0}}';
document.head.appendChild(confettiStyle);