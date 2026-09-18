<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * The two deployments disagree about the document root:
 *
 *   Docker      docroot = <repo>/public   → FCPATH = <repo>/public/ → file at FCPATH.'assets/…'
 *   cPanel      docroot = <repo>          → FCPATH = <repo>/        → file at FCPATH.'public/assets/…'
 *
 * A stylesheet URL that hardcodes one shape 404s in the other — which is exactly how the
 * revamped theme.css ended up missing on www.barindpost.com while working in Docker.
 * asset_file_variant() takes the base path explicitly so both shapes are testable here.
 *
 * @internal
 */
final class AssetUrlTest extends CIUnitTestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        helper('slug_helper');
        $this->root = sys_get_temp_dir() . '/asset-url-' . bin2hex(random_bytes(4));
        mkdir($this->root . '/public/assets/css', 0777, true);
        file_put_contents($this->root . '/public/assets/css/theme.css', 'body{}');
        file_put_contents($this->root . '/public/favicon-32x32.png', 'png');
    }

    protected function tearDown(): void
    {
        foreach (array_reverse(iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST))) as $f) {
            $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
        }
        @rmdir($this->root);
        parent::tearDown();
    }

    public function testDockerLayoutServesTheFileFromTheDocumentRoot(): void
    {
        // docroot is <repo>/public, so the URL path has no "public/" prefix
        $found = asset_file_variant($this->root . '/public', 'assets/css/theme.css');

        $this->assertNotNull($found);
        $this->assertSame('assets/css/theme.css', $found[0]);
        $this->assertGreaterThan(0, $found[1]);
    }

    public function testRootDeploymentFindsTheFileUnderPublic(): void
    {
        // docroot is the repo root: the same file must be addressed as public/assets/…
        $found = asset_file_variant($this->root, 'assets/css/theme.css');

        $this->assertNotNull($found);
        $this->assertSame('public/assets/css/theme.css', $found[0]);
    }

    public function testIconsResolveInBothLayouts(): void
    {
        $this->assertSame('favicon-32x32.png', asset_file_variant($this->root . '/public', 'favicon-32x32.png')[0]);
        $this->assertSame('public/favicon-32x32.png', asset_file_variant($this->root, 'favicon-32x32.png')[0]);
    }

    public function testMissingFileReturnsNullSoTheCallerCanFallBack(): void
    {
        $this->assertNull(asset_file_variant($this->root, 'assets/css/nope.css'));
        $this->assertNull(asset_file_variant($this->root . '/public', 'assets/css/nope.css'));
    }

    public function testLeadingSlashesAndTrailingSeparatorsAreTolerated(): void
    {
        $this->assertSame('public/assets/css/theme.css', asset_file_variant($this->root . '/', '/assets/css/theme.css')[0]);
    }

    public function testShippedStylesheetsAndIconsExistInTheRepo(): void
    {
        // Guards the deploy: these are the files the layouts ask for.
        foreach (['assets/css/theme.css', 'assets/css/admin.css', 'favicon.ico', 'favicon-16x16.png', 'favicon-32x32.png', 'apple-touch-icon.png'] as $asset) {
            $this->assertNotNull(asset_file_variant(ROOTPATH, $asset), "missing from the repo: {$asset}");
        }
    }
}
