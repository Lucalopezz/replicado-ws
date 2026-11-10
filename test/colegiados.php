<?php

// Integração das rotas e consultas reais com múltiplos colegiados, sem banco ou Memcached.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

error_reporting(E_ALL);
set_error_handler(static function ($severity, $message, $file, $line): void {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
require dirname(__DIR__) . '/vendor/autoload.php';

use Uspdev\Replicado\Replicado;
use Uspdev\Replicado_ws\Colegiados;

$failed = 0;
$total = 0;
$check = static function (bool $ok, string $name) use (&$failed, &$total): void {
    echo ($ok ? '[OK] ' : '[FALHA] ') . $name . PHP_EOL;
    $failed += $ok ? 0 : 1;
    $total++;
};

$config = Replicado::getInstance([
    'host' => '127.0.0.1', 'port' => 1433, 'database' => 'fixture',
    'username' => 'fixture', 'password' => 'fixture', 'usarCache' => false,
]);
foreach ([
    ['18', '18,97', [18, 97]],
    ['18', ' 18, 97,18 ', [18, 97]],
    ['18', '97', [97]],
    ['18', '', [18]],
    ['18,97', null, [18, 97]],
] as [$singular, $plural, $expected]) {
    putenv('REPLICADO_CODUNDCLG=' . $singular);
    putenv($plural === null ? 'REPLICADO_CODUNDCLGS' : 'REPLICADO_CODUNDCLGS=' . $plural);
    $check(Colegiados::consultar(fn ($codigo) => [$codigo]) === $expected, 'Normaliza configuração ' . json_encode([$singular, $plural]));
}
foreach (['', '18,,97', '18,', '0', '-18', '18.5', '18,97 OR 1=1'] as $invalid) {
    putenv('REPLICADO_CODUNDCLG=');
    putenv('REPLICADO_CODUNDCLGS=' . $invalid);
    try {
        Colegiados::consultar(fn ($codigo) => [$codigo]);
        $check(false, 'Rejeita configuração inválida ' . $invalid);
    } catch (InvalidArgumentException $error) {
        $check(true, 'Rejeita configuração inválida ' . $invalid);
    }
}

putenv('REPLICADO_CODUNDCLG=18');
putenv('REPLICADO_CODUNDCLGS=18,97');
$original = [$config->codundclg, $config->codundclgs, getenv('REPLICADO_CODUNDCLG'), getenv('REPLICADO_CODUNDCLGS')];
try {
    Colegiados::consultar(static function ($codigo) {
        throw new RuntimeException('Erro simulado de consulta');
    });
} catch (RuntimeException $error) {
    $check($original === [$config->codundclg, $config->codundclgs, getenv('REPLICADO_CODUNDCLG'), getenv('REPLICADO_CODUNDCLGS')], 'Restaura ambiente e biblioteca após erro');
}
$check(Colegiados::consultar(fn ($codigo) => $codigo === 18 ? [500 => [['codare' => 1]]] : []) === [500 => [['codare' => 1]]], 'Preserva mapa de cursos quando outro colegiado não tem resultados');
$check(Colegiados::verificar(fn ($codigo) => $codigo === 97), 'Verificação encontra vínculo no segundo colegiado');
$check(!Colegiados::verificar(fn ($codigo) => false), 'Verificação retorna falso quando nenhum colegiado possui vínculo');

$people = static fn ($data) => is_array($data) && count($data) === 3
    && array_column($data, 'codundclg') === [18, 97];
$areas = static fn ($data) => isset($data[500]) && count($data[500]) === 3
    && array_column($data[500], 'codare') === [5001, 1800, 9700];
$cases = [
    ['/pessoa/docentes', $people],
    ['/pessoa/servidores', $people],
    ['/pessoa/estagiarios', $people],
    ['/pessoa/docentes/seniores', $people],
    ['/posgraduacao/ativos', $people],
    ['/posgraduacao/verifica/123', static fn ($data) => $data === true],
    ['/posgraduacao/programas', static fn ($data) => count($data) === 3 && array_column($data, 'codcur') === [180, 500, 970]],
    ['/posgraduacao/programas/180', static fn ($data) => count($data) === 1 && $data[0]['codcur'] === 180],
    ['/posgraduacao/areasProgramas/500', $areas],
    ['/posgraduacao/areasProgramas/180', static fn ($data) => isset($data[180]) && count($data[180]) === 2],
    ['/posgraduacao/alunosPrograma/500', static fn ($data) => count($data) === 4 && array_column($data, 'codpes') === [18, 18, 97, 97]],
    ['/posgraduacao/alunosPrograma/180', static fn ($data) => count($data) === 2 && array_column($data, 'codpes') === [18, 18]],
    ['/posgraduacao/alunosPrograma/999', static fn ($data) => $data === []],
];
foreach ([false, true] as $cache) {
    foreach ($cases as [$path, $assert]) {
        $case = [
            'path' => $path, 'password' => 'senha-de-teste', 'cache' => $cache,
            'colegiados' => ['REPLICADO_CODUNDCLG' => '18', 'REPLICADO_CODUNDCLGS' => '18,97'],
        ];
        $process = proc_open([PHP_BINARY, __DIR__ . '/flight_compatibility.php', '--request', base64_encode(json_encode($case))],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        fclose($pipes[0]);
        $body = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);
        $metadata = json_decode(trim($stderr), true);
        $data = json_decode($body, true);
        $ok = $exit === 0 && ($metadata['status'] ?? null) === 200
            && ($metadata['headers']['Content-Type'] ?? '') === 'application/json; charset=utf-8'
            && json_last_error() === JSON_ERROR_NONE && $assert($data);
        $check($ok, $path . ' com cache ' . ($cache ? 'habilitado' : 'desabilitado'));
        if (!$ok) {
            fwrite(STDERR, $body . PHP_EOL . $stderr . PHP_EOL);
        }
    }
}
echo $total . ' cenários, ' . $failed . ' falhas.' . PHP_EOL;
exit($failed === 0 ? 0 : 1);
