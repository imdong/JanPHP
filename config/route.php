<?php
/**
 * 路由配置
 */

use Framework\Route;

Route::get('/user/{uid}-{name?}', App\Controller\IndexController::class, 'info', [
    'uid' => '[0-9]+'
]);

return [
    '/'       => 'IndexController@index',
    '/submit' => 'GET,POST#IndexController@submit'
];
