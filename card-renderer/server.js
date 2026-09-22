// Photo-card renderer for Facebook posts. No AI: it only lays out the title,
// subtitle, key points and date the pipeline already has. @napi-rs/canvas (Skia)
// shapes Bangla correctly via HarfBuzz. Fonts and logo are bundled, so it needs
// no system fonts and is identical locally and in Docker.
const express = require('express');
const path = require('path');
const { createCanvas, GlobalFonts, loadImage } = require('@napi-rs/canvas');
const QRCode = require('qrcode');

const FONTS = path.join(__dirname, 'fonts');
GlobalFonts.registerFromPath(path.join(FONTS, 'NotoSerifBengali-VF.ttf'), 'CardSerif');
GlobalFonts.registerFromPath(path.join(FONTS, 'NotoSansBengali-VF.ttf'), 'CardSans');

const W = 1080, H = 1080;
const INK = '#0f172a', INK2 = '#1e293b', RED = '#ef4444', WHITE = '#ffffff', MUTE = '#cbd5e1', MUTE2 = '#94a3b8';

let LOGO = null;
loadImage(path.join(__dirname, 'logo.png')).then((img) => { LOGO = img; }).catch(() => {});

function wrap(ctx, text, maxWidth, maxLines) {
  const words = String(text || '').trim().split(/\s+/).filter(Boolean);
  const lines = []; let line = '';
  for (const w of words) {
    const t = line ? line + ' ' + w : w;
    if (ctx.measureText(t).width > maxWidth && line) {
      lines.push(line); line = w;
      if (lines.length === maxLines) break;
    } else line = t;
  }
  if (lines.length < maxLines && line) lines.push(line);
  if (lines.length === maxLines) {
    const consumed = lines.join(' ').split(/\s+/).length;
    if (consumed < words.length) {
      let last = lines[maxLines - 1];
      while (last && ctx.measureText(last + '…').width > maxWidth) last = last.replace(/\s*\S+$/, '');
      lines[maxLines - 1] = (last || '') + '…';
    }
  }
  return lines;
}

async function renderCard({ title, subtitle, points, section, date, url, brand }) {
  const canvas = createCanvas(W, H);
  const ctx = canvas.getContext('2d');

  const g = ctx.createLinearGradient(0, 0, W, H);
  g.addColorStop(0, INK); g.addColorStop(1, INK2);
  ctx.fillStyle = g; ctx.fillRect(0, 0, W, H);
  ctx.fillStyle = RED; ctx.fillRect(0, 0, 18, H);

  const x = 80, right = W - 80, maxW = right - x;

  // --- header: logo top-left, QR top-right ---
  const headerY = 72, logoH = 76;
  if (LOGO) {
    const ratio = LOGO.width / LOGO.height;
    ctx.drawImage(LOGO, x, headerY, logoH * ratio, logoH);
    ctx.fillStyle = WHITE; ctx.font = `700 40px CardSans`;
    ctx.fillText(String(brand || 'বারিন্দ পোস্ট'), x + logoH * ratio + 22, headerY + 52);
  } else {
    ctx.fillStyle = WHITE; ctx.font = `700 40px CardSans`;
    ctx.fillText(String(brand || 'বারিন্দ পোস্ট'), x, headerY + 52);
  }
  if (url) {
    try {
      const qrBuf = await QRCode.toBuffer(String(url), { type: 'png', width: 160, margin: 1,
        color: { dark: '#0f172aff', light: '#ffffffff' } });
      const qr = await loadImage(qrBuf);
      const qrSize = 150, qx = right - qrSize, qy = headerY - 10;
      ctx.fillStyle = WHITE; ctx.fillRect(qx - 8, qy - 8, qrSize + 16, qrSize + 16);
      ctx.drawImage(qr, qx, qy, qrSize, qrSize);
    } catch (e) { /* no QR */ }
  }

  // divider
  ctx.strokeStyle = 'rgba(255,255,255,0.12)'; ctx.lineWidth = 2;
  ctx.beginPath(); ctx.moveTo(x, headerY + logoH + 44); ctx.lineTo(right, headerY + logoH + 44); ctx.stroke();

  // --- middle: news section ---
  let y = headerY + logoH + 110;
  if (section) {
    ctx.fillStyle = RED; ctx.font = `700 30px CardSans`;
    ctx.fillText(String(section), x, y); y += 54;
  }
  ctx.fillStyle = WHITE; ctx.font = `700 60px CardSerif`;
  for (const line of wrap(ctx, title, maxW, 4)) { y += 74; ctx.fillText(line, x, y); }
  y += 34;
  if (subtitle) {
    ctx.fillStyle = MUTE; ctx.font = `400 34px CardSans`;
    for (const line of wrap(ctx, subtitle, maxW, 3)) { y += 48; ctx.fillText(line, x, y); }
    y += 26;
  }
  const pts = (Array.isArray(points) ? points : []).filter(Boolean).slice(0, 4);
  ctx.font = `400 31px CardSans`;
  for (const p of pts) {
    if (y > H - 150) break;
    ctx.fillStyle = RED; ctx.fillRect(x + 2, y + 22, 15, 15);
    ctx.fillStyle = '#f1f5f9';
    for (const line of wrap(ctx, p, maxW - 44, 2)) { y += 45; ctx.fillText(line, x + 44, y); }
    y += 18;
  }

  // --- next line: date, smaller font ---
  if (date) {
    ctx.fillStyle = MUTE2; ctx.font = `500 27px CardSans`;
    ctx.fillText(String(date), x, H - 70);
  }

  return canvas.toBuffer('image/png');
}

const app = express();
app.use(express.json({ limit: '256kb' }));
app.get('/healthz', (_, res) => res.json({ ok: true, fonts: GlobalFonts.families.length, logo: !!LOGO }));
app.post('/render', async (req, res) => {
  try {
    if (!req.body || !req.body.title) return res.status(422).json({ error: 'title is required' });
    const png = await renderCard(req.body);
    if (req.query.format === 'png') return res.set('Content-Type', 'image/png').send(png);
    res.json({ image_base64: png.toString('base64'), bytes: png.length });
  } catch (e) {
    res.status(500).json({ error: String((e && e.message) || e) });
  }
});
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log('card-renderer on :3000, fonts:', GlobalFonts.families.length));
