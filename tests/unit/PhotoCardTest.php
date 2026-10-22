<?php

use App\Libraries\PhotoCard;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The GD share card (App\Libraries\PhotoCard) behind /og/{id}.png, and the helpers that
 * decide which image an article shares. Database-free; needs the gd extension (as CI has).
 *
 * @internal
 */
final class PhotoCardTest extends CIUnitTestCase
{
    private const FONTS = [
        'NotoSerifBengali-Bold.ttf', 'NotoSerifBengali-ExtraBold.ttf',
        'NotoSansBengali-Regular.ttf', 'NotoSansBengali-SemiBold.ttf', 'NotoSansBengali-Bold.ttf',
    ];

    public function testTheBanglaFontsShipWithTheSite(): void
    {
        foreach (self::FONTS as $font) {
            $this->assertNotNull(PhotoCard::fontPath($font), "{$font} is missing from public/assets/fonts");
        }
        $this->assertFileExists(ROOTPATH . 'public/assets/fonts/OFL.txt');
        $this->assertNull(PhotoCard::fontPath('NoSuchFont.ttf'));
    }

    public function testShapingDetectionAnswersWithoutThrowing(): void
    {
        // True only when libgd was built with libraqm; the bundled GD says no. Either way
        // the renderer must cope, which the render tests below cover.
        $this->assertIsBool(PhotoCard::canShapeBengali());
        $this->assertFalse(PhotoCard::canShapeBengali('/nonexistent/font.ttf'));
    }

    public function testAPhotolessArticleRendersAValidCard(): void
    {
        $png = (new PhotoCard())->render([
            'id'            => 1,
            'title'         => 'গোদাগাড়ীতে বাড়ছে ডেঙ্গু, গুরুতর রোগীদের পাঠানো হচ্ছে রাজশাহী মেডিকেলে',
            'category_name' => 'স্বাস্থ্য',
            'published_at'  => '2026-10-22 15:25:00',
            'image_url'     => null,
        ]);

        $this->assertPng($png);
    }

    public function testAMissingPhotoFileAndAnEmptyTitleAreTolerated(): void
    {
        $png = (new PhotoCard())->render([
            'id'           => 2,
            'title'        => '',
            'image_url'    => 'public/uploads/news/does-not-exist.webp',
            'published_at' => 'not a date',
        ]);

        $this->assertPng($png);
    }

    public function testBanglaDate(): void
    {
        $this->assertSame('২২ অক্টোবর ২০২৬', bangla_date('2026-10-22 15:25:00'));
        $this->assertSame('১ জানুয়ারি ২০২৫', bangla_date('2025-01-01'));
        $this->assertNull(bangla_date(null));
        $this->assertNull(bangla_date(''));
        $this->assertNull(bangla_date('nonsense'));
    }

    public function testShareImagePrefersThePhotoThenTheSavedCardThenTheGeneratedOne(): void
    {
        $this->assertSame(get_image_url('public/uploads/news/a.webp'), og_image_url(['id' => 5, 'image_url' => 'public/uploads/news/a.webp']));
        $this->assertSame(base_url('og/5.png'), og_image_url(['id' => 5, 'image_url' => null]));

        // A saved card changes the URL (cache-busting for Facebook's scraper).
        $id   = 999999990 + random_int(0, 9);
        $path = photo_card_share_path($id);
        try {
            file_put_contents($path, 'x');
            $this->assertSame(base_url("og/{$id}.png") . '?v=' . filemtime($path), og_image_url(['id' => $id]));
        } finally {
            @unlink($path);
        }
    }

    private function assertPng(string $png): void
    {
        $this->assertSame("\x89PNG\r\n\x1a\n", substr($png, 0, 8), 'not a PNG');
        $im = imagecreatefromstring($png);
        $this->assertNotFalse($im);
        $this->assertSame([PhotoCard::WIDTH, PhotoCard::HEIGHT], [imagesx($im), imagesy($im)]);
    }
}
