<?php

use App\Admin\Controllers\PostController;
use App\Admin\Controllers\SettingsController;
use App\Admin\Controllers\TaxonomyController;
use App\Admin\Controllers\TaxonomyItemController;
use Encore\Admin\Facades\Admin;
use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {
    $router->resource('taxonomies', TaxonomyController::class);
    $router->resource('taxonomy-items', TaxonomyItemController::class);
    $router->resource('posts', PostController::class);
    $router->resource('settings', SettingsController::class);
    $router->resource('numbers', \App\Admin\Controllers\NumberController::class);
    $router->resource('category', \App\Admin\Controllers\CategoryController::class);
    $router->resource('keys', \App\Admin\Controllers\KeysController::class);
    $router->resource('soicaus', \App\Admin\Controllers\SoicauController::class);
    $router->resource('menus', \App\Admin\Controllers\MenuController::class);
    $router->resource('pages', \App\Admin\Controllers\PageController::class);
    $router->resource('tags', \App\Admin\Controllers\TagController::class);
    $router->get('/', 'HomeController@index')->name('home');
});
