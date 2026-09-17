<?php

namespace App\Database;

use CodeIgniter\Database\BaseConnection;

/**
 * Run a plain .sql file statement by statement.
 *
 * MySQL's CLI splits on ";" but the framework's query() needs one statement
 * at a time, so this splits the file itself — respecting quoted strings
 * (sample news bodies contain ";" and "--"), backtick identifiers, and
 * comments. Used by the baseline migration, the sample-data seeder and
 * the schema:dump command.
 */
final class SqlFile
{
    /**
     * @return list<string> statements without their trailing semicolon
     */
    public static function statements(string $sql): array
    {
        $stmts = [];
        $buf   = '';
        $quote = null;
        $n     = strlen($sql);

        for ($i = 0; $i < $n; $i++) {
            $c = $sql[$i];

            if ($quote !== null) {
                $buf .= $c;
                if ($c === '\\' && $quote !== '`' && $i + 1 < $n) {
                    $buf .= $sql[++$i];
                    continue;
                }
                if ($c === $quote) {
                    $quote = null;
                }
                continue;
            }

            if ($c === '#' || substr($sql, $i, 2) === '--') {
                $j = strpos($sql, "\n", $i);
                $i = $j === false ? $n : $j;
                continue;
            }
            if (substr($sql, $i, 2) === '/*') {
                $j = strpos($sql, '*/', $i + 2);
                $i = $j === false ? $n : $j + 1;
                continue;
            }
            if ($c === '"' || $c === "'" || $c === '`') {
                $quote = $c;
                $buf .= $c;
                continue;
            }
            if ($c === ';') {
                $s = trim($buf);
                if ($s !== '') {
                    $stmts[] = $s;
                }
                $buf = '';
                continue;
            }
            $buf .= $c;
        }

        $s = trim($buf);
        if ($s !== '') {
            $stmts[] = $s;
        }

        return $stmts;
    }

    /**
     * @return list<string>
     */
    public static function statementsFromFile(string $path): array
    {
        if (! is_file($path)) {
            throw new \RuntimeException('SQL file not found: ' . $path);
        }

        return self::statements((string) file_get_contents($path));
    }

    /**
     * Execute every statement in the file. Returns how many ran.
     */
    public static function run(BaseConnection $db, string $path): int
    {
        $count = 0;
        foreach (self::statementsFromFile($path) as $stmt) {
            $db->query($stmt);
            $count++;
        }

        return $count;
    }

    /**
     * Table names created by CREATE TABLE statements in the file, in order.
     *
     * @return list<string>
     */
    public static function createdTables(string $path): array
    {
        $tables = [];
        foreach (self::statementsFromFile($path) as $stmt) {
            if (preg_match('/^CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $stmt, $m)) {
                $tables[] = $m[1];
            }
        }

        return $tables;
    }
}
