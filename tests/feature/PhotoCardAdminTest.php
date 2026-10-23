<?php

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Access to admin → Photo Cards and its save endpoint (AdminPhotoCards). Database-free:
 * the filters answer before any controller runs, and in the test harness a refusal
 * surfaces as the exception the filter raised. Role denials for the page itself are in
 * AdminGateTest.
 *
 * @internal
 */
final class PhotoCardAdminTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testACardCannotBeSavedWithoutTheCsrfToken(): void
    {
        $this->expectException(SecurityException::class);

        try {
            $this->post('admin/photo-card-generator/save', ['news_id' => 1]);
        } finally {
            $this->assertEmpty(glob(ROOTPATH . 'public/uploads/photo_cards/card-1-*.png'));
        }
    }

    public function testTheSaveEndpointIsPostOnly(): void
    {
        $session = ['logged_in' => true, 'user_id' => 1, 'user_name' => 'A', 'user_role' => 'admin'];

        $this->expectException(PageNotFoundException::class);
        $this->withSession($session)->get('admin/photo-card-generator/save');
    }

    public function testACardCannotBePostedToFacebookWithoutTheCsrfToken(): void
    {
        $session = ['logged_in' => true, 'user_id' => 1, 'user_name' => 'A', 'user_role' => 'admin'];

        $this->expectException(SecurityException::class);
        $this->withSession($session)->post('admin/photo-card-generator/facebook', ['news_id' => 1, 'message' => 'x']);
    }

    public function testTheFacebookEndpointIsPostOnly(): void
    {
        $session = ['logged_in' => true, 'user_id' => 1, 'user_name' => 'A', 'user_role' => 'admin'];

        $this->expectException(PageNotFoundException::class);
        $this->withSession($session)->get('admin/photo-card-generator/facebook');
    }

    public function testTheOldGdEndpointIsGone(): void
    {
        $session = ['logged_in' => true, 'user_id' => 1, 'user_name' => 'A', 'user_role' => 'admin'];

        $this->expectException(PageNotFoundException::class);
        $this->withSession($session)->get('admin/photo-card-generator/generate');
    }
}
