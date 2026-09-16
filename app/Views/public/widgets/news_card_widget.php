<?php
/**
 * News Card Widget
 * Reusable widget for displaying news cards with consistent styling
 * 
 * @param array $news - News item data
 * @param string $size - Card size: 'hero', 'large', 'medium', 'small'
 * @param bool $showKicker - Whether to show kicker
 * @param bool $showDate - Whether to show publication date
 * @param bool $showLead - Whether to show lead text
 */

// Set default values
$size = $size ?? 'medium';
$showKicker = $showKicker ?? true;
$showDate = $showDate ?? true;
$showLead = $showLead ?? true;
?>

<div class="news-card-widget news-card-<?= $size ?>">
    <div class="card news-card h-100 border-0 shadow-sm position-relative">
        <?php if (!empty($news['image_url'])): ?>
            <img src="<?= esc(get_image_url($news['image_url'])) ?>" 
                 class="card-img-top news-card-img" 
                 style="width: 100%; aspect-ratio: 16/9; object-fit: cover;"
                 alt="<?= esc($news['image_alt_text'] ?? '') ?>">
        <?php endif; ?>
        
        <div class="card-body">
            
            
            
            <?php if ($size === 'hero'): ?>
                <!-- Hero layout: kicker and title on different lines -->
                <div class="title-box <?=(!empty($news['kicker']))?'kicker-title-box':''?>">
                    <?php if ($showKicker && !empty($news['kicker'])): ?>
                        <div class="kicker" style="<?= get_kicker_style($news) ?>"><?= esc($news['kicker']) ?></div>
                    <?php endif; ?>
                    <h5 class="card-title news-card-title" title="<?= esc($news['title'], 'attr') ?>">
                        <a href="/news/<?= esc($news['slug']) ?>" class="text-decoration-none fw-semibold" title="<?= esc($news['title'], 'attr') ?>">
                            <?= esc($news['title'], 'raw') ?>
                        </a>
                    </h5>
                </div>
            <?php else: ?>
                <!-- Regular layout: kicker and title on same line -->
                <div class="title-box">
                    <?php if ($showKicker && !empty($news['kicker'])): ?>
                        <div class="kicker-title-inline">
                            <span class="kicker" style="<?= get_kicker_style($news) ?>"><?= esc($news['kicker']) ?></span>
                            <h5 class="card-title news-card-title d-inline" title="<?= esc($news['title'], 'attr') ?>">
                                <a href="/news/<?= esc($news['slug']) ?>" class="text-decoration-none fw-semibold" title="<?= esc($news['title'], 'attr') ?>">
                                    <?= esc($news['title'], 'raw') ?>
                                </a>
                            </h5>
                        </div>
                    <?php else: ?>
                        <h5 class="card-title news-card-title" title="<?= esc($news['title'], 'attr') ?>">
                            <a href="/news/<?= esc($news['slug']) ?>" class="text-decoration-none fw-semibold" title="<?= esc($news['title'], 'attr') ?>">
                                <?= esc($news['title'], 'raw') ?>
                            </a>
                        </h5>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        
         
            
            <?php if ($showDate): ?>
                <p class="card-text small text-muted mb-1">
                    <?= date('M d, Y', strtotime($news['published_at'])) ?>
                </p>
            <?php endif; ?>
            
			<?php if ($showLead): ?>
				<p class="card-text news-card-lead">
					<?php 
					$text = '';
					if (!empty($news['lead_text'])) {
						$text = $news['lead_text'];
					} elseif (!empty($news['content'])) {
						$text = $news['content'];
					}
					// Strip HTML and normalize whitespace for a clean excerpt
					$text = strip_tags($text);
					$text = preg_replace('/\s+/', ' ', $text);
					echo esc($text);
					?>
				</p>
			<?php endif; ?>
        </div>
    </div>
</div>

<style>
/* News Card Widget Styles */
.news-card-widget .news-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 0.75rem;
    overflow: hidden;
}

.news-card-widget .news-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

/* Title box spacing */
.title-box {
    margin-bottom: 0.5rem;
}

/* Kicker badge/title box */
.kicker-title-box {
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    padding: 0.5rem 0.75rem;
    margin-bottom: 0.5rem;
    border-left: 3px solid currentColor;
}

.kicker-title-box .kicker {
    display: inline-block;
    font-weight: 800;
    font-size: 0.78rem !important;
    padding: 0.15rem 0.5rem !important;
    border-radius: 0.35rem;
    letter-spacing: 0.6px !important;
    margin: 0 0.5rem 0.35rem 0 !important;
    text-transform: uppercase !important;
}

.kicker-title-box .news-card-title {
    margin: 0;
}

/* Inline kicker and title layout */
.kicker-title-inline {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.kicker-title-inline .kicker {
    flex-shrink: 0;
    margin-bottom: 0 !important;
}

.kicker-title-inline .news-card-title {
    margin: 0;
    flex: 1;
    min-width: 0; /* Allow title to shrink if needed */
}

.kicker-title-inline .news-card-title a {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Card title sizing */
.news-card-hero .card-title { font-size: 1.5rem; font-weight: 700; }
.news-card-large .card-title { font-size: 1.25rem; font-weight: 600; }
.news-card-medium .card-title { font-size: 1.1rem; font-weight: 600; }
.news-card-small .card-title { font-size: 1rem; font-weight: 600; }

/* Title and lead text line limits */
.news-card-title {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
}

.news-card-lead {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.4;
}

/* Kicker sizing */
.news-card-hero .kicker { font-size: 1.1rem !important; margin-bottom: 0.75rem !important; }
.news-card-large .kicker { font-size: 0.95rem !important; margin-bottom: 0.6rem !important; }
.news-card-medium .kicker { font-size: 0.9rem !important; margin-bottom: 0.5rem !important; }
.news-card-small .kicker { font-size: 0.8rem !important; margin-bottom: 0.4rem !important; }

/* Responsive adjustments */
@media (max-width: 768px) {
    .news-card-hero .card-title {
        font-size: 1.25rem;
    }
    
    .news-card-large .card-title { font-size: 1.1rem; }
    .news-card-medium .card-title { font-size: 1rem; }
    
    /* Images now use aspect-ratio: 16/9 for consistent sizing */
}

@media (max-width: 576px) {
    .news-card-hero .card-title {
        font-size: 1.1rem;
    }
    
    .news-card-large .card-title { font-size: 1rem; }
    .news-card-medium .card-title { font-size: 0.95rem; }
    .news-card-small .card-title { font-size: 0.9rem; }
    
    /* Images now use aspect-ratio: 16/9 for consistent sizing */
}
</style>
