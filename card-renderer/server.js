// Photo-card renderer for image-less news. No AI: it only lays out the title,
// subtitle and key points the pipeline already produced. Uses @napi-rs/canvas
// (Skia), which shapes Bangla correctly via HarfBuzz — GD on the PHP server
// cannot, and this is native on arm64/amd64 with no headless browser.
const express = require('express');
const { createCanvas, GlobalFonts } = require('@napi-rs/canvas');
const fs = require('fs');

const FONT_DIR = '/usr/share/fonts/truetype/noto/';
function reg(file, family) {
  const p = FONT_DIR + file;
  if (fs.existsSync(p)) { GlobalFonts.registerFromPath(p, family); return true; }
  return false;
}
const SERIF = 'CardSerif', SANS = 'CardSans', SANSB = 'CardSansB';
reg('NotoSerifBengali-Bold.ttf', SERIF) || reg('NotoSansBengali-Bold.ttf', SERIF);
reg('NotoSansBengali-Regular.ttf', SANS);
reg('NotoSansBengali-Bold.ttf', SANSB);

const W = 1200, H = 630;

function wrap(ctx, text, maxWidth, maxLines) {
  const words = String(text || '').trim().split(/\s+/).filter(Boolean);
  const lines = [];
  let line = '';
  for (const w of words) {
    const t = line ? line + ' ' + w : w;
    if (ctx.measureText(t).width > maxWidth && line) {
      lines.push(line); line = w;
      if (lines.length === maxLines) break;
    } else {
      line = t;
    }
  }
  if (lines.length < maxLines && line) lines.push(line);
  if (lines.length === maxLines) {
    // ellipsize the last line if content remains
    let last = lines[maxLines - 1];
    const consumed = lines.join(' ').split(/\s+/).length;
    if (consumed < words.length) {
      while (last && ctx.measureText(last + '…').width > maxWidth) last = last.replace(/\s*\S+$/, '');
      lines[maxLines - 1] = (last || '') + '…';
    }
  }
  return lines;
}

function renderCard({ title, subtitle, points, brand, kicker }) {
  const canvas = createCanvas(W, H);
  const ctx = canvas.getContext('2d');

  const grad = ctx.createLinearGradient(0, 0, W, H);
  grad.addColorStop(0, '#0f172a'); grad.addColorStop(1, '#1e293b');
  ctx.fillStyle = grad; ctx.fillRect(0, 0, W, H);
  ctx.fillStyle = '#ef4444'; ctx.fillRect(0, 0, 16, H);

  const x = 96, right = W - 72, maxW = right - x;
  let y = 96;

  if (kicker) {
    ctx.fillStyle = '#ef4444'; ctx.font = `700 26px ${SANSB}`;
    ctx.fillText(String(kicker), x, y); y += 44;
  }

  ctx.fillStyle = '#ffffff'; ctx.font = `700 52px ${SERIF}`;
  for (const line of wrap(ctx, title, maxW, 3)) { y += 60; ctx.fillText(line, x, y); }
  y += 30;

  if (subtitle) {
    ctx.fillStyle = '#cbd5e1'; ctx.font = `500 29px ${SANS}`;
    for (const line of wrap(ctx, subtitle, maxW, 2)) { y += 40; ctx.fillText(line, x, y); }
    y += 20;
  }

  const pts = (Array.isArray(points) ? points : []).filter(Boolean).slice(0, 4);
  ctx.font = `400 26px ${SANS}`;
  for (const p of pts) {
    const lines = wrap(ctx, p, maxW - 40, 2);
    ctx.fillStyle = '#ef4444'; ctx.fillRect(x + 2, y + 20, 14, 14);
    ctx.fillStyle = '#f1f5f9';
    let first = true;
    for (const line of lines) { y += 38; ctx.fillText(line, x + 40, y); first = false; }
    y += 16;
    if (y > H - 90) break;
  }

  // brand footer
  ctx.fillStyle = '#ef4444'; ctx.fillRect(x, H - 60, 30, 30);
  ctx.fillStyle = '#ffffff'; ctx.font = `700 28px ${SANSB}`;
  ctx.fillText(String(brand || 'বারিন্দ পোস্ট'), x + 44, H - 36);

  return canvas.toBuffer('image/png');
}

const app = express();
app.use(express.json({ limit: '256kb' }));
app.get('/healthz', (_, res) => res.json({ ok: true, fonts: GlobalFonts.families.length }));
app.post('/render', (req, res) => {
  try {
    if (!req.body || !req.body.title) return res.status(422).json({ error: 'title is required' });
    const png = renderCard(req.body);
    // n8n passes this straight to the PHP attach endpoint, so default to base64 JSON
    // (binary is available with ?format=png).
    if (req.query.format === 'png') return res.set('Content-Type', 'image/png').send(png);
    res.json({ image_base64: png.toString('base64'), bytes: png.length });
  } catch (e) {
    res.status(500).json({ error: String((e && e.message) || e) });
  }
});
app.listen(3000, () => console.log('card-renderer on :3000, fonts:', GlobalFonts.families.length));
