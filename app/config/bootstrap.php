<?php

define("BASE_DIR", __DIR__ . "/../../");
require_once(BASE_DIR . "/vendor/autoload.php");
require_once(BASE_DIR . "/app/config/config.php");

use Knp\Provider\ConsoleServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;

$app = new Silex\Application();
$app['config'] = $config;
$app['debug'] = isset($app['config']['debug']) ? $app['config']['debug'] : false;

$app->register(new ConsoleServiceProvider(), [
    'console.name'              => 'qiita-mirror',
    'console.version'           => '1.0.0',
    'console.project_directory' => BASE_DIR,
]);

$app->register(new Silex\Provider\TwigServiceProvider(), [
  'twig.path'       => BASE_DIR . '/app/views',
  'twig.class_path' => BASE_DIR . '/silex/vendor/twig/lib',
]);

$app->get('/', function() use($app) {
    $backupPath = $app['config']['backup_path'];
    // TODO Model class
    $filename = sprintf('%s/projects/list.json', $backupPath);
    if (!file_exists($filename)) {
        $app->abort(404, "data is not found");
    }

    $projects = json_decode(file_get_contents($filename), true);

    $filename = sprintf('%s/items/list.json', $backupPath);
    if (!file_exists($filename)) {
        $app->abort(404, "data is not found");
    }

    $items = json_decode(file_get_contents($filename), true);

    return $app['twig']->render('index.html.twig', [
        'projects' => $projects,
        'items' => $items,
    ]);
});

$app->get('/projects/{id}', function($id) use($app) {
    $backupPath = $app['config']['backup_path'];
    $filename = sprintf('%s/projects/%s.json', $backupPath, $id);
    if (!file_exists($filename)) {
        $app->abort(404, "data is not found");
    }
    $project = json_decode(file_get_contents($filename), true);

    return $app['twig']->render('project.html.twig', [
        'project' => $project,
    ]);
})->assert('id', '^[a-zA-Z0-9]+$');



$app->get('/items/{id}', function($id) use($app) {
    $backupPath = $app['config']['backup_path'];
    $filename = sprintf('%s/items/%s.json', $backupPath, $id);
    if (!file_exists($filename)) {
        $app->abort(404, "data is not found");
    }

    $item = json_decode(file_get_contents($filename), true);

    return $app['twig']->render('item.html.twig', [
        'item' => $item,
    ]);
})->assert('id', '^[a-zA-Z0-9]+$');

$app->get('{username}/items/{id}', function($username, $id) use ($app) {
    return $app->redirect(sprintf('/items/%s', $id));
})->assert('id', '^[a-zA-Z0-9]+$');

return $app;
