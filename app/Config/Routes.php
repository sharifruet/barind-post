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

// Photo Card Generation Routes (Admin only) - Front-end JavaScript based
$routes->get('/admin/photo-card-generator', 'Admin::photoCardGenerator');

// Add route for listing news images
$routes->get('/admin/news/images-list', 'Admin::newsImagesList');

// Image Upload Routes
$routes->post('/image-upload/upload', 'ImageUpload::upload');
$routes->post('/image-upload/update-caption', 'ImageUpload::updateCaption');
$routes->post('/image-upload/delete', 'ImageUpload::delete');
$routes->post('/image-upload/set-featured', 'ImageUpload::setFeatured');
$routes->get('/image-upload/get-images/(:num)', 'ImageUpload::getImages/$1');
$routes->get('/image-upload/existing-images', 'ImageUpload::getExistingImages');

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
