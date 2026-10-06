<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\SiteInfo;

/**
 * AdSense-readiness pieces that work without a database: the publisher
 * config that feeds footer/contact/about, and the menu filter's
 * fail-open behaviour.
 *
 * @internal
 */
final class SiteInfoTest extends CIUnitTestCase
{
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
