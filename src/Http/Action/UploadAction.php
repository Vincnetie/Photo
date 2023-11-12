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
        $photosTableExists = $this->pdo->query("SHOW TABLES LIKE 'photos'")->rowCount() > 0;

        if (!$photosTableExists) {
            $sql = "CREATE TABLE `photos` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `point` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

            $this->pdo->exec($sql);
        }

        $existingRecords = $this->pdo->query("SELECT COUNT(*) FROM `photos`")->fetchColumn();

        if ($existingRecords == 0) {
            $sql = "INSERT INTO `photos` (`id`, `name`, `point`) VALUES
            (1, 'Тверская 9', '55.75985606898725,37.61054750000002'),
            (2, 'Тверская, 20', '55.766642568974845,37.60237299999997'),
            (3, 'Охотный Ряд, 1', '55.75805306898262,37.6160005'),
            (4, 'Солянка, 16', '55.75061056899327,37.64180899999995')";

            $this->pdo->exec($sql);
        }
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