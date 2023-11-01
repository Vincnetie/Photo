<?php

namespace App\Http\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Slim\Http\UploadedFile;

class UploadAction
{
    private Twig $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'GET') {

            $data = [
                'name' => 'Upload File',
            ];

            return $this->twig->render($response, 'upload.twig', $data);
        }

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