<?php
use Cake\Routing\Router;

Router::plugin(
    'Upload',
    ['path' => '/upload'],
    function ($routes) {
        $routes->fallbacks('DashedRoute');
    }
);
