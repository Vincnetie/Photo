<?php

namespace App\Http\Action;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class DeleteAction
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
        // Получаем значение параметра id из URL
        $id = $args['id'];

        // Проверяем, есть ли фотография с заданным id
        $statement = $this->pdo->prepare('SELECT * FROM photos WHERE id = :id');
        $statement->bindValue(':id', $id);
        $statement->execute();
        $photo = $statement->fetch();

        // Если фотография не найдена, выводим предупреждение
        if (!$photo) {
            return $this->twig->render($response, 'error.twig', [
                'message' => 'Фотография не найдена'
            ])->withStatus(404);
        }

        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/img/';
        $fileName = $photo['image'];
        $filePath = $uploadPath . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Выполняем операцию удаления фотографии из базы данных
        $statement = $this->pdo->prepare('DELETE FROM photos WHERE id = :id');
        $statement->bindValue(':id', $id);
        $statement->execute();

        // Перенаправляем пользователя на страницу со списком фотографий
        return $response->withHeader('Location', '/photos')->withStatus(302);
    }
}