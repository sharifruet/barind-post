/**
 * Admin → Photo Cards page: wires the controls to the renderer (photo-card.js),
 * keeps a live preview on the canvas, and exports or saves the finished card.
 * Expects a <script type="application/json" id="pc-config"> with
 * { articles: {items, page, pages, total, url}, brand: {name, url, handle, logo}, saveUrl,
 *   facebook: {url, configured, page} } and the fonts
 * CardSerif / CardSans declared with @font-face on the page.
 */
import { renderCard, FORMATS, TEMPLATES, banglaDate } from './photo-card.js';

const cfg = JSON.parse(document.getElementById('pc-config').textContent);
const $ = (id) => document.getElementById(id);

const canvas = $('pcCanvas');
const ctx = canvas.getContext('2d');

const state = {
    articleId: null, template: 'photo', theme: 'dark', format: 'link',
    title: '', kicker: '', subtitle: '', points: [], quote: '', attribution: '', date: '',
    photoSource: 'none', focalY: 0.5, darken: 0.5,
    breaking: false, showLogo: true, showSubtitle: true, showPoints: true, headlineScale: 1,
};
let article = null;          // the picked article (an item from the picker page)
let listing = cfg.articles;  // the picker page on screen: {items, page, pages, total, url}
let logo = null;
let articlePhoto = null;
let uploadPhoto = null;
let photoMessage = '';

const loadImage = (src) => new Promise((resolve, reject) => {
    const img = new Image();
    img.onload = () => resolve(img);
    img.onerror = () => reject(new Error(`Could not load ${src}`));
    img.src = src;
});

async function loadFonts() {
    if (!document.fonts) return;
    const faces = ['700 24px CardSerif', '800 24px CardSerif', '400 24px CardSans', '600 24px CardSans', '700 24px CardSans'];
    await Promise.all(faces.map((f) => document.fonts.load(f).catch(() => null)));
}

/* ---------------------------------------------------------------- preview */

function spec() {
    const photo = state.photoSource === 'upload' ? uploadPhoto : state.photoSource === 'article' ? articlePhoto : null;
    return {
        format: FORMATS[state.format], template: state.template, theme: state.theme,
        title: state.title, kicker: state.kicker, subtitle: state.subtitle, points: state.points,
        quote: state.quote, attribution: state.attribution, date: state.date,
        url: cfg.brand.url, handle: cfg.brand.handle, brandName: cfg.brand.name, logo,
        showLogo: state.showLogo, showSubtitle: state.showSubtitle, showPoints: state.showPoints,
        breaking: state.breaking, headlineScale: state.headlineScale,
        photo: photo ? { image: photo, focalY: state.focalY, darken: state.darken } : null,
    };
}

let frame = 0;
function draw() {
    cancelAnimationFrame(frame);
    frame = requestAnimationFrame(() => {
        const s = spec();
        if (canvas.width !== s.format.w || canvas.height !== s.format.h) {
            canvas.width = s.format.w;
            canvas.height = s.format.h;
        }
        renderCard(ctx, s);
        $('pcFormatNote').textContent = `${s.format.w} × ${s.format.h}`;
    });
}

function note(text, isError = false) {
    const el = $('pcNote');
    el.textContent = text;
    el.className = `pc__note${isError ? ' is-error' : ''}`;
    el.hidden = !text;
}

/* ---------------------------------------------------------------- controls */

function select(group, value) {
    document.querySelectorAll(`[data-group="${group}"]`).forEach((el) => {
        el.classList.toggle('is-selected', el.dataset.value === value);
        el.setAttribute('aria-pressed', el.dataset.value === value ? 'true' : 'false');
    });
}

function showFor() {
    document.querySelectorAll('[data-for]').forEach((el) => {
        el.hidden = !el.dataset.for.split(',').includes(state.template);
    });
    $('pcThemeRow').hidden = state.template === 'photo';
    $('pcPhotoNote').textContent = photoMessage;
}

function readFields() {
    state.title = $('pcTitle').value.trim();
    state.kicker = $('pcKicker').value.trim();
    state.subtitle = $('pcSubtitle').value.trim();
    state.points = $('pcPoints').value.split('\n').map((p) => p.trim()).filter(Boolean);
    state.quote = $('pcQuote').value.trim();
    state.attribution = $('pcAttribution').value.trim();
    state.showSubtitle = $('pcShowSubtitle').checked;
    state.showPoints = $('pcShowPoints').checked;
    state.breaking = $('pcBreaking').checked;
    state.showLogo = $('pcShowLogo').checked;
    state.headlineScale = Number($('pcHeadlineScale').value) / 100;
    state.focalY = Number($('pcFocal').value) / 100;
    state.darken = Number($('pcDarken').value) / 100;
    draw();
}

async function useArticle(a) {
    article = a || null;
    state.articleId = a ? a.id : null;
    markSelected();
    $('pcTitle').value = a ? a.title : '';
    $('pcKicker').value = a ? a.category : '';
    $('pcSubtitle').value = a ? a.subtitle : '';
    $('pcPoints').value = a ? a.points.join('\n') : '';
    $('pcQuote').value = a ? (a.subtitle || a.points[0] || '') : '';
    $('pcAttribution').value = '';
    state.date = a ? banglaDate(a.date) : banglaDate(new Date());
    $('pcShareImage').disabled = !a;
    $('pcShareImage').checked = false;
    $('pcArticleLink').hidden = !a;
    if (a) $('pcArticleLink').href = a.url;
    $('pcFbMessage').value = a ? facebookMessage(a) : '';
    $('pcFbLink').textContent = a ? a.url : '—';
    $('pcFbResult').hidden = true;
    $('pcFbPost').disabled = !cfg.facebook.configured || !a || a.status !== 'published';
    $('pcFbPost').title = a && a.status !== 'published' ? 'Publish the article first' : '';
    $('pcFbShare').disabled = !a || a.status !== 'published';
    $('pcFbShare').title = $('pcFbPost').title;

    articlePhoto = null;
    photoMessage = '';
    if (a && a.image) {
        try {
            const src = new URL(a.image, location.origin);
            if (src.origin !== location.origin) throw new Error('hosted on another site');
            articlePhoto = await loadImage(src.href);
            state.photoSource = 'article';
        } catch (e) {
            photoMessage = `The article photo could not be used (${e.message}).`;
            state.photoSource = uploadPhoto ? 'upload' : 'none';
        }
    } else {
        photoMessage = a ? 'This article has no photo.' : '';
        state.photoSource = uploadPhoto ? 'upload' : 'none';
    }
    $('pcPhotoArticle').disabled = !articlePhoto;
    select('photo', state.photoSource);
    showFor();
    readFields();
}

/* ---------------------------------------------------------------- article picker */

function markSelected() {
    for (const el of $('pcArticles').querySelectorAll('.pc__item')) {
        const on = Number(el.dataset.id) === state.articleId;
        el.classList.toggle('is-selected', on);
        el.setAttribute('aria-selected', on ? 'true' : 'false');
    }
}

function renderArticles() {
    const list = $('pcArticles');
    list.replaceChildren();
    for (const a of listing.items) {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'pc__item';
        b.dataset.id = a.id;
        b.setAttribute('role', 'option');
        b.innerHTML = '<span class="pc__item-title"></span><span class="pc__item-meta"></span>';
        b.querySelector('.pc__item-title').textContent = a.title;
        const meta = b.querySelector('.pc__item-meta');
        if (a.status === 'draft') {
            const d = document.createElement('span');
            d.className = 'pc__item-draft';
            d.textContent = 'Draft · ';
            meta.appendChild(d);
        }
        meta.append([banglaDate(a.date), a.category].filter(Boolean).join(' · '));
        b.addEventListener('click', () => useArticle(a));
        list.appendChild(b);
    }
    if (!listing.items.length) {
        list.innerHTML = '<p class="pc__empty mb-0">No articles yet.</p>';
    }
    $('pcArticleCount').textContent = listing.total ? `· ${listing.total} total` : '';
    markSelected();
    renderPager();
}

// Page numbers: first, last, and two either side of the current page, with gaps as "…".
function renderPager() {
    const nav = $('pcPager');
    nav.replaceChildren();
    const { page, pages } = listing;
    nav.hidden = pages <= 1;
    const button = (label, target, opts = {}) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.textContent = label;
        b.disabled = !!opts.disabled;
        if (opts.current) {
            b.className = 'is-current';
            b.setAttribute('aria-current', 'page');
        }
        if (opts.title) b.title = opts.title;
        b.addEventListener('click', () => loadPage(target));
        nav.appendChild(b);
    };
    button('‹', page - 1, { disabled: page <= 1, title: 'Newer' });
    let last = 0;
    for (let p = 1; p <= pages; p++) {
        if (p !== 1 && p !== pages && Math.abs(p - page) > 2) continue;
        if (p - last > 1) nav.appendChild(Object.assign(document.createElement('span'), { textContent: '…' }));
        button(String(p), p, { current: p === page });
        last = p;
    }
    button('›', page + 1, { disabled: page >= pages, title: 'Older' });
}

async function loadPage(n) {
    const list = $('pcArticles');
    list.classList.add('is-loading');
    try {
        const res = await fetch(`${listing.url}?page=${encodeURIComponent(n)}`, { headers: { Accept: 'application/json' } });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.error || `HTTP ${res.status}`);
        listing = { ...data, url: listing.url };
        renderArticles();
    } catch (e) {
        note(`Could not load that page of articles: ${e.message}`, true);
    } finally {
        list.classList.remove('is-loading');
    }
}

/* ---------------------------------------------------------------- export */

const toBlob = (type, quality) => new Promise((resolve, reject) => {
    try {
        canvas.toBlob((b) => (b ? resolve(b) : reject(new Error('The browser could not export the canvas.'))), type, quality);
    } catch (e) {
        reject(e);
    }
});

const filename = (ext) => `barindpost-${state.articleId || 'card'}-${state.template}-${state.format}.${ext}`;

async function download(type, ext, quality) {
    try {
        const blob = await toBlob(type, quality);
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = filename(ext);
        a.click();
        setTimeout(() => URL.revokeObjectURL(a.href), 5000);
    } catch (e) {
        note(`Download failed: ${e.message}`, true);
    }
}

async function save() {
    const btn = $('pcSave');
    btn.disabled = true;
    note('Saving…');
    try {
        const blob = await toBlob('image/png');
        const body = new FormData();
        body.append('image', blob, filename('png'));
        body.append('news_id', state.articleId || 0);
        body.append('format', state.format);
        body.append('template', state.template);
        body.append('share_image', $('pcShareImage').checked && state.articleId ? '1' : '0');
        const res = await fetch(cfg.saveUrl, { method: 'POST', body, headers: { Accept: 'application/json' } });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.error || `HTTP ${res.status}`);
        $('pcResultUrl').href = data.url;
        $('pcResultUrl').textContent = data.url;
        $('pcResult').hidden = false;
        $('pcShareDone').hidden = !data.share_image_url;
        note(data.share_image_url
            ? 'Saved. It is now this article\'s share image — if the link was shared before, ask Facebook to re-scrape it.'
            : 'Saved.');
    } catch (e) {
        note(`Save failed: ${e.message}`, true);
    } finally {
        btn.disabled = false;
    }
}

/* ---------------------------------------------------------------- facebook */

// Standfirst, then the key points (news.lead_text) as bullets. The server appends the link.
const facebookMessage = (a) => [a.subtitle, a.points.map((p) => `• ${p}`).join('\n')].filter(Boolean).join('\n\n');

async function postToFacebook() {
    const a = article;
    if (!a) return;
    if (!confirm('Post this card to the Barind Post Facebook page now? It will be public immediately.')) return;
    const btn = $('pcFbPost');
    btn.disabled = true;
    note('Posting to Facebook…');
    try {
        const blob = await toBlob('image/png');
        const body = new FormData();
        body.append('image', blob, filename('png'));
        body.append('news_id', a.id);
        body.append('message', $('pcFbMessage').value);
        const res = await fetch(cfg.facebook.url, { method: 'POST', body, headers: { Accept: 'application/json' } });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.error || `HTTP ${res.status}`);
        $('pcFbResult').href = data.post_url;
        $('pcFbResult').hidden = false;
        note('Posted to Facebook.');
    } catch (e) {
        note(`Facebook post failed: ${e.message}`, true);
    } finally {
        btn.disabled = false;
    }
}

// Facebook's Share dialog lets the editor pick their own timeline, a group or a Page they
// manage — the only way to reach those, since the Graph API cannot post to profiles or
// groups. It shares a link, so the card becomes the article's og:image first (the server
// also asks Facebook to re-scrape), and the message goes to the clipboard to paste.
async function shareViaDialog() {
    const a = article;
    if (!a) return;
    // Opened now, inside the click, so the popup blocker allows it; pointed at Facebook below.
    const popup = window.open('', 'barindpost-fb-share', 'width=640,height=640');
    const btn = $('pcFbShare');
    btn.disabled = true;
    note('Saving the card as the share image…');
    try {
        const blob = await toBlob('image/png');
        const body = new FormData();
        body.append('image', blob, filename('png'));
        body.append('news_id', a.id);
        body.append('format', state.format);
        body.append('template', state.template);
        body.append('share_image', '1');
        const res = await fetch(cfg.saveUrl, { method: 'POST', body, headers: { Accept: 'application/json' } });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.error || `HTTP ${res.status}`);
        $('pcShareImage').checked = true;

        let copied = true;
        try {
            await navigator.clipboard.writeText(`${$('pcFbMessage').value.trim()}\n\nবিস্তারিত: ${a.url}`.trim());
        } catch {
            copied = false;
        }

        const share = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(a.url)}`;
        if (popup && !popup.closed) {
            popup.opener = null;   // Facebook's page gets no handle on the admin page
            popup.location.href = share;
        }
        else window.open(share, '_blank', 'noopener');

        note([
            copied ? 'Message copied — paste it into the Share dialog.' : 'Could not copy the message; copy it from the box above.',
            data.rescraped ? '' : 'If the preview still shows an old picture, re-scrape the link in Facebook\'s Sharing Debugger.',
        ].filter(Boolean).join(' '));
    } catch (e) {
        if (popup && !popup.closed) popup.close();
        note(`Share failed: ${e.message}`, true);
    } finally {
        btn.disabled = false;
    }
}

/* ---------------------------------------------------------------- boot */

async function boot() {
    renderArticles();

    for (const [key, f] of Object.entries(FORMATS)) {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'pc__pill';
        b.dataset.group = 'format';
        b.dataset.value = key;
        b.title = f.note;
        b.textContent = f.label;
        $('pcFormats').appendChild(b);
    }
    for (const [key, t] of Object.entries(TEMPLATES)) {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'pc__choice';
        b.dataset.group = 'template';
        b.dataset.value = key;
        b.innerHTML = `<strong></strong><small></small>`;
        b.querySelector('strong').textContent = t.label;
        b.querySelector('small').textContent = t.note;
        $('pcTemplates').appendChild(b);
    }

    document.addEventListener('click', (ev) => {
        const el = ev.target.closest('[data-group]');
        if (!el || el.disabled) return;
        const { group, value } = el.dataset;
        if (group === 'template') state.template = value;
        if (group === 'theme') state.theme = value;
        if (group === 'format') state.format = value;
        if (group === 'photo') state.photoSource = value;
        select(group, value);
        showFor();
        draw();
    });
    select('template', state.template);
    select('theme', state.theme);
    select('format', state.format);
    select('photo', state.photoSource);

    for (const id of ['pcTitle', 'pcKicker', 'pcSubtitle', 'pcPoints', 'pcQuote', 'pcAttribution', 'pcHeadlineScale', 'pcFocal', 'pcDarken']) {
        $(id).addEventListener('input', readFields);
    }
    for (const id of ['pcShowSubtitle', 'pcShowPoints', 'pcBreaking', 'pcShowLogo']) {
        $(id).addEventListener('change', readFields);
    }
    $('pcReset').addEventListener('click', () => useArticle(article));
    $('pcUpload').addEventListener('change', async (ev) => {
        const file = ev.target.files && ev.target.files[0];
        if (!file) return;
        try {
            uploadPhoto = await loadImage(URL.createObjectURL(file));
            state.photoSource = 'upload';
            $('pcPhotoUpload').disabled = false;
            select('photo', 'upload');
            draw();
        } catch (e) {
            note(`Could not read that image: ${e.message}`, true);
        }
    });
    $('pcDownloadPng').addEventListener('click', () => download('image/png', 'png'));
    $('pcDownloadJpg').addEventListener('click', () => download('image/jpeg', 'jpg', 0.92));
    $('pcSave').addEventListener('click', save);
    $('pcFbPost').addEventListener('click', postToFacebook);
    $('pcFbShare').addEventListener('click', shareViaDialog);
    $('pcCopyUrl').addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText($('pcResultUrl').href);
            note('URL copied.');
        } catch {
            note('Copy failed — select the URL and copy it by hand.', true);
        }
    });

    try {
        logo = await loadImage(cfg.brand.logo);
    } catch {
        logo = null;
    }
    await loadFonts();
    if (listing.items.length) {
        await useArticle(listing.items[0]);
    } else {
        readFields();
    }
    showFor();
    draw();
}

boot().catch((e) => note(`The card tool could not start: ${e.message}`, true));
