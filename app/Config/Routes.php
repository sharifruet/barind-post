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
$routes->get('/admin/roles', 'Admin::roles');
$routes->post('/admin/roles/add', 'Admin::addRole');
$routes->post('/admin/roles/delete', 'Admin::deleteRole');
$routes->get('/admin/roles/edit/(:num)', 'Admin::editRole/$1'); // Added for roles edit
$routes->post('/admin/roles/edit/(:num)', 'Admin::updateRole/$1'); // Added for roles update

$routes->get('/admin/users', 'Admin::users');
$routes->post('/admin/users/add', 'Admin::addUser');
$routes->post('/admin/users/delete', 'Admin::deleteUser');

$routes->get('/admin/news', 'Admin::newsList');
$routes->get('/test', 'Admin::test');
$routes->get('/admin/news/create', 'Admin::newsCreate');
$routes->post('/admin/news/create', 'Admin::newsStore');
$routes->get('/admin/news/edit/(:num)', 'Admin::newsEdit/$1');
$routes->post('/admin/news/edit/(:num)', 'Admin::newsUpdate/$1');
$routes->post('/admin/news/delete/(:num)', 'Admin::newsDelete/$1');
$routes->post('/admin/news/toggle-featured/(:num)', 'Admin::toggleFeatured/$1');
$routes->post('/admin/news/toggle-breaking/(:num)', 'Admin::toggleBreakingNews/$1');

// Photo Card Generation Routes (Admin only) - Front-end JavaScript based
$routes->get('/admin/photo-card-generator', 'Admin::photoCardGenerator');
$routes->post('/admin/photo-card-generator/generate', 'Admin::generatePhotoCard');

// Add route for listing news images
$routes->get('/admin/news/images-list', 'Admin::newsImagesList');

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

$routes->get('/admin/tags', 'Admin::tags');
$routes->post('/admin/tags/add', 'Admin::addTag');
$routes->post('/admin/tags/delete', 'Admin::deleteTag');
$routes->get('/admin/tags/edit/(:num)', 'Admin::editTag/$1');
$routes->post('/admin/tags/edit/(:num)', 'Admin::updateTag/$1');

$routes->get('/admin/categories', 'Admin::categories');
$routes->post('/admin/categories/add', 'Admin::addCategory');
$routes->post('/admin/categories/delete', 'Admin::deleteCategory');
$routes->get('/admin/categories/edit/(:num)', 'Admin::editCategory/$1');
$routes->post('/admin/categories/edit/(:num)', 'Admin::updateCategory/$1');

// Reporter Roles Routes
$routes->get('/admin/reporter-roles', 'Admin::reporterRoles');
$routes->post('/admin/reporter-roles/add', 'Admin::addReporterRole');
$routes->post('/admin/reporter-roles/delete', 'Admin::deleteReporterRole');
$routes->get('/admin/reporter-roles/edit/(:num)', 'Admin::editReporterRole/$1');
$routes->post('/admin/reporter-roles/edit/(:num)', 'Admin::updateReporterRole/$1');
$routes->get('/admin/reporter-roles/assign/(:num)', 'Admin::assignReporterRoles/$1');
$routes->post('/admin/reporter-roles/assign/(:num)', 'Admin::saveReporterRoleAssignment/$1');

// Admin Contacts Routes
$routes->get('/admin/contacts', 'Admin::contacts');
$routes->get('/admin/contacts/list', 'Admin::getContacts');
$routes->get('/admin/contacts/(:num)', 'Admin::getContact/$1');
$routes->post('/admin/contacts/(:num)/reply', 'Admin::replyToContact/$1');
$routes->delete('/admin/contacts/(:num)', 'Admin::deleteContact/$1');
$routes->get('/admin/contacts/export', 'Admin::exportContacts');

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

// RSS Feed Routes
$routes->get('/rss', 'PublicSite::rss');
$routes->get('/rss/category/(:segment)', 'PublicSite::rssCategory/$1');
$routes->get('/rss-info', 'PublicSite::rssInfo');

// Admin Prayer Times Management Routes
$routes->get('/admin/prayer-times', 'Admin::prayerTimes');
$routes->get('/admin/prayer-times/(:num)', 'Admin::prayerTimes/$1');
$routes->get('/admin/prayer-times/fetch/(:num)/(:num)', 'Admin::fetchPrayerTimes/$1/$2');
$routes->get('/admin/prayer-times/delete/(:num)/(:num)', 'Admin::deletePrayerTimes/$1/$2');

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
$routes->get('/admin/logs', 'Admin::viewLogs');

// Kicker Management Routes
$routes->get('/admin/kickers', 'Admin::kickers');
$routes->get('/admin/kickers/create', 'Admin::createKicker');
$routes->post('/admin/kickers/create', 'Admin::createKicker');
$routes->get('/admin/kickers/edit/(:num)', 'Admin::editKicker/$1');
$routes->post('/admin/kickers/edit/(:num)', 'Admin::editKicker/$1');
$routes->post('/admin/kickers/delete/(:num)', 'Admin::deleteKicker/$1');
$routes->get('/admin/kickers/api', 'Admin::getKickers');

// Sports Events — Admin
$routes->get('/admin/sports-events', 'AdminSportsEvents::index');
$routes->get('/admin/sports-events/create', 'AdminSportsEvents::create');
$routes->post('/admin/sports-events/create', 'AdminSportsEvents::store');
$routes->get('/admin/sports-events/edit/(:num)', 'AdminSportsEvents::edit/$1');
$routes->post('/admin/sports-events/edit/(:num)', 'AdminSportsEvents::update/$1');
$routes->post('/admin/sports-events/delete/(:num)', 'AdminSportsEvents::delete/$1');
$routes->get('/admin/sports-events/manage/(:num)', 'AdminSportsEvents::manage/$1');
$routes->get('/admin/sports-events/participants', 'AdminSportsEvents::participants');
$routes->post('/admin/sports-events/participants/add', 'AdminSportsEvents::addParticipant');
$routes->get('/admin/sports-events/participants/edit/(:num)', 'AdminSportsEvents::editParticipant/$1');
$routes->post('/admin/sports-events/participants/edit/(:num)', 'AdminSportsEvents::updateParticipant/$1');
$routes->post('/admin/sports-events/participants/delete/(:num)', 'AdminSportsEvents::deleteParticipant/$1');
$routes->get('/admin/sports-events/venues', 'AdminSportsEvents::venues');
$routes->post('/admin/sports-events/venues/add', 'AdminSportsEvents::addVenue');
$routes->get('/admin/sports-events/venues/edit/(:num)', 'AdminSportsEvents::editVenue/$1');
$routes->post('/admin/sports-events/venues/edit/(:num)', 'AdminSportsEvents::updateVenue/$1');
$routes->post('/admin/sports-events/venues/delete/(:num)', 'AdminSportsEvents::deleteVenue/$1');
$routes->get('/admin/sports-events/(:num)/teams', 'AdminSportsEvents::eventTeams/$1');
$routes->post('/admin/sports-events/(:num)/teams/add', 'AdminSportsEvents::addEventTeam/$1');
$routes->post('/admin/sports-events/(:num)/teams/update/(:num)', 'AdminSportsEvents::updateEventTeam/$1/$2');
$routes->post('/admin/sports-events/(:num)/teams/remove/(:num)', 'AdminSportsEvents::removeEventTeam/$1/$2');
$routes->get('/admin/sports-events/(:num)/matches', 'AdminSportsEvents::matches/$1');
$routes->get('/admin/sports-events/(:num)/matches/create', 'AdminSportsEvents::createMatch/$1');
$routes->post('/admin/sports-events/(:num)/matches/create', 'AdminSportsEvents::storeMatch/$1');
$routes->get('/admin/sports-events/(:num)/matches/edit/(:num)', 'AdminSportsEvents::editMatch/$1/$2');
$routes->post('/admin/sports-events/(:num)/matches/edit/(:num)', 'AdminSportsEvents::updateMatch/$1/$2');
$routes->post('/admin/sports-events/(:num)/matches/delete/(:num)', 'AdminSportsEvents::deleteMatch/$1/$2');
$routes->get('/admin/sports-events/(:num)/standings', 'AdminSportsEvents::standings/$1');
$routes->get('/admin/sports-events/(:num)/news', 'AdminSportsEvents::eventNews/$1');

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
    $routes->post('news', 'Api\NewsController::create');
    $routes->get('categories', 'Api\NewsController::categories');
    $routes->get('tags', 'Api\NewsController::tags');
});
