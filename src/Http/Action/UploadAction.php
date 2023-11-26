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

    public function writeLog($message) {

        // Определяем имя файла и путь для сохранения
        $filename = 'exif_data.json'; // Имя файла
        $filePath = __DIR__ . '/../../../public/img/' . $filename; // Путь к текущей папке + имя файла

        $file = fopen($filePath, 'a');
        fwrite($file, $message . PHP_EOL);
        fclose($file);
    }

    public function getCoordinatesFromImage($path) {
        // Выполняем команду exiftool для получения метаданных
        $command = "exiftool -GPSLatitude -GPSLongitude " . escapeshellarg($path);
        $output = shell_exec($command);

        // Обрабатываем вывод команды, чтобы получить значения GPS Latitude и GPS Longitude
        $latitude = null;
        $longitude = null;
        if (preg_match("/GPS Latitude\s+:\s+([0-9.]+) deg ([0-9.]+)' ([0-9.]+)\" ([NS])/", $output, $latitudeMatches) &&
            preg_match("/GPS Longitude\s+:\s+([0-9.]+) deg ([0-9.]+)' ([0-9.]+)\" ([EW])/", $output, $longitudeMatches)) {
            $latitudeDegrees = (float) $latitudeMatches[1];
            $latitudeMinutes = (float) $latitudeMatches[2];
            $latitudeSeconds = (float) $latitudeMatches[3];
            $latitudeDirection = $latitudeMatches[4];

            $longitudeDegrees = (float) $longitudeMatches[1];
            $longitudeMinutes = (float) $longitudeMatches[2];
            $longitudeSeconds = (float) $longitudeMatches[3];
            $longitudeDirection = $longitudeMatches[4];

            // Преобразуем значения в градусы и десятичную долю градуса
            $latitude = $latitudeDegrees + ($latitudeMinutes / 60) + ($latitudeSeconds / 3600);
            if ($latitudeDirection === 'S') {
                $latitude = -$latitude;
            }

            $longitude = $longitudeDegrees + ($longitudeMinutes / 60) + ($longitudeSeconds / 3600);
            if ($longitudeDirection === 'W') {
                $longitude = -$longitude;
            }
            return [$latitude, $longitude];
        }
        return false;
    }

    public function getAddressFromCoordinates($latitude, $longitude) {
        if(empty($latitude) || empty($longitude)) {
            return 'Unable to retrieve address';
        }

        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$latitude}&lon={$longitude}";

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => 'User-Agent: YourAppName',
            ],
        ]);
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            $error = error_get_last();
            return 'Unable to retrieve address';
        }

        $this->writeLog($response);
        $data = json_decode($response, true);
        if (!empty($data['display_name'])) {
            return $data['display_name'];
        } else {
            return 'Unable to retrieve address';
        }
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

        $ext = strtolower(preg_replace("#.+\.([a-z]+)$#i", "$1", $fileName));
        $newFileName = sha1(uniqid()) . '.' . $ext;

        $file->moveTo($uploadPath . $newFileName);

        $coordinates = $this->getCoordinatesFromImage($path = $uploadPath . $newFileName);
        $latitude = $coordinates[0];
        $longitude = $coordinates[1];
        $name = $this->getAddressFromCoordinates($latitude,$longitude);

        $sql = "INSERT INTO `photos` (`name`, `point`, `image`) VALUES ('".$name."', '".$latitude.",".$longitude."','".$newFileName."')";
        $this->pdo->exec($sql);

        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode(['success' => 'Файл успешно загружен', 'file' => $fileName]));
        return $response->withStatus(200);


    }
}