<?php

declare(strict_types=1);

use App\Http;
use Slim\Factory\AppFactory;

http_response_code(500);

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->addErrorMiddleware(false, true, true);

$app->get('/', Http\Action\HomeAction::class);

$app->run();