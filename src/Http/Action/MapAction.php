<?php

namespace App\Http\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class MapAction
{
    private Twig $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $data = [
            'name' => 'Hello Project Photo!',
            'point' => '55.75985606898725,37.61054750000002',
            'name' =>   'Тверская 9'
        ];

        return $this->twig->render($response, 'map.twig', $data);
    }
}