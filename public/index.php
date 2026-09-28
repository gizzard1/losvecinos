<?php

declare(strict_types=1);

/**
 * Front controller - Punto de entrada único
 * Rutas:
 * - / → public/index.html (landing)
 */

require_once __DIR__ . '/../config/bootstrap.php';

$baseDir = dirname(__DIR__);
$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . ltrim($uriPath, '/');

// Si la app está en subcarpeta, removemos ese prefijo del path.
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($scriptDir !== '' && $scriptDir !== '/' && str_starts_with($path, $scriptDir . '/')) {
    $path = substr($path, strlen($scriptDir));
    $path = $path === '' ? '/' : $path;
}

switch ($path) {
    case '/':
    case '/index.php':
    case '/index.html':
        header('Content-Type: text/html; charset=UTF-8');
        if (is_file($baseDir . '/public/index.html')) {
            readfile($baseDir . '/public/index.html');
        } else {
            http_response_code(404);
            echo 'Home not found.';
        }
        exit;

    default:
        // Si existe archivo real (assets, html, etc.), se entrega directo.
        $candidate = realpath($baseDir . $path);
        $isInsideBaseDir = $candidate !== false && str_starts_with($candidate, $baseDir . DIRECTORY_SEPARATOR);

        if ($isInsideBaseDir && is_file($candidate)) {
            $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
            if ($ext === 'html') {
                header('Content-Type: text/html; charset=UTF-8');
            }
            readfile($candidate);
            exit;
        }

        http_response_code(404);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!doctype html><html lang="en"><head><meta charset="UTF-8"><title>404</title></head><body><h1>404</h1><p>Route not found.</p></body></html>';
        exit;
}
