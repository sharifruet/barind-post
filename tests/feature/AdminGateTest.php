<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * The admin gate is filters now (AdminAuthFilter on `admin/*`, RoleFilter on
 * admin-only routes), so it can be exercised without a database: every case
 * here is decided before any controller code runs.
 *
 * @internal
 */
final class AdminGateTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testAnonymousVisitorIsSentToLogin(): void
    {
        $result = $this->get('admin/users');

        $result->assertRedirectTo('/login');
    }

    public function testAnonymousAjaxCallerGetsJson401(): void
    {
        $result = $this->withHeaders(['Accept' => 'application/json'])->get('admin/incoming');

        $result->assertStatus(401);
        $result->assertJSONFragment(['error' => 'Authentication required']);
    }

    public function testReporterCannotOpenAdminOnlyAreas(): void
    {
        $session = ['logged_in' => true, 'user_id' => 1, 'user_name' => 'R', 'user_role' => 'reporter'];

        foreach (['admin/users', 'admin/roles', 'admin/reporter-roles', 'admin/incoming', 'admin/sports-events', 'admin/photo-card-generator', 'admin/logs'] as $uri) {
            $result = $this->withSession($session)->get($uri);
            $result->assertRedirectTo('/admin');
        }
    }

    public function testReporterAjaxDenialIsJson403(): void
    {
        $session = ['logged_in' => true, 'user_id' => 1, 'user_name' => 'R', 'user_role' => 'reporter'];
        $result  = $this->withSession($session)->withHeaders(['Accept' => 'application/json'])->get('admin/incoming');

        $result->assertStatus(403);
    }

    public function testEditorMayNotUseAdminOnlyRoutes(): void
    {
        $session = ['logged_in' => true, 'user_id' => 1, 'user_name' => 'E', 'user_role' => 'editor'];
        $result  = $this->withSession($session)->get('admin/photo-card-generator');

        $result->assertRedirectTo('/admin');
    }
}
