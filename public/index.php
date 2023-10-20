<?php

declare(strict_types=1);

use App\Http;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Psr\Container\ContainerInterface;
use Twig\TwigFunction;

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

        // Регистрация помощника для функции asset
        $twig->getEnvironment()->addFunction(
            new TwigFunction('asset', function (string $path) {
                // Поддержка для файлов Bootstrap CSS
                if (strpos($path, 'app.css') !== false) {
                    return 'build/css/app.css';

                }

                // Поддержка для файлов Bootstrap JS
                if (strpos($path, 'app.js') !== false) {
                    return 'build/js/app.js';
                }

                // Возвращает остальные файлы
                //return '/path/to/public/' . ltrim($path, '/');
            })
        );

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