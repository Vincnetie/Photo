<?php

namespace App\Http\Action;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Slim\Http\UploadedFile;

class UploadAction
{
    private Twig $twig;
    private PDO $pdo;

    public function __construct(Twig $twig, PDO $pdo)
    {
        $this->twig = $twig;
        $this->pdo = $pdo;
    }

    private function createPhotosTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS photos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

        $this->pdo->exec($sql);
    }

    public function __invoke(Request $request, Response $response): Response
    {

        // Создание таблицы photos, если она отсутствует
        $this->createPhotosTable();

        if ($request->getMethod() === 'GET') {

            $data = [
                'name' => 'Upload File',
            ];

            return $this->twig->render($response, 'upload.twig', $data);
        }

        // Выполнение запроса к базе данных
        //$statement = $this->pdo->query('SELECT * FROM mytable');
        //$results = $statement->fetchAll(PDO::FETCH_ASSOC);

        $uploadedFiles = $request->getUploadedFiles();

        // Проверяем, есть ли файлы для загрузки
        if (empty($uploadedFiles['file'])) {
            $response = $response->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode(['error' => 'Нет файлов для загрузки']));
            return $response->withStatus(400);
        }

        $file = $uploadedFiles['file'];

        // Проверяем, возникли ли ошибки при загрузке файла
        if ($file->getError() !== UPLOAD_ERR_OK) {
            $response = $response->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode(['error' => 'Ошибка при загрузке файла']));
            return $response->withStatus(500);
        }

        // Перемещаем загруженный файл в нужную директорию на сервере
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/img/';
        $fileName = $file->getClientFilename();
        $file->moveTo($uploadPath . $fileName);

        // Возвращаем успешный ответ с информацией о загруженном файле
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode(['success' => 'Файл успешно загружен', 'file' => $fileName]));
        return $response->withStatus(200);

    }
}