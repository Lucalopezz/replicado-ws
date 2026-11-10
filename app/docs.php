<?php

// As rotas são registradas após o controle de IP em www/index.php.
Flight::route('GET /openapi.json', function () {
    $spec = (new \OpenApi\Generator())
        ->setVersion('3.0.3')
        ->generate([__DIR__ . '/../src/OpenApi']);
    $spec->servers = [new \OpenApi\Attributes\Server(
        url: rtrim(DOMINIO, '/'),
        description: 'Esta instalação da API',
    )];
    Flight::json($spec, 200, true, 'utf-8', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
});

Flight::route('GET /docs', function () {
    $html = file_get_contents(__DIR__ . '/../vendor/swagger-api/swagger-ui/dist/index.html');
    // O endereço base resolve os arquivos do pacote tanto em /docs quanto em /docs/.
    $base = htmlspecialchars(rtrim(DOMINIO, '/') . '/docs/', ENT_QUOTES, 'UTF-8');
    Flight::response()
        ->header('Content-Type', 'text/html; charset=utf-8')
        ->write(str_replace('<head>', '<head><base href="' . $base . '">', $html))
        ->send();
});

Flight::route('GET /docs/@asset', function ($asset) {
    if ($asset === 'swagger-initializer.js') {
        $config = json_encode([
            'url' => rtrim(DOMINIO, '/') . '/openapi.json',
            'dom_id' => '#swagger-ui',
            'deepLinking' => true,
            'displayRequestDuration' => true,
            'persistAuthorization' => false,
            'validatorUrl' => null,
            'supportedSubmitMethods' => ['get'],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
        $content = 'window.onload = function () { window.ui = SwaggerUIBundle(' . $config . '); };';
        $type = 'application/javascript; charset=utf-8';
    } else {
        // Somente os arquivos usados pela página oficial podem ser acessados.
        $assets = [
            'swagger-ui.css' => 'text/css; charset=utf-8',
            'index.css' => 'text/css; charset=utf-8',
            'swagger-ui-bundle.js' => 'application/javascript; charset=utf-8',
            'swagger-ui-standalone-preset.js' => 'application/javascript; charset=utf-8',
            'favicon-16x16.png' => 'image/png',
            'favicon-32x32.png' => 'image/png',
        ];
        if (!isset($assets[$asset])) {
            Flight::notFound();
            return;
        }
        $content = file_get_contents(__DIR__ . '/../vendor/swagger-api/swagger-ui/dist/' . $asset);
        $type = $assets[$asset];
    }

    Flight::response()
        ->header('Content-Type', $type)
        ->write($content)
        ->send();
});
