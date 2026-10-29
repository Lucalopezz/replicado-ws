<?php

// Verifica a resposta inicial real e o funcionamento com cache desabilitado.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

error_reporting(E_ALL);
set_error_handler(static function ($severity, $message, $file, $line): void {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

if (($argv[1] ?? '') === '--home') {
    require dirname(__DIR__) . '/config/config.php';
    putenv('USPDEV_CACHE_DISABLE=1');
    putenv('REPLICADO_USAR_CACHE=false');
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = (parse_url(DOMINIO, PHP_URL_PATH) ?: '') . '/';
    $_SERVER['SCRIPT_NAME'] = (parse_url(DOMINIO, PHP_URL_PATH) ?: '') . '/index.php';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    chdir(dirname(__DIR__) . '/www');
    require 'index.php';
    exit;
}

$process = proc_open([PHP_BINARY, __FILE__, '--home'], [
    0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w'],
], $pipes);
if (!is_resource($process)) {
    fwrite(STDERR, "Não foi possível iniciar o teste da resposta inicial.\n");
    exit(1);
}
fclose($pipes[0]);
$body = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$exitCode = proc_close($process);

try {
    $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $error) {
    $data = null;
}
$expectedKeys = ['graduacao_url', 'posgraduacao_url', 'pessoa_url', 'lattes_url',
    'bempatrimoniado_url', 'login_url', 'logout_url'];
if ($exitCode !== 0 || $stderr !== '' || !is_array($data) || array_keys($data) !== $expectedKeys) {
    fwrite(STDERR, "[FALHA] A entrada real deve retornar somente o JSON, sem avisos PHP.\n" . $stderr);
    exit(1);
}
echo "[OK] Entrada real retorna JSON válido, sem avisos PHP.\n";

require dirname(__DIR__) . '/vendor/autoload.php';
putenv('USPDEV_CACHE_DISABLE=1');

class CacheCompatibilityFixture
{
    public static $calls = 0;

    public static function next(): int
    {
        return ++self::$calls;
    }

    public function value($value)
    {
        return $value;
    }
}

$failed = false;
$check = static function (bool $ok, string $message) use (&$failed): void {
    echo ($ok ? '[OK] ' : '[FALHA] ') . $message . PHP_EOL;
    $failed = $failed || !$ok;
};
$cache = new Uspdev\Replicado_ws\Cache();
$check((bool) $cache->disable, 'Cache desabilitado sem depender de Memcached.');
$check($cache->getCached(CacheCompatibilityFixture::class . '::next', []) === 1
    && $cache->getCached(CacheCompatibilityFixture::class . '::next', []) === 2,
    'Métodos estáticos executados novamente a cada chamada.');
$instanceCache = new Uspdev\Replicado_ws\Cache(new CacheCompatibilityFixture());
foreach (['João', null, [], false, 0] as $value) {
    $check($instanceCache->getCached('value', [$value]) === $value,
        'Método de instância preserva o valor do tipo ' . get_debug_type($value) . '.');
}
$check($instanceCache->getCached('value', 'ação') === 'ação', 'Parâmetro escalar preservado.');
$check($cache->status()['disable'] === 'sim', 'Status identifica o cache desabilitado.');
$check($cache->flush() === true, 'Limpeza com cache desabilitado não acessa Memcached.');
exit($failed ? 1 : 0);
