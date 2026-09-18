<?php

use App\Controllers\Api\NewsController;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The gates that decide whether the pipeline may publish an article itself.
 *
 * Every rule here exists because the failure it guards against actually happened in this
 * pipeline: articles generated from a headline with no body, a wholly invented article
 * produced from a failed page fetch, and the same event arriving from two outlets.
 * Failing a gate is not an error — the article is still created, as a draft.
 *
 * @internal
 */
final class PublishGateTest extends CIUnitTestCase
{
    /**
     * @return list<string>
     */
    private function gates(array $data, int $wordCount, ?array $similar, bool $autoPublish = true, int $minWords = 120): array
    {
        $automation                     = new \Config\Automation();
        $automation->autoPublish        = $autoPublish;
        $automation->autoPublishMinWords = $minWords;

        $method = (new ReflectionClass(NewsController::class))->getMethod('publishGateFailures');
        $method->setAccessible(true);

        return $method->invoke(new NewsController(), $data, $wordCount, $similar, $automation);
    }

    private function goodArticle(): array
    {
        return [
            'subtitle'   => 'একটি সম্পূর্ণ স্ট্যান্ডফার্স্ট বাক্য।',
            'lead_text'  => "প্রথম মূল তথ্য\nদ্বিতীয় মূল তথ্য",
            'source_url' => 'https://example.test/a/1',
        ];
    }

    public function testACompleteArticlePassesEveryGate(): void
    {
        $this->assertSame([], $this->gates($this->goodArticle(), 250, null));
    }

    public function testNothingPublishesWhileTheServerSwitchIsOff(): void
    {
        $failures = $this->gates($this->goodArticle(), 250, null, false);

        $this->assertCount(1, $failures);
        $this->assertStringContainsString('automation.autoPublish', $failures[0]);
    }

    public function testShortBodiesStayDrafts(): void
    {
        $failures = $this->gates($this->goodArticle(), 95, null);

        $this->assertNotEmpty($failures);
        $this->assertStringContainsString('95 words', $failures[0]);
    }

    public function testASuspectedCrossSourceDuplicateStaysADraft(): void
    {
        $failures = $this->gates($this->goodArticle(), 250, ['id' => 42, 'title' => 'x', 'score' => 0.87]);

        $this->assertNotEmpty($failures);
        $this->assertStringContainsString('#42', $failures[0]);
        $this->assertStringContainsString('87%', $failures[0]);
    }

    public function testAnArticleWithNoSubtitleAndNoKeyPointsStaysADraft(): void
    {
        $article = ['subtitle' => null, 'lead_text' => '', 'source_url' => 'https://example.test/a/1'];

        $this->assertContains('no subtitle and no key points — incomplete article', $this->gates($article, 250, null));
    }

    public function testAnArticleWithSubtitleButNoKeyPointsIsStillPublishable(): void
    {
        $article = ['subtitle' => 'একটি স্ট্যান্ডফার্স্ট।', 'lead_text' => null, 'source_url' => 'https://example.test/a/1'];

        $this->assertSame([], $this->gates($article, 250, null));
    }

    public function testUnattributedArticlesStayDrafts(): void
    {
        $article = $this->goodArticle();
        unset($article['source_url']);

        $this->assertContains('no source_url to attribute the story to', $this->gates($article, 250, null));
    }

    public function testEveryFailingGateIsReportedTogether(): void
    {
        $failures = $this->gates(['subtitle' => null, 'lead_text' => null], 10, ['id' => 7, 'title' => 'x', 'score' => 0.9], false);

        $this->assertCount(5, $failures, 'switch off, too short, duplicate, incomplete, unattributed');
    }

    public function testAFabricatedHeadlineStopsPublication(): void
    {
        // The real incident: a correct article about a CID fraud arrest was published under
        // "মহাবিদ্যালয় সভাপতি আদনানের হাত থেকে বান্ধবী উদ্ধার", which shares no word with its body.
        $article = [
            'title'      => 'মহাবিদ্যালয় সভাপতি আদনানের হাত থেকে বান্ধবী উদ্ধার',
            'subtitle'   => 'কিশোরগঞ্জে সংঘবদ্ধ অনলাইন প্রতারক চক্রের দুই সদস্য গ্রেফতার করলো সিআইডি।',
            'lead_text'  => "গ্রেফতার দুই সদস্যের বিরুদ্ধে ৯৪ লাখ টাকা আত্মসাতের অভিযোগ\nচক্রটি ফিশিং লিংক ব্যবহার করেছিল",
            'content'    => 'কিশোরগঞ্জ সদর থানা এলাকায় সংঘবদ্ধ অনলাইন প্রতারক চক্রের দুই সদস্যকে গ্রেফতার করেছে পুলিশের অপরাধ তদন্ত বিভাগ। তদন্ত চলমান রয়েছে।',
            'source_url' => 'https://dailynayadiganta.com/post/crime/1054344',
        ];

        $failures = $this->gates($article, 250, null);

        $this->assertNotEmpty($failures);
        $this->assertStringContainsString('headline does not match the article', implode(' | ', $failures));
    }

    public function testAnHonestHeadlinePassesTheGroundingCheck(): void
    {
        $article = [
            'title'      => 'কিশোরগঞ্জে অনলাইন প্রতারক চক্রের দুই সদস্য গ্রেফতার',
            'subtitle'   => 'সিআইডি জানিয়েছে, চক্রটি ফিশিং লিংক ব্যবহার করেছিল।',
            'lead_text'  => "দুই সদস্য গ্রেফতার\n৯৪ লাখ টাকা আত্মসাতের অভিযোগ",
            'content'    => 'কিশোরগঞ্জ সদর থানা এলাকায় সংঘবদ্ধ অনলাইন প্রতারক চক্রের দুই সদস্যকে গ্রেফতার করেছে সিআইডি। তদন্ত চলমান।',
            'source_url' => 'https://dailynayadiganta.com/post/crime/1054344',
        ];

        $this->assertSame([], $this->gates($article, 250, null));
    }

    public function testAutoPublishIsOffUnlessAServerExplicitlyTurnsItOn(): void
    {
        // The *shipped* default must be "file drafts only" — turning it on is a deliberate
        // server-side act (automation.autoPublish in .env), not something a caller can do.
        // Asserted on the declared property, since a developer's own .env may enable it.
        $declared = (new ReflectionClass(\Config\Automation::class))->getDefaultProperties();

        $this->assertFalse($declared['autoPublish']);
        $this->assertSame(120, $declared['autoPublishMinWords']);
    }
}
