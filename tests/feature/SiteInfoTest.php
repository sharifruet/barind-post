<?php

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\SiteInfo;

/**
 * AdSense-readiness pieces that work without a database: ads.txt, the
 * publisher config that feeds footer/contact/about, and the menu filter's
 * fail-open behaviour.
 *
 * @internal
 */
final class SiteInfoTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private mixed $savedAdsenseId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->savedAdsenseId = $_ENV['GOOGLE_ADSENSE_ID'] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->savedAdsenseId === null) {
            unset($_ENV['GOOGLE_ADSENSE_ID']);
        } else {
            $_ENV['GOOGLE_ADSENSE_ID'] = $this->savedAdsenseId;
        }
        parent::tearDown();
    }

    public function testAdsTxtIsBuiltFromTheAdsenseClientId(): void
    {
        $_ENV['GOOGLE_ADSENSE_ID'] = 'ca-pub-1234567890123456';

        $result = $this->get('ads.txt');

        $result->assertStatus(200);
        $this->assertSame("google.com, pub-1234567890123456, DIRECT, f08c47fec0942fa0\n", $result->response()->getBody());
    }

    public function testAdsTxtIs404UntilConfigured(): void
    {
        unset($_ENV['GOOGLE_ADSENSE_ID']);
        putenv('GOOGLE_ADSENSE_ID');

        $this->expectException(PageNotFoundException::class);
        $this->get('ads.txt');
    }

    public function testUnsetSocialProfilesAreNotRendered(): void
    {
        $publisher            = new SiteInfo();
        $publisher->facebook  = 'https://facebook.com/barindpost';
        $publisher->youtube   = '';
        $publisher->x         = '';
        $publisher->instagram = 'https://instagram.com/barindpost';

        $this->assertSame(['Facebook', 'Instagram'], array_column($publisher->socialLinks(), 'label'));
    }

    public function testMenuKeepsEveryCategoryWhenCountsAreUnavailable(): void
    {
        // The test DB has no schema, so the count query fails; the menu must not vanish.
        $categories = [['id' => 1, 'slug' => 'a'], ['id' => 2, 'slug' => 'b']];

        $this->assertSame($categories, menu_categories($categories));
    }
}
