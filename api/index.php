<?php

if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])) {
    $_ENV['LARAVEL_STORAGE_PATH'] = $_ENV['LARAVEL_STORAGE_PATH'] ?? '/tmp/storage';
    $_SERVER['LARAVEL_STORAGE_PATH'] = $_SERVER['LARAVEL_STORAGE_PATH'] ?? '/tmp/storage';

    foreach ([
        '/tmp/storage/app',
        '/tmp/storage/framework/cache',
        '/tmp/storage/framework/sessions',
        '/tmp/storage/framework/views',
        '/tmp/storage/logs',
    ] as $directory) {
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }
}

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$publicFile = realpath(__DIR__.'/../public'.$requestPath);
$publicRoot = realpath(__DIR__.'/../public');

if ($publicFile && $publicRoot && str_starts_with($publicFile, $publicRoot) && is_file($publicFile)) {
    header('Content-Type: '.(mime_content_type($publicFile) ?: 'application/octet-stream'));
    header('Content-Length: '.filesize($publicFile));
    readfile($publicFile);
    return;
}

require __DIR__.'/../public/index.php';
