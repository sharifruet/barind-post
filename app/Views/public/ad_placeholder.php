<?php
/**
 * Ad Placeholder Component
 * Usage: include with parameters
 * <?php include __DIR__.'/ad_placeholder.php'; ?>
 * 
 * Parameters:
 * - $adType: 'banner', 'sidebar', 'inline', 'sticky'
 * - $adSize: 'small', 'medium', 'large', 'custom'
 * - $adPosition: 'top', 'bottom', 'left', 'right', 'center'
 * - $adText: Custom text for the placeholder
 */

$adType = $adType ?? 'banner';
$adSize = $adSize ?? 'medium';
$adPosition = $adPosition ?? 'center';
$adText = $adText ?? 'বিজ্ঞাপন দিন';

// Define size classes
$sizeClasses = [
    'small' => 'ad-banner',
    'medium' => 'ad-inline', 
    'large' => 'ad-sidebar',
    'custom' => ''
];

$adClass = $sizeClasses[$adSize] ?? 'ad-banner';

// Add sticky class if needed
if ($adType === 'sticky') {
    $adClass .= ' ad-sticky';
}

// Position classes
$positionClasses = [
    'top' => 'mt-0 mb-3',
    'bottom' => 'mt-3 mb-0',
    'left' => 'me-3',
    'right' => 'ms-3',
    'center' => 'mx-auto'
];

$positionClass = $positionClasses[$adPosition] ?? 'mx-auto';
?>

<div class="ad-placeholder <?= $adClass ?> <?= $positionClass ?>" style="<?= $adSize === 'custom' ? 'height: 100px;' : '' ?>">
    <div>
        <i class="fas fa-bullhorn me-2"></i>
        <strong><?= esc($adText) ?></strong>
        <br>
        <small class="text-muted"><?= strtoupper($adType) ?> - <?= strtoupper($adSize) ?></small>
    </div>
</div> 