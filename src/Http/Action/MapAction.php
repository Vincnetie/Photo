<?php

namespace App\Http\Action;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class MapAction
{
    private Twig $twig;

    public function __construct(Twig $twig, PDO $pdo)
    {
        $this->twig = $twig;
        $this->pdo = $pdo;
    }

    public function __invoke(Request $request, Response $response): Response
    {

        $sth = $this->pdo->prepare("SELECT * FROM `photos` ORDER BY `name`");
        $sth->execute();
        $list = $sth->fetchAll(PDO::FETCH_ASSOC);

        $data = [
            'list' => $list
        ];

        return $this->twig->render($response, 'map.twig', $data);
    }
}