<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group([
    'prefix' => 'api'
], function () use ($router) {
    $router->post('auth/login', 'AuthController@login');

    # With Auth
    $router->group([
        'middleware' => 'auth'
    ], function () use ($router) {
        $router->get('auth/me', 'AuthController@me');
        $router->get('auth/refresh', 'AuthController@refresh');
        $router->post('auth/logout', 'AuthController@logout');

        # Category
        # route untuk menampilkan semua Category dengan method GET
        $router->get('categories', 'CategoryController@index');
        # route untuk menampilkan satu data Category dengan method GET
        $router->get('categories/{id}', 'CategoryController@show');

        # Meja
        # route untuk menampilkan semua Meja dengan method GET
        $router->get('tables', 'TableController@index');
        # route untuk menampilkan satu data Meja dengan method GET
        $router->get('tables/{id}', 'TableController@show');

        # Item
        # route untuk menampilkan semua Item dengan method GET
        $router->get('items', 'ItemController@index');
        # route untuk menampilkan satu data Item dengan method GET
        $router->get('items/{id}', 'ItemController@show');

        # Transaction
        # route untuk menampilkan semua Transaksi dengan method GET
        $router->get('transactions', 'TransactionController@index');
        # route untuk menampilkan satu data Transaksi dengan method GET
        $router->get('transactions/{invoice}', 'TransactionController@show');

        $router->get('dashboard', 'DashboardController');

        $router->get('reports/income-report', 'IncomeReportController');
    });

    # Middleware auth & role kasir
    $router->group([
        'middleware' => ['auth', 'role:kasir']
    ], function () use ($router) {
        # Transaction
        # route untuk membuat Transaksi dengan method POST
        $router->post('transactions', 'TransactionController@store');
        # route untuk merubah data Transaksi dengan method PUT
        $router->put('transactions/{id}', 'TransactionController@update');
        # route untuk merubah status pending ke success Transaksi dengan method PUT
        $router->put('transactions/{id}/status', 'TransactionController@updateStatus');
        # route untuk menghapus data Transaksi dengan method DELETE
        $router->delete('transactions/{id}', 'TransactionController@destroy');
    });

    # Middleware auth & role admin
    $router->group([
        'middleware' => ['auth', 'role:admin']
    ], function () use ($router) {
        # Category
        # route untuk membuat Category dengan method POST
        $router->post('categories', 'CategoryController@store');
        # route untuk merubah data Category dengan method PUT
        $router->put('categories/{id}', 'CategoryController@update');
        # route untuk menghapus data Category dengan method DELETE
        $router->delete('categories/{id}', 'CategoryController@destroy');

        # Meja
        $router->post('tables', 'TableController@store');
        # route untuk merubah data Meja dengan method PUT
        $router->put('tables/{id}', 'TableController@update');
        # route untuk menghapus data Meja dengan method DELETE
        $router->delete('tables/{id}', 'TableController@destroy');

        # Item
        # route untuk membuat Item dengan method POST
        $router->post('items', 'ItemController@store');
        # route untuk merubah data Item dengan method PUT
        $router->put('items/{id}', 'ItemController@update');
        # route untuk menghapus data Item dengan method DELETE
        $router->delete('items/{id}', 'ItemController@destroy');
    });
});
