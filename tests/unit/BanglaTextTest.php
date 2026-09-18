<?php

use App\Libraries\BanglaText;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Guards the "is this actually Bangla?" check behind the publish gate. Every case here is
 * taken from real pipeline output.
 *
 * @internal
 */
final class BanglaTextTest extends CIUnitTestCase
{
    public function testCleanBanglaHasNoProblems(): void
    {
        $text = 'মেক্সিকোতে স্প্যানিশ শিক্ষার্থী নিহত। প্রতিদিন গড়ে প্রায় ১০ জন নারী হত্যা হন।';

        $this->assertSame([], BanglaText::problems($text));
    }

    public function testTheDandaIsNotTreatedAsForeignScript(): void
    {
        // "।" is U+0964, inside the Devanagari block but the normal Bangla full stop.
        // Counting it as foreign marks every correct article as broken.
        $r = BanglaText::inspect('এটি একটি বাক্য। এটি আরেকটি বাক্য।');

        $this->assertSame([], $r['foreign']);
        $this->assertSame([], BanglaText::problems('এটি একটি বাক্য। আরেকটি বাক্য।'));
    }

    public function testUntranslatedEnglishClauseIsCaught(): void
    {
        $text     = 'কুয়াটলায় dozens of student dressed in black shown up. তারা স্লোগান তুলে';
        $problems = BanglaText::problems($text);

        $this->assertNotEmpty($problems);
        $this->assertStringContainsString('untranslated Latin words', $problems[0]);
    }

    public function testStrayForeignLanguageWordsAreCaught(): void
    {
        // "geweld" and "aldus" are Dutch — both appeared in real output.
        $this->assertNotEmpty(BanglaText::problems('মোরেলোস রাজ্যে geweld দেখা যাচ্ছে aldus কর্তৃপক্ষ bijzonder'));
    }

    public function testAnOccasionalAcronymIsTolerated(): void
    {
        // Bangla news does print the odd acronym; the gate should not block on one or two.
        $this->assertSame([], BanglaText::problems('আইসিসির সভায় ICC ও BFF অংশ নেয়। সিদ্ধান্ত হয়েছে।'));
    }

    public function testOtherScriptsAreCaught(): void
    {
        foreach (['हिंदी में लिखा', 'العربية نص', '中文文本'] as $foreign) {
            $problems = BanglaText::problems('বাংলা লেখা ' . $foreign);
            $this->assertNotEmpty($problems, $foreign);
            $this->assertStringContainsString('non-Bangla script', $problems[0]);
        }
    }

    public function testEscapedNewlinesAreRepaired(): void
    {
        $broken = 'প্রথম অনুচ্ছেদ।\n\nMorelos রাজ্যে দ্বিতীয় অনুচ্ছেদ।';
        $fixed  = BanglaText::fixEscapedNewlines($broken);

        $this->assertStringNotContainsString('\\n', $fixed);
        $this->assertStringContainsString("\n\n", $fixed);
        $this->assertNull(BanglaText::fixEscapedNewlines(null));
    }

    public function testRealNewlinesSurviveUntouched(): void
    {
        $this->assertSame("ক\n\nখ", BanglaText::fixEscapedNewlines("ক\n\nখ"));
    }
}
