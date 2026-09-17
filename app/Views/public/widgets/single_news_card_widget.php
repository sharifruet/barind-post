<?php
/**
 * Thin wrapper kept for older callers; delegates to the story card so there is
 * only one card implementation to maintain.
 *
 * @param array $news
 * @param bool  $showDate
 * @param bool  $showLead
 */
if (empty($news)) {
    return;
}

echo view('public/widgets/news_card_widget', [
    'news'       => $news,
    'size'       => $size ?? 'medium',
    'showKicker' => $showKicker ?? true,
    'showDate'   => $showDate ?? true,
    'showLead'   => $showLead ?? true,
]);
