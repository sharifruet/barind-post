<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * app/Helpers/text_helper.php — the summary/key-point/related-story helpers the
 * public article page, cards and SEO tags depend on.
 *
 * @internal
 */
final class TextHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('text_helper');
    }

    public function testKeyPointsSplitsLinesAndStripsBulletCharacters(): void
    {
        $this->assertSame(
            ['প্রথম তথ্য', 'দ্বিতীয় তথ্য', 'তৃতীয় তথ্য'],
            key_points("• প্রথম তথ্য\n- দ্বিতীয় তথ্য  \r\n\n  তৃতীয় তথ্য"),
        );
        $this->assertSame(['এক', 'দুই'], key_points("\r\n\r\nএক\r\n\r\nদুই\r\n"));
        $this->assertSame(['একটি বাক্যের লিড।'], key_points('একটি বাক্যের লিড।'));
        $this->assertSame([], key_points(null));
        $this->assertSame([], key_points("  \n  "));
    }

    public function testStoryExcerptPrefersSubtitleThenKeyPointsThenContent(): void
    {
        $base = ['content' => '<p>বডি <b>টেক্সট</b> এখানে</p>', 'title' => 'শিরোনাম'];

        $this->assertSame('সাবটাইটেল বাক্য', story_excerpt($base + ['subtitle' => 'সাবটাইটেল বাক্য', 'lead_text' => "ক\nখ"]));
        $this->assertSame('ক · খ · গ', story_excerpt($base + ['subtitle' => '', 'lead_text' => "ক\nখ\nগ"]));
        $this->assertSame('বডি টেক্সট এখানে', story_excerpt($base + ['subtitle' => null, 'lead_text' => null]));
        $this->assertSame('', story_excerpt(['subtitle' => null, 'lead_text' => '', 'content' => '']));
    }

    public function testExcerptTextTrimsToWordCountWithEllipsis(): void
    {
        $this->assertSame('one two three…', excerpt_text('one two three four five', 3));
        $this->assertSame('one two', excerpt_text('  one   two  ', 5));
        $this->assertSame('', excerpt_text('<p></p>'));
    }

    public function testSeoDescriptionFallbackOrder(): void
    {
        $this->assertSame('সাবটাইটেল', seo_description(['subtitle' => 'সাবটাইটেল', 'lead_text' => "ক\nখ", 'title' => 'ট'], ' - s'));
        $this->assertSame('ক। খ। গ', seo_description(['subtitle' => '', 'lead_text' => "ক\nখ\nগ", 'title' => 'ট'], ' - s'));
        $this->assertSame('শিরোনাম - s', seo_description(['subtitle' => null, 'lead_text' => null, 'title' => 'শিরোনাম'], ' - s'));
    }

    public function testRenderArticleBodyTurnsRelatedStoryBlockquoteIntoCallout(): void
    {
        $stored = '<p>Intro</p><blockquote><p><strong>আরও পড়ুন:</strong> <a href="/news/Ywbt0H4D8y">শিরোনাম &amp; আরও</a></p></blockquote><p>After</p>';
        $html   = render_article_body($stored);

        $this->assertStringContainsString('<aside class="related-inline">', $html);
        $this->assertStringContainsString('<a class="related-inline__link" href="/news/Ywbt0H4D8y">শিরোনাম &amp; আরও</a>', $html);
        $this->assertStringNotContainsString('<blockquote>', $html);
        $this->assertStringStartsWith('<p>Intro</p>', $html);
        $this->assertStringEndsWith('<p>After</p>', $html);
    }

    public function testRenderArticleBodyAcceptsEditorVariantsAndLeavesOtherBlockquotesAlone(): void
    {
        // &nbsp; and extra anchor attributes, as CKEditor may emit them
        $this->assertStringContainsString('related-inline__link', render_article_body('<blockquote><p><strong>আরও পড়ুন:</strong>&nbsp;<a href="/news/abc" target="_blank">Title</a></p></blockquote>'));
        // colon outside <strong>
        $this->assertStringContainsString('related-inline__link', render_article_body('<blockquote><p><strong>আরও পড়ুন</strong>: <a href="/news/abc">Title</a></p></blockquote>'));
        // an ordinary quote is untouched
        $plain = '<blockquote><p>একটি সাধারণ উদ্ধৃতি</p></blockquote>';
        $this->assertSame($plain, render_article_body($plain));
        $this->assertSame('', render_article_body(null));
    }

    public function testPlainTextBodiesBecomeParagraphs(): void
    {
        // Automation articles are plain text with blank lines; printed raw they collapsed
        // into one unbroken block on the page.
        $html = render_article_body("প্রথম অনুচ্ছেদ।\n\nদ্বিতীয় অনুচ্ছেদ।\n\nতৃতীয় অনুচ্ছেদ।");

        $this->assertSame(3, substr_count($html, '<p>'));
        $this->assertStringContainsString('<p>প্রথম অনুচ্ছেদ।</p>', $html);
    }

    public function testEditorHtmlIsLeftAlone(): void
    {
        $html = '<p>একটি অনুচ্ছেদ</p><p>আরেকটি</p>';

        $this->assertSame($html, render_article_body($html));
    }

    public function testRelatedStoryBlockStillWorksInsideEditorHtml(): void
    {
        $stored = '<p>Intro</p><blockquote><p><strong>আরও পড়ুন:</strong> <a href="/news/abc">শিরোনাম</a></p></blockquote>';

        $this->assertStringContainsString('related-inline__link', render_article_body($stored));
    }

    public function testBnNumber(): void
    {
        $this->assertSame('২০২৬-০৯-১৭', bn_number('2026-09-17'));
        $this->assertSame('১০', bn_number(10));
    }
}
