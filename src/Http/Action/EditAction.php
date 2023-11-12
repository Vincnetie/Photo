<?php

namespace App\Http\Action;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class EditAction
{
    private Twig $twig;

    public function __construct(Twig $twig, PDO $pdo)
    {
        $this->twig = $twig;
        $this->pdo = $pdo;
    }

    public function __invoke(Request $request, Response $response): Response
    {

        $sql = "SELECT * FROM photos";
        $stmt = $this->pdo->query($sql);
        $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $data = [
            'name' => 'Hello Project Photo!',
            'photos' => $photos
        ];

        return $this->twig->render($response, 'edit.twig', $data);
    }
}