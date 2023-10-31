<?php

namespace App\Http\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class UploadAction
{
    private Twig $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $data = [
            'name' => 'Upload File',
        ];

        return $this->twig->render($response, 'upload.twig', $data);
    }
}