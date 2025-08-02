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

// Add route for listing news images
$routes->get('/admin/news/images-list', 'Admin::newsImagesList');

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

// Public Site Routes
$routes->get('/section/(:segment)', 'PublicSite::section/$1');
$routes->get('/news/(:any)', 'PublicSite::news/$1');
$routes->get('/news-bn/(:any)', 'PublicSite::newsByTitle/$1');
$routes->get('/tag/(:segment)', 'PublicSite::tag/$1');
$routes->get('/search', 'PublicSite::search');
