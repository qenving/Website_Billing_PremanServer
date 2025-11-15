<?php

declare(strict_types=1);

require __DIR__ . '/../src/Support/autoload.php';

use App\Application;

$app = new Application();
$response = $app->handle($_SERVER, file_get_contents('php://input') ?: '');

http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $value) {
    header($name . ': ' . $value);
}

echo $response->getBody();
