# News Widgets

This directory contains reusable news widgets for displaying individual news items in different formats.

## Available Widgets

### 1. single_news_widget.php
Displays a single news item with small photo on the left and title on the right (for list layouts).

**Usage:**
```php
<?php
// Basic usage
echo view('public/widgets/single_news_widget', ['news' => $yourNewsItem]);

// With custom parameters
echo view('public/widgets/single_news_widget', [
    'news' => $yourNewsItem,
    'showDate' => false,
    'showLead' => true,
    'imageSize' => 'custom-size'
]);
?>
```

### 2. single_news_card_widget.php
Displays a single news item as a card (for grid layouts).

**Usage:**
```php
<?php
// Basic usage
echo view('public/widgets/single_news_card_widget', ['news' => $yourNewsItem]);

// With custom parameters
echo view('public/widgets/single_news_card_widget', [
    'news' => $yourNewsItem,
    'imageHeight' => 150,
    'cardClass' => 'card border-danger',
    'showLead' => false
]);
?>
```

### 3. news_photo_list_widget.php
Displays multiple news items with small photos on the left and titles on the right.

**Usage:**
```php
<?php
// Basic usage
echo view('public/widgets/news_photo_list_widget', ['newsItems' => $yourNewsArray]);

// With custom parameters
echo view('public/widgets/news_photo_list_widget', [
    'newsItems' => $yourNewsArray,
    'title' => 'রাজনীতি সংবাদ',
    'maxItems' => 10
]);
?>
```

### 4. news_list_widget.php
Displays multiple news items in a simple list format with optional numbering.

**Usage:**
```php
<?php
// Basic usage
echo view('public/widgets/news_list_widget', ['newsItems' => $yourNewsArray]);

// With custom parameters
echo view('public/widgets/news_list_widget', [
    'newsItems' => $yourNewsArray,
    'title' => 'ব্রেকিং নিউজ',
    'showNumbers' => false
]);
?>
```

## News Item Structure

Each news item in the `$newsItems` array should have the following structure:

```php
$newsItem = [
    'slug' => 'news-slug',
    'title' => 'News Title',
    'image_url' => 'path/to/image.jpg',
    'image_alt_text' => 'Image description',
    'published_at' => '2024-01-01 12:00:00',
    'lead_text' => 'News lead text...'
];
```

## Examples

### Single News Widget in Loop (Right Column)
```php
<?php
foreach ($rightColumnNews as $news): 
    echo view('public/widgets/single_news_widget', ['news' => $news]);
endforeach;
?>
```

### Single News Card in Grid
```php
<?php
foreach ($latestNews as $news): 
    echo view('public/widgets/single_news_card_widget', ['news' => $news]);
endforeach;
?>
```

### Custom Single News Widget
```php
<?php
echo view('public/widgets/single_news_widget', [
    'news' => $featuredNews,
    'showDate' => false,
    'showLead' => true,
    'linkClass' => 'custom-link-class'
]);
?>
```

### Custom Single News Card
```php
<?php
echo view('public/widgets/single_news_card_widget', [
    'news' => $breakingNews,
    'cardClass' => 'card border-danger',
    'imageHeight' => 150,
    'showLead' => false
]);
?>
```

### Multiple News List Widget
```php
<?php
echo view('public/widgets/news_photo_list_widget', [
    'newsItems' => $latestNews,
    'title' => 'আরও সর্বশেষ সংবাদ',
    'maxItems' => 15
]);
?>
```
