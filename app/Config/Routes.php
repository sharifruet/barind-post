<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'PublicSite::home');
$routes->get('/admin', 'Admin::dashboard');
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::attemptLogin');
$routes->get('/logout', 'Auth::logout');

// Admin Routes
$routes->get('/admin/roles', 'AdminUsers::roles', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/roles/add', 'AdminUsers::addRole', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/roles/delete', 'AdminUsers::deleteRole', ['filter' => 'role:admin,editor,sub-editor']);

$routes->get('/admin/users', 'AdminUsers::users', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/users/add', 'AdminUsers::addUser', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/users/delete', 'AdminUsers::deleteUser', ['filter' => 'role:admin,editor,sub-editor']);

$routes->get('/admin/news', 'AdminNews::newsList');
$routes->get('/admin/news/create', 'AdminNews::newsCreate');
$routes->post('/admin/news/create', 'AdminNews::newsStore');
$routes->get('/admin/news/edit/(:num)', 'AdminNews::newsEdit/$1');
$routes->post('/admin/news/edit/(:num)', 'AdminNews::newsUpdate/$1');
$routes->post('/admin/news/delete/(:num)', 'AdminNews::newsDelete/$1');
$routes->post('/admin/news/toggle-featured/(:num)', 'AdminNews::toggleFeatured/$1', ['filter' => 'role:admin']);
$routes->post('/admin/news/toggle-breaking/(:num)', 'AdminNews::toggleBreakingNews/$1', ['filter' => 'role:admin']);

// Photo Card Generation Routes (Admin only) - Front-end JavaScript based
$routes->get('/admin/photo-card-generator', 'AdminPhotoCards::photoCardGenerator', ['filter' => 'role:admin']);
$routes->post('/admin/photo-card-generator/generate', 'AdminPhotoCards::generatePhotoCard', ['filter' => 'role:admin']);

// Add route for listing news images
$routes->get('/admin/news/search', 'AdminNews::newsSearch');

// Admin incoming queue — review/publish/discard automation drafts (AdminIncoming)
$routes->get('/admin/incoming', 'AdminIncoming::index', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/incoming/publish/(:num)', 'AdminIncoming::publish/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/incoming/discard/(:num)', 'AdminIncoming::discard/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/incoming/restore/(:num)', 'AdminIncoming::restore/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/incoming/discard-bulk', 'AdminIncoming::discardBulk', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/incoming/category/(:num)', 'AdminIncoming::category/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/incoming/adopt-image/(:num)', 'AdminIncoming::adoptImage/$1', ['filter' => 'role:admin,editor,sub-editor']);

// View-count beacon: pinged by the article page's JS so counting survives page caching
$routes->post('/news/view/(:num)', 'PublicSite::trackViewBeacon/$1');

// Image Upload Routes
$routes->post('/image-upload/upload', 'ImageUpload::upload');
$routes->post('/image-upload/update-caption', 'ImageUpload::updateCaption');
$routes->post('/image-upload/delete', 'ImageUpload::delete');
$routes->post('/image-upload/set-featured', 'ImageUpload::setFeatured');
$routes->get('/image-upload/get-images/(:num)', 'ImageUpload::getImages/$1');
$routes->get('/image-upload/existing-images', 'ImageUpload::getExistingImages');

// Prayer Times API Routes
$routes->get('/api/prayer-times/today/(:num)', 'PrayerTimes::getToday/$1');
$routes->get('/api/prayer-times/cities', 'PrayerTimes::getCities');

// Reusable Image Routes
$routes->get('/image-upload/all-images', 'ImageUpload::getAllImages');
$routes->post('/image-upload/link-image', 'ImageUpload::linkImage');
$routes->post('/image-upload/remove-from-news', 'ImageUpload::removeFromNews');

$routes->get('/admin/tags', 'AdminTags::tags');
$routes->post('/admin/tags/add', 'AdminTags::addTag');
$routes->post('/admin/tags/delete', 'AdminTags::deleteTag');
$routes->get('/admin/tags/edit/(:num)', 'AdminTags::editTag/$1');
$routes->post('/admin/tags/edit/(:num)', 'AdminTags::updateTag/$1');

$routes->get('/admin/categories', 'AdminCategories::categories');
$routes->post('/admin/categories/add', 'AdminCategories::addCategory');
$routes->post('/admin/categories/delete', 'AdminCategories::deleteCategory');
$routes->get('/admin/categories/edit/(:num)', 'AdminCategories::editCategory/$1');
$routes->post('/admin/categories/edit/(:num)', 'AdminCategories::updateCategory/$1');

// Reporter Roles Routes
$routes->get('/admin/reporter-roles', 'AdminReporterRoles::reporterRoles', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/reporter-roles/add', 'AdminReporterRoles::addReporterRole', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/reporter-roles/delete', 'AdminReporterRoles::deleteReporterRole', ['filter' => 'role:admin,editor,sub-editor']);
$routes->get('/admin/reporter-roles/edit/(:num)', 'AdminReporterRoles::editReporterRole/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/reporter-roles/edit/(:num)', 'AdminReporterRoles::updateReporterRole/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->get('/admin/reporter-roles/assign/(:num)', 'AdminReporterRoles::assignReporterRoles/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/reporter-roles/assign/(:num)', 'AdminReporterRoles::saveReporterRoleAssignment/$1', ['filter' => 'role:admin,editor,sub-editor']);

// Admin Contacts Routes
$routes->get('/admin/contacts', 'AdminContacts::contacts');
$routes->get('/admin/contacts/list', 'AdminContacts::getContacts');
$routes->get('/admin/contacts/(:num)', 'AdminContacts::getContact/$1');
$routes->post('/admin/contacts/(:num)/reply', 'AdminContacts::replyToContact/$1');
$routes->delete('/admin/contacts/(:num)', 'AdminContacts::deleteContact/$1');
$routes->get('/admin/contacts/export', 'AdminContacts::exportContacts');

// Public Site Routes
$routes->get('/section/(:segment)', 'PublicSite::section/$1');
$routes->get('/news/(:any)', 'PublicSite::news/$1');
$routes->get('/news-bn/(:any)', 'PublicSite::newsByTitle/$1');
$routes->get('/tag/(:segment)', 'PublicSite::tag/$1');
$routes->get('/search', 'PublicSite::search');
$routes->get('/privacy', 'PublicSite::privacy');
$routes->get('/terms', 'PublicSite::terms');
$routes->get('/contact', 'PublicSite::contact');
$routes->post('/contact', 'PublicSite::submitContact');
$routes->get('/ads', 'PublicSite::ads');
$routes->get('/barind-post', 'PublicSite::about');

// Google News sitemap (last 48 h of published articles)
$routes->get('/news-sitemap.xml', 'PublicSite::newsSitemap');

// RSS Feed Routes
$routes->get('/rss', 'PublicSite::rss');
$routes->get('/rss/category/(:segment)', 'PublicSite::rssCategory/$1');
$routes->get('/rss-info', 'PublicSite::rssInfo');

// Admin Prayer Times Management Routes
$routes->get('/admin/prayer-times', 'AdminPrayerTimes::prayerTimes');
$routes->get('/admin/prayer-times/(:num)', 'AdminPrayerTimes::prayerTimes/$1');
$routes->get('/admin/prayer-times/fetch/(:num)/(:num)', 'AdminPrayerTimes::fetchPrayerTimes/$1/$2');
$routes->get('/admin/prayer-times/delete/(:num)/(:num)', 'AdminPrayerTimes::deletePrayerTimes/$1/$2');

// Prayer Times API Routes
$routes->get('/prayer-time/(:num)', 'PrayerTimes::index/$1');
$routes->get('/prayer-time/(:num)/(:num)', 'PrayerTimes::fetchCityYear/$1/$2');
$routes->get('/prayer-time/city/(:num)/(:any)', 'PrayerTimes::getCityDate/$1/$2');
$routes->get('/prayer-time/debug-api', 'PrayerTimes::debugApi');

// Public Prayer Times AJAX Routes
$routes->get('/prayer-time/today', 'PrayerTimes::getToday');
$routes->get('/prayer-time/today/(:num)', 'PrayerTimes::getToday/$1');
$routes->get('/prayer-time/cities', 'PrayerTimes::getCities');

// Admin Logs Route
$routes->get('/admin/logs', 'Admin::viewLogs', ['filter' => 'role:admin']); // logs can contain internals: admins only

// Kicker Management Routes
$routes->get('/admin/kickers', 'AdminKickers::kickers');
$routes->get('/admin/kickers/create', 'AdminKickers::createKicker');
$routes->post('/admin/kickers/create', 'AdminKickers::createKicker');
$routes->get('/admin/kickers/edit/(:num)', 'AdminKickers::editKicker/$1');
$routes->post('/admin/kickers/edit/(:num)', 'AdminKickers::editKicker/$1');
$routes->post('/admin/kickers/delete/(:num)', 'AdminKickers::deleteKicker/$1');
$routes->get('/admin/kickers/api', 'AdminKickers::getKickers');

// Sports Events — Admin
$routes->get('/admin/sports-events', 'AdminSportsEvents::index', ['filter' => 'role:admin,editor,sub-editor']);
$routes->get('/admin/sports-events/create', 'AdminSportsEvents::create', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/sports-events/create', 'AdminSportsEvents::store', ['filter' => 'role:admin,editor,sub-editor']);
$routes->get('/admin/sports-events/edit/(:num)', 'AdminSportsEvents::edit/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/sports-events/edit/(:num)', 'AdminSportsEvents::update/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/sports-events/delete/(:num)', 'AdminSportsEvents::delete/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->get('/admin/sports-events/manage/(:num)', 'AdminSportsEvents::manage/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->get('/admin/sports-events/participants', 'AdminSportsEvents::participants', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/sports-events/participants/add', 'AdminSportsEvents::addParticipant', ['filter' => 'role:admin,editor,sub-editor']);
$routes->get('/admin/sports-events/participants/edit/(:num)', 'AdminSportsEvents::editParticipant/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/sports-events/participants/edit/(:num)', 'AdminSportsEvents::updateParticipant/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/sports-events/participants/delete/(:num)', 'AdminSportsEvents::deleteParticipant/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->get('/admin/sports-events/venues', 'AdminSportsEvents::venues', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/sports-events/venues/add', 'AdminSportsEvents::addVenue', ['filter' => 'role:admin,editor,sub-editor']);
$routes->get('/admin/sports-events/venues/edit/(:num)', 'AdminSportsEvents::editVenue/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/sports-events/venues/edit/(:num)', 'AdminSportsEvents::updateVenue/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/sports-events/venues/delete/(:num)', 'AdminSportsEvents::deleteVenue/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->get('/admin/sports-events/(:num)/teams', 'AdminSportsEvents::eventTeams/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/sports-events/(:num)/teams/add', 'AdminSportsEvents::addEventTeam/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/sports-events/(:num)/teams/update/(:num)', 'AdminSportsEvents::updateEventTeam/$1/$2', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/sports-events/(:num)/teams/remove/(:num)', 'AdminSportsEvents::removeEventTeam/$1/$2', ['filter' => 'role:admin,editor,sub-editor']);
$routes->get('/admin/sports-events/(:num)/matches', 'AdminSportsEvents::matches/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->get('/admin/sports-events/(:num)/matches/create', 'AdminSportsEvents::createMatch/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/sports-events/(:num)/matches/create', 'AdminSportsEvents::storeMatch/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->get('/admin/sports-events/(:num)/matches/edit/(:num)', 'AdminSportsEvents::editMatch/$1/$2', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/sports-events/(:num)/matches/edit/(:num)', 'AdminSportsEvents::updateMatch/$1/$2', ['filter' => 'role:admin,editor,sub-editor']);
$routes->post('/admin/sports-events/(:num)/matches/delete/(:num)', 'AdminSportsEvents::deleteMatch/$1/$2', ['filter' => 'role:admin,editor,sub-editor']);
$routes->get('/admin/sports-events/(:num)/standings', 'AdminSportsEvents::standings/$1', ['filter' => 'role:admin,editor,sub-editor']);
$routes->get('/admin/sports-events/(:num)/news', 'AdminSportsEvents::eventNews/$1', ['filter' => 'role:admin,editor,sub-editor']);

// Sports Events — Public
$routes->get('/sports/(:segment)', 'SportsEvent::hub/$1');
$routes->get('/sports/(:segment)/fixtures', 'SportsEvent::fixtures/$1');
$routes->get('/sports/(:segment)/results', 'SportsEvent::results/$1');
$routes->get('/sports/(:segment)/standings', 'SportsEvent::standings/$1');
$routes->get('/sports/(:segment)/stats', 'SportsEvent::stats/$1');
$routes->get('/sports/(:segment)/teams', 'SportsEvent::teams/$1');
$routes->get('/sports/(:segment)/team/(:segment)', 'SportsEvent::team/$1/$2');
$routes->get('/sports/(:segment)/match/(:segment)', 'SportsEvent::match/$1/$2');
$routes->get('/sports/(:segment)/news', 'SportsEvent::news/$1');

// Automation API (n8n) — see N8N_NEWS_AUTOMATION_PLAN.md. Guarded by the 'apikey' filter.
$routes->group('api/v1', ['filter' => 'apikey'], static function ($routes) {
    $routes->get('news/exists', 'Api\NewsController::exists');
    $routes->get('news/(:num)', 'Api\NewsController::show/$1');
    $routes->post('news', 'Api\NewsController::create');
    $routes->get('categories', 'Api\NewsController::categories');
    $routes->get('tags', 'Api\NewsController::tags');
    $routes->post('automation/runs', 'Api\AutomationController::createRun'); // n8n run summaries / error reports
});

// Open Graph card for articles without a photo (GD-rendered headline card, cached in writable/og)
$routes->get('/og/(:num)\.png', 'PublicSite::ogImage/$1');
