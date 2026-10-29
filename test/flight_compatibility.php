<?php

// Testa o Flight com bootstrap, rotas e autenticação reais, sem banco nem Memcached.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

if (($argv[1] ?? '') === '--request') {
    $case = json_decode(base64_decode($argv[2]), true, 512, JSON_THROW_ON_ERROR);
    error_reporting(E_ALL);
    set_error_handler(static function ($severity, $message, $file, $line): void {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    $base = $case['base'] ?? '/api';
    define('DOMINIO', 'https://example.test' . $base);
    define('IP_ACCESS_LIST', [['127.0.0.1', '32']]);
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = $base . $case['path'];
    $_SERVER['SCRIPT_NAME'] = $base . '/index.php';
    $_SERVER['REMOTE_ADDR'] = $case['ip'] ?? '127.0.0.1';
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    parse_str(parse_url($case['path'], PHP_URL_QUERY) ?? '', $_GET);
    $users = ['teste' => md5('senha-de-teste')];
    if (isset($case['password'])) {
        $_SERVER['PHP_AUTH_USER'] = 'teste';
        $_SERVER['PHP_AUTH_PW'] = $case['password'];
    }
    foreach (['HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION'] as $header) {
        if (isset($case[$header])) {
            $_SERVER[$header] = $case[$header];
        }
    }

    require dirname(__DIR__) . '/vendor/autoload.php';
    Flight::set('flight.handle_errors', false);
    require dirname(__DIR__) . '/app/bootstrap.php';
    register_shutdown_function(static function (): void {
        fwrite(STDERR, json_encode([
            'status' => http_response_code() ?: Flight::response()->status(),
            'headers' => Flight::response()->headers(),
        ]) . PHP_EOL);
    });

    // Substitui apenas a consulta: captura os argumentos recebidos pela rota.
    $c = new class {
        public function getCached($method, $params): array
        {
            return ['method' => $method, 'params' => $params];
        }
    };

    Flight::route('*', static function () {
        Uspdev\Replicado_ws\Auth::ip_control();
        return true;
    });
    Flight::route('/login', [Uspdev\Replicado_ws\Auth::class, 'login']);
    Flight::route('/logout', [Uspdev\Replicado_ws\Auth::class, 'logout']);
    $app = explode('/', parse_url($case['path'], PHP_URL_PATH))[1];
    if (in_array($app, ['pessoa', 'posgraduacao', 'graduacao', 'lattes', 'bempatrimoniado'], true)) {
        require dirname(__DIR__) . '/app/' . $app . '.php';
    }
    Flight::start();
    exit;
}

$cases = [
    ['path' => '/pessoa', 'base' => '', 'status' => 200, 'key' => 'dump'],
    ['path' => '/nulo', 'base' => '', 'status' => 404, 'message' => 'Not Found'],
    ['path' => '/posgraduacao/ativos', 'base' => '', 'status' => 200, 'message' => 'Requires Authentication'],
    ['path' => '/pessoa', 'status' => 200, 'key' => 'dump'],
    ['path' => '/posgraduacao', 'status' => 200, 'key' => 'ativos'],
    ['path' => '/lattes', 'status' => 200, 'key' => 'idLattes'],
    ['path' => '/bempatrimoniado', 'status' => 200, 'key' => 'dump'],
    ['path' => '/nulo', 'status' => 404, 'message' => 'Not Found'],
    ['path' => '/posgraduacao/nulo/nulo', 'status' => 404, 'message' => 'Not Found'],
    ['path' => '/posgraduacao/orientadores/texto', 'status' => 404, 'message' => 'Not Found'],
    ['path' => '/pessoa/nome', 'status' => 404, 'message' => 'Not Found'],
    ['path' => '/posgraduacao/ativos', 'status' => 200, 'message' => 'Requires Authentication'],
    ['path' => '/posgraduacao/verifica/123', 'status' => 200, 'message' => 'Requires Authentication'],
    ['path' => '/pessoa/dump/123', 'password' => 'incorreta', 'status' => 200, 'message' => 'Requires Authentication'],
    ['path' => '/pessoa/dump/123', 'password' => 'senha-de-teste', 'status' => 200, 'params' => '123'],
    ['path' => '/pessoa/nome?q=Jo%C3%A3o', 'password' => 'senha-de-teste', 'status' => 200, 'method' => '\\Uspdev\\Replicado\\Pessoa::procurarPorNome', 'params' => ['João', false, false]],
    ['path' => '/pessoa', 'ip' => '192.0.2.1', 'status' => 403, 'message' => 'Forbidden'],
    ['path' => '/login', 'status' => 401, 'text' => 'Você deve digitar um login e senha válidos para acessar este recurso\\n'],
    ['path' => '/login', 'password' => 'incorreta', 'status' => 401, 'text' => 'Credenciais inválidas'],
    ['path' => '/login', 'password' => 'senha-de-teste', 'status' => 200, 'text' => 'Login success'],
    ['path' => '/login', 'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('teste:senha-de-teste'), 'status' => 200, 'text' => 'Login success'],
    ['path' => '/login', 'REDIRECT_HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('teste:senha-de-teste'), 'status' => 200, 'text' => 'Login success'],
    ['path' => '/pessoa/dump/123', 'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('teste:senha-de-teste'), 'status' => 200, 'params' => '123'],
    ['path' => '/pessoa/dump/123', 'REDIRECT_HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('teste:senha-de-teste'), 'status' => 200, 'params' => '123'],
    ['path' => '/login', 'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('teste:incorreta'), 'status' => 401, 'text' => 'Credenciais inválidas'],
    ['path' => '/login', 'HTTP_AUTHORIZATION' => 'Basic !!!', 'status' => 401, 'text' => 'Você deve digitar um login e senha válidos para acessar este recurso\\n'],
    ['path' => '/login', 'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('teste'), 'status' => 401, 'text' => 'Você deve digitar um login e senha válidos para acessar este recurso\\n'],
    ['path' => '/login', 'HTTP_AUTHORIZATION' => 'Bearer token', 'status' => 401, 'text' => 'Você deve digitar um login e senha válidos para acessar este recurso\\n'],
    ['path' => '/login', 'password' => 'incorreta', 'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('teste:senha-de-teste'), 'status' => 401, 'text' => 'Credenciais inválidas'],
    ['path' => '/logout', 'status' => 401, 'text' => 'logout'],
];

$failed = 0;
foreach ($cases as $case) {
    $domain = 'https://example.test' . ($case['base'] ?? '/api');
    $process = proc_open([
        PHP_BINARY, __FILE__, '--request', base64_encode(json_encode($case)),
    ], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) {
        fwrite(STDERR, "Não foi possível iniciar o processo de teste.\n");
        exit(1);
    }
    fclose($pipes[0]);
    $body = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);
    $response = json_decode(trim($stderr), true);
    $ok = $exitCode === 0 && ($response['status'] ?? null) === $case['status'];
    if (isset($case['text'])) {
        $ok = $ok && $body === $case['text'];
    } else {
        $data = json_decode($body, true);
        $ok = $ok && json_last_error() === JSON_ERROR_NONE
            && ($response['headers']['Content-Type'] ?? '') === 'application/json; charset=utf-8';
        if (isset($case['message'])) {
            $ok = $ok && $data === [
                'message' => $case['message'],
                'documentation_url' => $domain . '/',
            ];
        }
        if (isset($case['key'])) {
            $ok = $ok && isset($data[$case['key']]['url'])
                && strpos($data[$case['key']]['url'], $domain . '/') === 0;
        }
        if (isset($case['params'])) {
            $ok = $ok && ($data['params'] ?? null) === $case['params'];
        }
        if (isset($case['method'])) {
            $ok = $ok && ($data['method'] ?? null) === $case['method'];
        }
    }
    echo ($ok ? '[OK] ' : '[FALHA] ') . $case['path'] . PHP_EOL;
    if (!$ok) {
        $failed++;
        fwrite(STDERR, 'Saída: ' . $body . PHP_EOL . 'Diagnóstico: ' . $stderr . PHP_EOL);
    }
}
echo count($cases) . ' cenários, ' . $failed . ' falhas.' . PHP_EOL;
exit($failed === 0 ? 0 : 1);
