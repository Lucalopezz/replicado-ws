<?php

// Verifica cobertura e referências da documentação sem banco ou servidor HTTP.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
$spec = json_decode(file_get_contents($root . '/doc/openapi.json'), true, 512, JSON_THROW_ON_ERROR);
require $root . '/vendor/autoload.php';
$generated = (new \OpenApi\Generator())->setVersion('3.0.3')->generate([$root . '/src/OpenApi']);
$errors = [];
$check = static function (bool $ok, string $message) use (&$errors): void {
    if (!$ok) {
        $errors[] = $message;
    }
};

$check($spec['openapi'] === '3.0.3', 'Versão OpenAPI inesperada.');
$check($generated->validate(), 'A validação da biblioteca swagger-php falhou.');
$check(json_decode($generated->toJson(), true, 512, JSON_THROW_ON_ERROR) === $spec,
    'O arquivo exportado está desatualizado. Execute php vendor/bin/openapi --version 3.0.3 --output doc/openapi.json src/OpenApi.');
$operations = [];
foreach ($spec['paths'] as $path => $item) {
    $operation = $item['get'];
    $id = $operation['operationId'];
    $check(!isset($operations[$id]), 'operationId repetido: ' . $id);
    $operations[$id] = true;
    $check(!empty($operation['responses']), 'Respostas ausentes: ' . $path);
    preg_match_all('/\{([^}]+)\}/', $path, $matches);
    $names = [];
    foreach ($operation['parameters'] ?? [] as $parameter) {
        if (isset($parameter['$ref'])) {
            $parameter = $spec['components']['parameters'][basename($parameter['$ref'])];
        }
        if ($parameter['in'] === 'path') {
            $names[] = $parameter['name'];
            $check($parameter['required'] === true, 'Parâmetro de caminho opcional: ' . $path);
        }
    }
    sort($names);
    sort($matches[1]);
    $check($names === $matches[1], 'Parâmetros de caminho inconsistentes: ' . $path);
}

$walk = static function (array $node) use (&$walk, $spec, $check): void {
    if (isset($node['$ref'])) {
        $reference = $node['$ref'];
        $check(str_starts_with($reference, '#/'), 'Referência externa inesperada: ' . $reference);
        $target = $spec;
        foreach (explode('/', substr($reference, 2)) as $part) {
            $key = str_replace(['~1', '~0'], ['/', '~'], $part);
            if (!is_array($target) || !array_key_exists($key, $target)) {
                $check(false, 'Referência inexistente: ' . $reference);
                break;
            }
            $target = $target[$key];
        }
    }
    foreach ($node as $value) {
        if (is_array($value)) {
            $walk($value);
        }
    }
};
$walk($spec);

// Compara os padrões Flight com os caminhos OpenAPI, expandindo o segmento opcional.
$documented = array_fill_keys(array_map(static fn ($path) => rtrim($path, '/') ?: '/', array_keys($spec['paths'])), true);
$implemented = [];
foreach (array_merge(glob($root . '/app/*.php'), [$root . '/www/index.php']) as $file) {
    preg_match_all("/Flight::route\('([^']+)'/", file_get_contents($file), $matches);
    foreach ($matches[1] as $route) {
        // Os arquivos da interface Swagger UI não são operações da API.
        if ($route === '*' || $route === 'GET /docs/@asset') {
            continue;
        }
        $route = preg_replace('/^GET /', '', $route);
        $variants = str_contains($route, '(')
            ? [preg_replace('/\([^)]*\)/', '', $route), str_replace(['(', ')'], '', $route)]
            : [$route];
        foreach ($variants as $variant) {
            $path = preg_replace('/@([a-z]+):\[0-9\]\+/', '{$1}', $variant);
            $implemented[rtrim($path, '/') ?: '/'] = true;
        }
    }
}
foreach (array_diff_key($implemented, $documented) as $path => $_) {
    $check(false, 'Rota sem documentação: ' . $path);
}
foreach (array_diff_key($documented, $implemented) as $path => $_) {
    $check(false, 'Rota documentada sem implementação: ' . $path);
}

foreach (['Codpes', 'Nome', 'CodigoOuNome', 'Codare', 'Numpat'] as $name) {
    $parameter = $spec['components']['parameters'][$name];
    $check($parameter['example'] === '', 'Exemplo deveria estar vazio: ' . $name);
    $check(!array_key_exists('default', $parameter['schema']), 'Valor padrão inesperado: ' . $name);
}
$check($spec['components']['parameters']['Codcur']['example'] === 18070, 'Exemplo de codcur deve ser 18070.');
$check($spec['components']['securitySchemes']['basicAuth']['scheme'] === 'basic', 'Autenticação deve ser HTTP Basic.');

foreach ($errors as $error) {
    fwrite(STDERR, '[FALHA] ' . $error . PHP_EOL);
}
echo count($spec['paths']) . ' rotas documentadas; ' . count($errors) . ' falhas.' . PHP_EOL;
exit($errors ? 1 : 0);
