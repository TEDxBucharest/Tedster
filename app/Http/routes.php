<?php

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

$app->get('/', [
    'as' => 'index', 'uses' => 'IndexController@welcome'
]);

$app->post('/upload', [
    'as' => 'upload', 'uses' => 'IndexController@upload'
]);

$app->get('pictures/profile_{id}.jpg', ['as' => 'picture', function($id) {
    $path = storage_path('app') . '/profile_' . $id . '.jpg';
    if (file_exists($path)) {
        return response(readfile($path, 200))->header('Content-Type', 'image/jpg');
    }
}]);
