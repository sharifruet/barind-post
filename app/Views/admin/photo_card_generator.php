<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<style>
    <?php foreach ($fonts as $file => $url): ?>
    @font-face {
        font-family: '<?= str_starts_with($file, 'NotoSerif') ? 'CardSerif' : 'CardSans' ?>';
        font-weight: <?= ['Regular' => 400, 'SemiBold' => 600, 'Bold' => 700, 'ExtraBold' => 800][preg_replace('/^.*-(\w+)\.ttf$/', '$1', $file)] ?>;
        font-display: block;
        src: url('<?= str_replace(["'", '\\', "\n", "\r"], '', $url) /* asset_url() output, not user input */ ?>') format('truetype');
    }
    <?php endforeach; ?>
</style>

<div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-image me-2"></i>Photo Cards</h1>
        <p class="text-muted mb-0">Social cards for Facebook, Instagram and WhatsApp, drawn from the article. Bangla is shaped by your browser, so what you see is what you get.</p>
    </div>
</div>

<div class="pc">
    <div>
        <section class="pc__panel">
            <h2>Article</h2>
            <input type="search" class="form-control form-control-sm mb-2 bengali-input" id="pcSearch" placeholder="Search headlines…" autocomplete="off">
            <select class="form-select bengali-input" id="pcArticle" size="6"></select>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <a id="pcArticleLink" class="small" href="#" target="_blank" rel="noopener" hidden>Open article <i class="fas fa-external-link-alt fa-xs"></i></a>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="pcReset"><i class="fas fa-undo me-1"></i>Reset text</button>
            </div>
        </section>

        <section class="pc__panel">
            <h2>Template</h2>
            <div class="pc__choices" id="pcTemplates"></div>
            <div class="pc__pills mt-2" id="pcThemeRow">
                <button type="button" class="pc__pill" data-group="theme" data-value="dark">Dark</button>
                <button type="button" class="pc__pill" data-group="theme" data-value="light">Light</button>
            </div>
            <h2 class="mt-3">Format</h2>
            <div class="pc__pills" id="pcFormats"></div>
        </section>

        <section class="pc__panel">
            <h2>Text</h2>
            <label class="form-label small mb-1" for="pcTitle">Headline</label>
            <textarea class="form-control bengali-input" id="pcTitle" rows="3"></textarea>
            <label class="form-label small mb-1 mt-2" for="pcKicker">Section / kicker</label>
            <input type="text" class="form-control bengali-input" id="pcKicker">
            <div data-for="photo,text">
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <label class="form-label small mb-1" for="pcSubtitle">Standfirst</label>
                    <label class="form-check form-check-inline small mb-1"><input class="form-check-input" type="checkbox" id="pcShowSubtitle" checked> show</label>
                </div>
                <textarea class="form-control bengali-input" id="pcSubtitle" rows="2"></textarea>
            </div>
            <div data-for="text">
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <label class="form-label small mb-1" for="pcPoints">Key points <span class="text-muted">(one per line)</span></label>
                    <label class="form-check form-check-inline small mb-1"><input class="form-check-input" type="checkbox" id="pcShowPoints" checked> show</label>
                </div>
                <textarea class="form-control bengali-input" id="pcPoints" rows="4"></textarea>
            </div>
            <div data-for="quote">
                <label class="form-label small mb-1 mt-2" for="pcQuote">Quote</label>
                <textarea class="form-control bengali-input" id="pcQuote" rows="3"></textarea>
                <label class="form-label small mb-1 mt-2" for="pcAttribution">Attribution <span class="text-muted">(name, title)</span></label>
                <input type="text" class="form-control bengali-input" id="pcAttribution">
            </div>
            <label class="form-label small mb-1 mt-3" for="pcHeadlineScale">Headline size</label>
            <input type="range" class="form-range" id="pcHeadlineScale" min="80" max="125" value="100">
        </section>

        <section class="pc__panel" data-for="photo,quote">
            <h2>Photo</h2>
            <div class="pc__pills">
                <button type="button" class="pc__pill" data-group="photo" data-value="article" id="pcPhotoArticle" disabled>Article photo</button>
                <button type="button" class="pc__pill" data-group="photo" data-value="upload" id="pcPhotoUpload" disabled>Uploaded</button>
                <button type="button" class="pc__pill" data-group="photo" data-value="none">None</button>
            </div>
            <input type="file" class="form-control form-control-sm mt-2" id="pcUpload" accept="image/*">
            <p class="small text-muted mt-1 mb-0" id="pcPhotoNote"></p>
            <label class="form-label small mb-1 mt-2" for="pcFocal">Crop position (top ↔ bottom)</label>
            <input type="range" class="form-range" id="pcFocal" min="0" max="100" value="50">
            <label class="form-label small mb-1" for="pcDarken">Darken for legibility</label>
            <input type="range" class="form-range" id="pcDarken" min="0" max="100" value="50">
        </section>

        <section class="pc__panel">
            <h2>Options</h2>
            <label class="form-check small"><input class="form-check-input" type="checkbox" id="pcBreaking"> Breaking-news band</label>
            <label class="form-check small"><input class="form-check-input" type="checkbox" id="pcShowLogo" checked> Show logo</label>
        </section>
    </div>

    <div>
        <div class="pc__stage">
            <canvas id="pcCanvas" width="1200" height="630" aria-label="Card preview"></canvas>
        </div>
        <div class="pc__actions">
            <span class="small text-muted me-auto" id="pcFormatNote"></span>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="pcDownloadPng"><i class="fas fa-download me-1"></i>PNG</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="pcDownloadJpg"><i class="fas fa-download me-1"></i>JPG</button>
            <label class="form-check form-check-inline small mb-0 ms-2"><input class="form-check-input" type="checkbox" id="pcShareImage" disabled> Use as this article's share image</label>
            <button type="button" class="btn btn-sm btn-accent" id="pcSave"><i class="fas fa-cloud-upload-alt me-1"></i>Save to site</button>
        </div>
        <p class="pc__note" id="pcNote" hidden></p>
        <div class="pc__result" id="pcResult" hidden>
            <i class="fas fa-link me-1"></i><a id="pcResultUrl" href="#" target="_blank" rel="noopener"></a>
            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="pcCopyUrl"><i class="fas fa-copy me-1"></i>Copy URL</button>
            <span class="badge text-bg-success ms-2" id="pcShareDone" hidden>share image</span>
        </div>
    </div>
</div>

<script type="application/json" id="pc-config"><?= json_encode(['articles' => $articles, 'brand' => $brand, 'saveUrl' => $saveUrl], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script type="module" src="<?= asset_url('assets/js/photo-card-admin.js') ?>"></script>

<?= $this->endSection() ?>
