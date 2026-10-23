<?php

use App\Controllers\AdminPhotoCards;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Facebook;

/**
 * The caption admin → Photo Cards sends with a card posted to the Facebook Page, and the
 * switch that keeps the button off until the Page is configured.
 *
 * @internal
 */
final class FacebookCaptionTest extends CIUnitTestCase
{
    public function testTheLinkFollowsTheMessageOnItsOwnLine(): void
    {
        $caption = AdminPhotoCards::facebookCaption("  ভয়ংকর অক্টোবর\n\n• প্রথম পয়েন্ট  ", 'https://www.barindpost.com/news/abc');

        $this->assertSame("ভয়ংকর অক্টোবর\n\n• প্রথম পয়েন্ট\n\nবিস্তারিত: https://www.barindpost.com/news/abc", $caption);
    }

    public function testAnEmptyMessageStillCarriesTheLink(): void
    {
        $this->assertSame('বিস্তারিত: https://www.barindpost.com/news/abc', AdminPhotoCards::facebookCaption(" \n ", 'https://www.barindpost.com/news/abc'));
    }

    public function testWindowsLineBreaksFromTheTextareaAreNormalised(): void
    {
        $this->assertStringNotContainsString("\r", AdminPhotoCards::facebookCaption("এক\r\nদুই", 'https://example.test/news/x'));
    }

    public function testPostingIsOffUntilBothPageIdAndTokenAreSet(): void
    {
        $fb = new Facebook();
        $fb->pageId    = '';
        $fb->pageToken = '';
        $this->assertFalse($fb->isConfigured());

        $fb->pageId = '1234567890';
        $this->assertFalse($fb->isConfigured());

        $fb->pageToken = 'token';
        $this->assertTrue($fb->isConfigured());
    }
}
