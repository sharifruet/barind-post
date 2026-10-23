<?php

use App\Controllers\AdminPhotoCards;
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

    public function testTheArticlePickerFeedIsAdminOnly(): void
    {
        $this->withHeaders(['Accept' => 'application/json'])->get('admin/photo-card-generator/articles?page=2')->assertStatus(401);

        $editor = ['logged_in' => true, 'user_id' => 1, 'user_name' => 'E', 'user_role' => 'editor'];
        $this->withSession($editor)->withHeaders(['Accept' => 'application/json'])->get('admin/photo-card-generator/articles')->assertStatus(403);
    }

    public function testThePickerAlwaysHasAtLeastOnePage(): void
    {
        $this->assertSame(1, AdminPhotoCards::pageCount(0));
        $this->assertSame(1, AdminPhotoCards::pageCount(AdminPhotoCards::PER_PAGE));
        $this->assertSame(2, AdminPhotoCards::pageCount(AdminPhotoCards::PER_PAGE + 1));
        $this->assertSame(16, AdminPhotoCards::pageCount(151));
    }

    public function testTheOldGdEndpointIsGone(): void
    {
        $session = ['logged_in' => true, 'user_id' => 1, 'user_name' => 'A', 'user_role' => 'admin'];

        $this->expectException(PageNotFoundException::class);
        $this->withSession($session)->get('admin/photo-card-generator/generate');
    }
}
