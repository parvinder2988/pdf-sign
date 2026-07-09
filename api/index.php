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

if ($requestPath === '/__diagnostics') {
    header('Content-Type: application/json');
    echo json_encode([
        'php_version' => PHP_VERSION,
        'vercel' => isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']),
        'app_key_set' => (bool) ($_ENV['APP_KEY'] ?? $_SERVER['APP_KEY'] ?? getenv('APP_KEY')),
        'app_env' => $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? getenv('APP_ENV') ?: null,
        'app_debug' => $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: null,
        'app_url_set' => (bool) ($_ENV['APP_URL'] ?? $_SERVER['APP_URL'] ?? getenv('APP_URL')),
        'db_connection' => $_ENV['DB_CONNECTION'] ?? $_SERVER['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: null,
        'db_url_set' => (bool) ($_ENV['DB_URL'] ?? $_SERVER['DB_URL'] ?? getenv('DB_URL')),
        'session_driver' => $_ENV['SESSION_DRIVER'] ?? $_SERVER['SESSION_DRIVER'] ?? getenv('SESSION_DRIVER') ?: null,
        'cache_store' => $_ENV['CACHE_STORE'] ?? $_SERVER['CACHE_STORE'] ?? getenv('CACHE_STORE') ?: null,
        'tmp_writable' => is_writable('/tmp'),
        'vendor_autoload_exists' => is_file(__DIR__.'/../vendor/autoload.php'),
        'public_index_exists' => is_file(__DIR__.'/../public/index.php'),
    ], JSON_PRETTY_PRINT);
    return;
}

if ($requestPath === '/__laravel-debug') {
    header('Content-Type: application/json');

    try {
        require __DIR__.'/../vendor/autoload.php';

        $app = require __DIR__.'/../bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        $request = Illuminate\Http\Request::create('/', 'GET');
        $response = $kernel->handle($request);

        echo json_encode([
            'status' => $response->getStatusCode(),
            'content_type' => $response->headers->get('Content-Type'),
            'content_length' => strlen($response->getContent()),
            'storage_path' => storage_path(),
            'view_compiled' => config('view.compiled'),
            'session_driver' => config('session.driver'),
            'cache_default' => config('cache.default'),
        ], JSON_PRETTY_PRINT);

        $kernel->terminate($request, $response);
    } catch (Throwable $exception) {
        echo json_encode([
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ], JSON_PRETTY_PRINT);
    }

    return;
}

$publicFile = realpath(__DIR__.'/../public'.$requestPath);
$publicRoot = realpath(__DIR__.'/../public');

if ($publicFile && $publicRoot && str_starts_with($publicFile, $publicRoot) && is_file($publicFile)) {
    header('Content-Type: '.(mime_content_type($publicFile) ?: 'application/octet-stream'));
    header('Content-Length: '.filesize($publicFile));
    readfile($publicFile);
    return;
}

require __DIR__.'/../public/index.php';
