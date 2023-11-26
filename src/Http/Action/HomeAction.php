<?php

namespace App\Http\Action;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class HomeAction
{
    private Twig $twig;

    public function __construct(Twig $twig, PDO $pdo)
    {
        $this->twig = $twig;
        $this->pdo = $pdo;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        // Создание таблицы photos, если она отсутствует
        $this->createPhotosTable();
        $data = [
            'name' => 'Hello Project Photo!',
        ];

        return $this->twig->render($response, 'home.twig', $data);
    }


    private function createPhotosTable(): void
    {
        $photosTableExists = $this->pdo->query("SHOW TABLES LIKE 'photos'")->rowCount() > 0;

        if (!$photosTableExists) {
            $sql = "CREATE TABLE `photos` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `point` varchar(255) NOT NULL,
            `image` varchar(255) NOT NULL
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

            $this->pdo->exec($sql);
        }

        $existingRecords = $this->pdo->query("SELECT COUNT(*) FROM `photos`")->fetchColumn();

        if ($existingRecords == 0) {
            $sql = "INSERT INTO `photos` (`id`, `name`, `point`, `image`) VALUES
            (1, 'Тверская 9', '55.75985606898725,37.61054750000002', ''),
            (2, 'Тверская, 20', '55.766642568974845,37.60237299999997', ''),
            (3, 'Охотный Ряд, 1', '55.75805306898262,37.6160005', ''),
            (4, 'Солянка, 16', '55.75061056899327,37.64180899999995', '')";

            $this->pdo->exec($sql);
        }
    }

}