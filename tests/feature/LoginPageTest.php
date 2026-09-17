<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Regression guard for the login page: it must render without a database and
 * its form must carry the CSRF field (CSRF is enforced globally).
 *
 * @internal
 */
final class LoginPageTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testLoginPageRendersWithCsrfField(): void
    {
        $result = $this->get('login');

        $result->assertStatus(200);

        // Attributes, not text, so check the raw body (DOMParser's selectors are tag/#id/.class only).
        $body = (string) $result->response()->getBody();
        $this->assertStringContainsString('action="/login"', $body);
        $this->assertMatchesRegularExpression('/<input[^>]+name="csrf_test_name"[^>]+value="[0-9a-f]{32}"/', $body);
    }

    // Note: the admin gate (BaseAdminController) uses header()+exit, which would
    // terminate the PHPUnit process — it is covered by the curl-based checks in
    // the docs, not here, until that gate becomes a filter.
}
