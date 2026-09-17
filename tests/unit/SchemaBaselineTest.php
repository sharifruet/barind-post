<?php

use App\Database\SqlFile;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The baseline schema (app/Database/Schema/baseline.sql) is the single source of
 * truth for the database. These checks keep it honest without a database:
 * it must be non-destructive, and every table the application code touches
 * must be defined in it.
 *
 * @internal
 */
final class SchemaBaselineTest extends CIUnitTestCase
{
    private const BASELINE = APPPATH . 'Database/Schema/baseline.sql';
    private const SAMPLE   = APPPATH . 'Database/Schema/sample_data.sql';

    public function testBaselineIsCreateIfNotExistsOnly(): void
    {
        $stmts = SqlFile::statementsFromFile(self::BASELINE);

        $this->assertGreaterThanOrEqual(20, count($stmts));
        foreach ($stmts as $stmt) {
            $this->assertMatchesRegularExpression('/^CREATE TABLE IF NOT EXISTS `?\w+`?/i', $stmt, substr($stmt, 0, 60));
            $this->assertStringNotContainsStringIgnoringCase('DROP ', $stmt);
        }
    }

    public function testEveryTableUsedByTheCodeExistsInTheBaseline(): void
    {
        // Baseline tables plus any CREATE TABLE in later migrations.
        $defined = SqlFile::createdTables(self::BASELINE);
        foreach (glob(APPPATH . 'Database/Migrations/*.php') as $migration) {
            if (preg_match_all('/CREATE TABLE IF NOT EXISTS `?(\w+)`?/i', (string) file_get_contents($migration), $m)) {
                $defined = array_merge($defined, $m[1]);
            }
        }

        $used = [];
        foreach ($this->phpFiles(APPPATH . 'Models') + $this->phpFiles(APPPATH . 'Controllers') + $this->phpFiles(APPPATH . 'Views') as $file) {
            $src = (string) file_get_contents($file);
            if (preg_match_all('/protected\s+\$table\s*=\s*[\'"](\w+)[\'"]/', $src, $m)) {
                $used = array_merge($used, $m[1]);
            }
            if (preg_match_all('/->table\(\s*[\'"](\w+)[\'"]\s*\)/', $src, $m)) {
                $used = array_merge($used, $m[1]);
            }
        }
        $used = array_values(array_unique(array_diff($used, ['migrations'])));
        sort($used);

        $this->assertNotEmpty($used);
        $this->assertSame([], array_values(array_diff($used, $defined)), 'Tables referenced by code but missing from baseline.sql');
    }

    public function testSampleDataOnlyInserts(): void
    {
        $stmts = SqlFile::statementsFromFile(self::SAMPLE);

        $this->assertNotEmpty($stmts);
        foreach ($stmts as $stmt) {
            $this->assertMatchesRegularExpression('/^INSERT INTO /i', $stmt, substr($stmt, 0, 60));
        }
    }

    public function testSplitterRespectsQuotesAndComments(): void
    {
        $sql = "INSERT INTO t (a) VALUES ('x; -- not a comment; y');\n-- a comment; with semicolons\nSELECT 1; /* block; comment */ SELECT `we;ird`;";

        $this->assertSame(
            ["INSERT INTO t (a) VALUES ('x; -- not a comment; y')", 'SELECT 1', 'SELECT `we;ird`'],
            SqlFile::statements($sql),
        );
    }

    /**
     * @return array<string, string> path => path
     */
    private function phpFiles(string $dir): array
    {
        $out = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $f) {
            if ($f->isFile() && $f->getExtension() === 'php') {
                $out[$f->getPathname()] = $f->getPathname();
            }
        }

        return $out;
    }
}
