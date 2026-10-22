/**
 * Barind Post photo-card renderer: draws a social card on a 2D canvas context.
 *
 * The same module runs unchanged in the browser (admin → Photo Cards) and in Node
 * with @napi-rs/canvas (design checks), so what the editor previews is exactly what
 * is exported. Bangla is shaped by the canvas text engine (HarfBuzz/Skia), which
 * PHP's GD cannot do — that is why cards are drawn client-side and only uploaded.
 *
 * Fonts: the caller registers 'CardSerif' (Noto Serif Bengali 700/800) and
 * 'CardSans' (Noto Sans Bengali 400/600/700) from public/assets/fonts before drawing.
 */

export const BRAND = {
    name: 'বারিন্দ পোস্ট',
    url: 'barindpost.com',
    ink: '#14140f',
    paper: '#ffffff',
    accent: '#c8102e',
    accentDark: '#9d0c24',
    handle: '@barindpost',
    rule: '#e0dfd8',
    muted: '#6b6a62',
    serif: 'CardSerif',
    sans: 'CardSans',
};

export const FORMATS = {
    link:     { w: 1200, h: 630,  label: 'Link share',    note: '1200×630 · Facebook/WhatsApp link preview' },
    square:   { w: 1080, h: 1080, label: 'Square post',   note: '1080×1080 · Facebook/Instagram feed' },
    portrait: { w: 1080, h: 1350, label: 'Portrait post', note: '1080×1350 · Facebook/Instagram feed' },
    story:    { w: 1080, h: 1920, label: 'Story',         note: '1080×1920 · Stories/Reels cover' },
};

export const TEMPLATES = {
    photo: { label: 'Photo',    note: 'Full-bleed photo, headline over a dark gradient' },
    text:  { label: 'Headline', note: 'Headline, standfirst and key points' },
    quote: { label: 'Quote',    note: 'A pulled quote with attribution' },
};

const BN_DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
const BN_MONTHS = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];

export const toBanglaDigits = (value) => String(value).replace(/\d/g, (d) => BN_DIGITS[d]);

/** "2026-10-22 15:25:00" → "২২ অক্টোবর ২০২৬" */
export function banglaDate(value) {
    const d = value instanceof Date ? value : new Date(String(value).replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return '';
    return `${toBanglaDigits(d.getDate())} ${BN_MONTHS[d.getMonth()]} ${toBanglaDigits(d.getFullYear())}`;
}

const font = (weight, size, family) => `${weight} ${Math.round(size)}px ${family}`;

/** Greedy word wrap; the last line gets an ellipsis when the text does not fit in maxLines. */
export function wrapText(ctx, text, maxWidth, maxLines = Infinity) {
    const words = String(text ?? '').trim().split(/\s+/).filter(Boolean);
    const lines = [];
    let line = '';
    let overflow = false;
    for (const word of words) {
        const candidate = line ? `${line} ${word}` : word;
        if (line && ctx.measureText(candidate).width > maxWidth) {
            lines.push(line);
            line = word;
            if (lines.length === maxLines) { overflow = true; break; }
        } else {
            line = candidate;
        }
    }
    if (!overflow && line) lines.push(line);
    if (overflow) {
        let last = lines[maxLines - 1];
        while (last && ctx.measureText(`${last}…`).width > maxWidth) last = last.replace(/\s*\S+$/, '');
        lines[maxLines - 1] = `${last || ''}…`;
    }
    return { lines, overflow };
}

/**
 * Largest font size (maxSize → minSize) at which the text fits maxLines × maxWidth and,
 * if given, maxHeight. Returns the lines and the line height; leaves ctx.font set.
 */
export function fitText(ctx, text, { family, weight, maxWidth, maxLines, maxSize, minSize, step = 2, lineHeight = 1.38, maxHeight = Infinity }) {
    let result = null;
    for (let size = maxSize; size >= minSize; size -= step) {
        ctx.font = font(weight, size, family);
        const { lines, overflow } = wrapText(ctx, text, maxWidth, maxLines);
        result = { size, lines, lineHeight: size * lineHeight };
        const fits = !overflow
            && !lines.some((l) => ctx.measureText(l).width > maxWidth)
            && lines.length * result.lineHeight <= maxHeight;
        if (fits) return result;
    }
    return result;
}

function drawLines(ctx, lines, x, baseline, lineHeight, align = 'left') {
    let y = baseline;
    for (const line of lines) {
        const dx = align === 'right' ? -ctx.measureText(line).width : 0;
        ctx.fillText(line, x + dx, y);
        y += lineHeight;
    }
    return y;
}

/** Scale-to-cover crop of img into the box; focalX/focalY (0–1) choose which part survives the crop. */
export function drawCover(ctx, img, x, y, w, h, focalX = 0.5, focalY = 0.5) {
    const iw = img.naturalWidth || img.width;
    const ih = img.naturalHeight || img.height;
    const scale = Math.max(w / iw, h / ih);
    const sw = w / scale;
    const sh = h / scale;
    ctx.drawImage(img, (iw - sw) * focalX, (ih - sh) * focalY, sw, sh, x, y, w, h);
}

function rgba(hex, alpha) {
    const n = parseInt(hex.slice(1), 16);
    return `rgba(${(n >> 16) & 255}, ${(n >> 8) & 255}, ${n & 255}, ${alpha})`;
}

/** Logo chip + wordmark. Returns the x where the brand block ends. */
function drawBrand(ctx, spec, x, y, size, color) {
    let cx = x;
    if (spec.showLogo !== false && spec.logo) {
        ctx.save();
        ctx.beginPath();
        ctx.roundRect(x, y, size, size, size * 0.18);
        ctx.clip();
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(x, y, size, size);
        ctx.drawImage(spec.logo, x, y, size, size);
        ctx.restore();
        cx = x + size + size * 0.28;
    }
    const name = spec.brandName || BRAND.name;
    ctx.fillStyle = color;
    ctx.font = font(700, size * 0.5, BRAND.sans);
    ctx.fillText(name, cx, y + size * 0.67);
    return cx + ctx.measureText(name).width;
}

/** Red tag with white text, e.g. the section name. Returns its width. */
function drawTag(ctx, text, x, y, u, fill = BRAND.accent, color = '#ffffff') {
    ctx.font = font(700, 26 * u, BRAND.sans);
    const padX = 18 * u;
    const w = ctx.measureText(text).width + padX * 2;
    const h = 46 * u;
    ctx.fillStyle = fill;
    ctx.beginPath();
    ctx.roundRect(x, y, w, h, 6 * u);
    ctx.fill();
    ctx.fillStyle = color;
    ctx.fillText(text, x + padX, y + h * 0.7);
    return w;
}

/** Thin rule with a text at each end (either may be empty). */
function drawFooter(ctx, spec, m, u, ruleY, textColor, ruleColor, left, right) {
    const W = spec.format.w;
    ctx.fillStyle = ruleColor;
    ctx.fillRect(m, ruleY, W - 2 * m, Math.max(2, 2 * u));
    const baseline = ruleY + 46 * u;
    ctx.fillStyle = textColor;
    ctx.font = font(600, 26 * u, BRAND.sans);
    if (left) ctx.fillText(left, m, baseline);
    ctx.font = font(700, 26 * u, BRAND.sans);
    if (right) ctx.fillText(right, W - m - ctx.measureText(right).width, baseline);
    return baseline;
}

/** Red "breaking news" band across the top. Returns its height. */
function drawBreakingBand(ctx, spec, u) {
    const W = spec.format.w;
    const h = 64 * u;
    ctx.fillStyle = BRAND.accent;
    ctx.fillRect(0, 0, W, h);
    ctx.fillStyle = '#ffffff';
    ctx.beginPath();
    ctx.arc(64 * u, h / 2, 9 * u, 0, Math.PI * 2);
    ctx.fill();
    ctx.font = font(700, 28 * u, BRAND.sans);
    ctx.fillText(spec.breakingLabel || 'ব্রেকিং নিউজ', 88 * u, h * 0.68);
    return h;
}

/* ------------------------------------------------------------------ templates */

function renderPhoto(ctx, spec, u, m) {
    const { w: W, h: H } = spec.format;
    const photo = spec.photo && spec.photo.image;
    const darken = Math.min(1, Math.max(0, spec.photo?.darken ?? 0.5));

    ctx.fillStyle = BRAND.ink;
    ctx.fillRect(0, 0, W, H);
    if (photo) {
        drawCover(ctx, photo, 0, 0, W, H, 0.5, spec.photo.focalY ?? 0.5);
    } else {
        // No photo: deep ink with a warm accent glow so the card still has depth.
        const glow = ctx.createRadialGradient(W * 0.15, H * 0.95, 0, W * 0.15, H * 0.95, Math.max(W, H) * 0.9);
        glow.addColorStop(0, rgba(BRAND.accentDark, 0.55));
        glow.addColorStop(1, rgba(BRAND.ink, 0));
        ctx.fillStyle = glow;
        ctx.fillRect(0, 0, W, H);
    }

    // Legibility: a soft top shade for the brand row, a strong bottom shade for the headline.
    const top = ctx.createLinearGradient(0, 0, 0, H * 0.3);
    top.addColorStop(0, 'rgba(0,0,0,0.55)');
    top.addColorStop(1, 'rgba(0,0,0,0)');
    ctx.fillStyle = top;
    ctx.fillRect(0, 0, W, H * 0.3);
    const bottom = ctx.createLinearGradient(0, H * 0.3, 0, H);
    bottom.addColorStop(0, rgba(BRAND.ink, 0));
    bottom.addColorStop(0.55, rgba(BRAND.ink, 0.45 + 0.35 * darken));
    bottom.addColorStop(1, rgba(BRAND.ink, 0.85 + 0.15 * darken));
    ctx.fillStyle = bottom;
    ctx.fillRect(0, H * 0.3, W, H * 0.7);

    let headerTop = m;
    if (spec.breaking) headerTop = drawBreakingBand(ctx, spec, u) + m * 0.75;
    drawBrand(ctx, spec, m, headerTop, 72 * u, '#ffffff');

    // Bottom block, measured first so it sits on the footer.
    const maxWidth = W - 2 * m;
    const landscape = H / W < 0.8;
    const head = fitText(ctx, spec.title, {
        family: BRAND.serif, weight: 800, maxWidth, maxLines: landscape ? 3 : 4,
        maxSize: (landscape ? 66 : 76) * u * (spec.headlineScale || 1), minSize: 40 * u, maxHeight: H * 0.5,
    });
    const headLH = head.lineHeight;
    let sub = null;
    if (spec.showSubtitle !== false && spec.subtitle) {
        ctx.font = font(400, 30 * u, BRAND.sans);
        sub = wrapText(ctx, spec.subtitle, maxWidth, landscape ? 1 : 2).lines;
    }
    const subLH = 30 * u * 1.5;
    const footerRuleY = H - m - 46 * u;
    const kickerH = spec.kicker ? 46 * u + 24 * u : 0;
    const blockH = kickerH + head.lines.length * headLH + (sub ? 18 * u + sub.length * subLH : 0);
    let y = footerRuleY - 40 * u - blockH;

    if (spec.kicker) {
        drawTag(ctx, spec.kicker, m, y, u);
        y += kickerH;
    }
    ctx.fillStyle = '#ffffff';
    ctx.font = font(800, head.size, BRAND.serif);
    ctx.shadowColor = 'rgba(0,0,0,0.45)';
    ctx.shadowBlur = 18 * u;
    ctx.shadowOffsetY = 2 * u;
    y = drawLines(ctx, head.lines, m, y + head.size * 0.85, headLH) - headLH + head.size * 0.45;
    ctx.shadowColor = 'transparent';
    ctx.shadowBlur = 0;
    ctx.shadowOffsetY = 0;
    if (sub) {
        ctx.fillStyle = 'rgba(255,255,255,0.82)';
        ctx.font = font(400, 30 * u, BRAND.sans);
        drawLines(ctx, sub, m, y + 18 * u + 30 * u, subLH);
    }
    drawFooter(ctx, spec, m, u, footerRuleY, 'rgba(255,255,255,0.78)', 'rgba(255,255,255,0.28)', spec.date, spec.url || BRAND.url);
}

function renderText(ctx, spec, u, m) {
    const { w: W, h: H } = spec.format;
    const dark = spec.theme === 'dark';
    const fg = dark ? '#ffffff' : BRAND.ink;
    const mutedFg = dark ? 'rgba(255,255,255,0.72)' : BRAND.muted;
    const ruleColor = dark ? 'rgba(255,255,255,0.22)' : BRAND.rule;
    const landscape = H / W < 0.8;
    const tall = H / W > 1.5;

    ctx.fillStyle = dark ? BRAND.ink : BRAND.paper;
    ctx.fillRect(0, 0, W, H);
    if (dark) {
        const glow = ctx.createRadialGradient(W, 0, 0, W, 0, Math.max(W, H));
        glow.addColorStop(0, rgba(BRAND.accentDark, 0.35));
        glow.addColorStop(1, rgba(BRAND.ink, 0));
        ctx.fillStyle = glow;
        ctx.fillRect(0, 0, W, H);
    }
    let top = 0;
    if (spec.breaking) {
        top = drawBreakingBand(ctx, spec, u);
    } else {
        ctx.fillStyle = BRAND.accent;
        ctx.fillRect(0, 0, W, 14 * u);
        top = 14 * u;
    }

    const headerTop = top + m * 0.9;
    drawBrand(ctx, spec, m, headerTop, 64 * u, fg);
    if (spec.date) {
        ctx.fillStyle = mutedFg;
        ctx.font = font(600, 26 * u, BRAND.sans);
        ctx.fillText(spec.date, W - m - ctx.measureText(spec.date).width, headerTop + 64 * u * 0.67);
    }

    const maxWidth = W - 2 * m;
    const footerRuleY = H - m - 46 * u;
    const bodyTop = headerTop + 64 * u + m * 0.9;
    const bodyBottom = footerRuleY - m * 0.7;

    // Measure everything first, then centre the block when it is short.
    const kickerH = spec.kicker ? 30 * u + 30 * u : 0;
    const head = fitText(ctx, spec.title, {
        family: BRAND.serif, weight: 800, maxWidth, maxLines: landscape ? 3 : tall ? 6 : 4,
        maxSize: (landscape ? 70 : 80) * u * (spec.headlineScale || 1), minSize: 42 * u,
        maxHeight: bodyBottom - bodyTop - kickerH,
    });
    const headLH = head.lineHeight;
    let sub = null;
    if (spec.showSubtitle !== false && spec.subtitle) {
        ctx.font = font(400, 32 * u, BRAND.sans);
        sub = wrapText(ctx, spec.subtitle, maxWidth, landscape ? 2 : 3).lines;
    }
    const subLH = 32 * u * 1.5;
    const points = [];
    if (spec.showPoints !== false && Array.isArray(spec.points) && !landscape) {
        ctx.font = font(500, 30 * u, BRAND.sans);
        for (const p of spec.points.filter(Boolean).slice(0, tall ? 5 : 4)) {
            points.push(wrapText(ctx, p, maxWidth - 44 * u, 2).lines);
        }
    }
    const pointLH = 30 * u * 1.45;
    const avail = bodyBottom - bodyTop;
    const measure = () => {
        let h = kickerH + head.lines.length * headLH;
        if (sub) h += 24 * u + sub.length * subLH;
        if (points.length) h += 36 * u + points.reduce((n, p) => n + p.length * pointLH + 16 * u, 0);
        return h;
    };
    // Too much copy for the format: drop key points from the end, then the standfirst.
    let blockH = measure();
    while (blockH > avail && points.length) { points.pop(); blockH = measure(); }
    if (blockH > avail && sub) { sub = null; blockH = measure(); }
    let y = bodyTop + Math.max(0, (avail - blockH) * 0.42);

    if (spec.kicker) {
        ctx.fillStyle = BRAND.accent;
        ctx.fillRect(m, y, 64 * u, 6 * u);
        ctx.font = font(700, 30 * u, BRAND.sans);
        ctx.fillText(spec.kicker, m, y + 6 * u + 36 * u);
        y += kickerH + 18 * u;
    }
    ctx.fillStyle = fg;
    ctx.font = font(800, head.size, BRAND.serif);
    y = drawLines(ctx, head.lines, m, y + head.size * 0.85, headLH) - headLH + head.size * 0.45;
    if (sub) {
        y += 24 * u;
        ctx.fillStyle = mutedFg;
        ctx.font = font(400, 32 * u, BRAND.sans);
        y = drawLines(ctx, sub, m, y + 32 * u, subLH) - subLH + 32 * u * 0.45;
    }
    if (points.length) {
        y += 36 * u;
        for (const lines of points) {
            ctx.fillStyle = BRAND.accent;
            ctx.fillRect(m + 2 * u, y + 30 * u * 0.42, 14 * u, 14 * u);
            ctx.fillStyle = fg;
            ctx.font = font(500, 30 * u, BRAND.sans);
            y = drawLines(ctx, lines, m + 44 * u, y + 30 * u, pointLH) - pointLH + 30 * u * 0.45 + 16 * u;
        }
    }
    drawFooter(ctx, spec, m, u, footerRuleY, mutedFg, ruleColor, spec.url || BRAND.url, spec.handle || BRAND.handle);
}

function renderQuote(ctx, spec, u, m) {
    const { w: W, h: H } = spec.format;
    const dark = spec.theme !== 'light';
    const fg = dark ? '#ffffff' : BRAND.ink;
    const mutedFg = dark ? 'rgba(255,255,255,0.72)' : BRAND.muted;
    const ruleColor = dark ? 'rgba(255,255,255,0.22)' : BRAND.rule;
    const landscape = H / W < 0.8;
    const tall = H / W > 1.5;

    ctx.fillStyle = dark ? BRAND.ink : BRAND.paper;
    ctx.fillRect(0, 0, W, H);
    if (spec.photo && spec.photo.image && dark) {
        // Photo as a muted backdrop: cover, then ink wash so the quote stays readable.
        drawCover(ctx, spec.photo.image, 0, 0, W, H, 0.5, spec.photo.focalY ?? 0.5);
        ctx.fillStyle = rgba(BRAND.ink, 0.82 + 0.15 * (spec.photo.darken ?? 0.5));
        ctx.fillRect(0, 0, W, H);
    }
    ctx.fillStyle = BRAND.accent;
    ctx.fillRect(0, 0, 14 * u, H);

    const left = m + 14 * u;
    const maxWidth = W - left - m;
    const footerRuleY = H - m - 46 * u;

    // Opening quotation mark as a large accent ornament.
    ctx.fillStyle = rgba(BRAND.accent, dark ? 0.95 : 0.9);
    ctx.font = font(800, 220 * u, BRAND.serif);
    ctx.fillText('“', left - 10 * u, m + 150 * u);

    const attribution = spec.attribution ? String(spec.attribution).trim() : '';
    const bodyTop = m + 110 * u;
    const bodyBottom = footerRuleY - 64 * u - 28 * u - m * 0.8;
    const quote = fitText(ctx, spec.quote || spec.subtitle || spec.title, {
        family: BRAND.serif, weight: 700, maxWidth, maxLines: landscape ? 4 : tall ? 9 : 6,
        maxSize: (landscape ? 70 : 84) * u * (spec.headlineScale || 1), minSize: 36 * u, lineHeight: 1.42,
        maxHeight: bodyBottom - bodyTop - (attribution ? 68 * u : 0),
    });
    const qLH = quote.lineHeight;
    const blockH = quote.lines.length * qLH + (attribution ? 36 * u + 32 * u : 0);
    let y = bodyTop + Math.max(0, (bodyBottom - bodyTop - blockH) * 0.3);

    ctx.fillStyle = fg;
    ctx.font = font(700, quote.size, BRAND.serif);
    y = drawLines(ctx, quote.lines, left, y + quote.size * 0.85, qLH) - qLH + quote.size * 0.45;
    if (attribution) {
        y += 36 * u;
        ctx.fillStyle = BRAND.accent;
        ctx.font = font(600, 30 * u, BRAND.sans);
        ctx.fillText(`— ${attribution}`, left, y + 30 * u);
    }

    drawBrand(ctx, spec, left, footerRuleY - 64 * u - 28 * u, 64 * u, fg);
    drawFooter(ctx, spec, m, u, footerRuleY, mutedFg, ruleColor, spec.kicker || spec.date, spec.url || BRAND.url);
}

/**
 * Draw one card. `spec`:
 *   format {w,h} · template 'photo'|'text'|'quote' · theme 'dark'|'light'
 *   title · kicker (section) · subtitle · points[] · quote · attribution · date (already formatted)
 *   url · brandName · logo (Image|null) · showLogo · showSubtitle · showPoints
 *   breaking · breakingLabel · headlineScale (0.8–1.25)
 *   photo { image, focalY (0–1), darken (0–1) } | null
 */
export function renderCard(ctx, spec) {
    const { w: W, h: H } = spec.format;
    const u = Math.min(W, H * 1.25) / 1080;   // design unit: a 1080-wide card is scale 1
    const m = Math.round(64 * u);              // margin
    ctx.save();
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.clearRect(0, 0, W, H);
    ctx.textBaseline = 'alphabetic';
    ctx.textAlign = 'left';
    const draw = { photo: renderPhoto, text: renderText, quote: renderQuote }[spec.template] || renderPhoto;
    draw(ctx, spec, u, m);
    ctx.restore();
}
