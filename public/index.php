<?php

declare(strict_types=1);

use App\Http;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Psr\Container\ContainerInterface;

http_response_code(500);

require __DIR__ . '/../vendor/autoload.php';

$builder = new ContainerBuilder();

$builder->addDefinitions([
    'config' => [
        'debug' => true,
    ],
    Twig::class => function (ContainerInterface $container) {
        $twig = Twig::create(__DIR__ . '/../templates', [
            'cache' => false,
        ]);
        return $twig;
    },
    'view' => DI\get(Twig::class), // Register the 'view' key with Twig instance
]);

$container = $builder->build();

$app = AppFactory::createFromContainer($container);

$app->addErrorMiddleware($container->get('config')['debug'], true, true);

$app->add(TwigMiddleware::createFromContainer($app));

$app->get('/', Http\Action\HomeAction::class);

$app->run();