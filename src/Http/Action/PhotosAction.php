<?php

namespace App\Http\Action;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class PhotosAction
{
    private Twig $twig;
    private PDO $pdo;

    public function __construct(Twig $twig, PDO $pdo)
    {
        $this->twig = $twig;
        $this->pdo = $pdo;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        //$page = $request->getQueryParams()['page'] ?? 1;
        //$page = $args['page'] ?? 1;

        if (isset($args['page'])) {
            $page = $args['page'];
        } else {
            $page = 1; // Значение по умолчанию
        }


        $perPage = 2;

        // Calculate the offset based on the current page and number of items per page
        $offset = ($page - 1) * $perPage;

        // Query the database for a specific page of photos
        $sql = "SELECT * FROM photos LIMIT $perPage OFFSET $offset";
        $stmt = $this->pdo->query($sql);
        $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate the total number of pages
        $totalPhotos = $this->pdo->query("SELECT COUNT(*) FROM photos")->fetchColumn();
        $totalPages = ceil($totalPhotos / $perPage);

        $data = [
            'photos' => $photos,
            'current_page' => $page,
            'total_pages' => $totalPages
        ];

        return $this->twig->render($response, 'photos.twig', $data);
    }
}