<?php

use App\Libraries\TitleSimilarity;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class TitleSimilarityTest extends CIUnitTestCase
{
    public function testSameEventFromTwoOutletsScoresAsDuplicate(): void
    {
        $a = 'ডেঙ্গুতে আরো ৫ জনের মৃত্যু, হাসপাতালে ভর্তি ১৪৮৪';
        $b = 'ডেঙ্গুতে ২৪ ঘণ্টায় ৫ জনের মৃত্যু';

        $score = TitleSimilarity::score($a, $b);
        $this->assertGreaterThanOrEqual(TitleSimilarity::THRESHOLD, $score);
        $this->assertTrue(TitleSimilarity::isDuplicate($score));
    }

    public function testDifferentStoriesOnTheSameTopicAreNotDuplicates(): void
    {
        $a = 'ডেঙ্গু প্রতিরোধে কনটেন্ট নির্মাতাদের সহায়তা চায় স্বাস্থ্য মন্ত্রণালয়';
        $b = 'ডেঙ্গুতে ২৪ ঘণ্টায় ৫ জনের মৃত্যু';

        $this->assertFalse(TitleSimilarity::isDuplicate(TitleSimilarity::score($a, $b)));
    }

    public function testShortOrStopwordOnlyTitlesNeverMatch(): void
    {
        $this->assertSame(0.0, TitleSimilarity::score('সংক্ষিপ্ত সংবাদ', 'সংক্ষিপ্ত সংবাদ'));
        $this->assertSame(0.0, TitleSimilarity::score('এ ও এর জন্য', 'এ ও এর জন্য থেকে'));
    }

    public function testTokensNormalisePunctuationDigitsCaseAndStopwords(): void
    {
        $this->assertSame(
            ['bangladesh', '5', 'wickets', 'win'],
            TitleSimilarity::tokens('Bangladesh, ৫ wickets: the WIN!'),
        );
    }

    public function testEnglishHeadlinesWork(): void
    {
        $score = TitleSimilarity::score('Abhishek Sharma hits fastest T20I ton by a Test-playing nation', 'Abhishek Sharma smashes fastest T20I ton');
        $this->assertTrue(TitleSimilarity::isDuplicate($score));
    }
}
