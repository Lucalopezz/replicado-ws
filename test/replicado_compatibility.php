<?php

// Executa a conversão real do Replicado com dados sintéticos e sem acesso à rede.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

error_reporting(E_ALL);
set_error_handler(static function ($severity, $message, $file, $line): void {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
require dirname(__DIR__) . '/vendor/autoload.php';

use Uspdev\Replicado\DB;
use Uspdev\Replicado\Replicado;
use Uspdev\Replicado\Uteis;

$cases = [
    'Latin-1 convertido para UTF-8' => static function (): bool {
        return Uteis::utf8_converter(['nome' => "Jo\xe3o", 'texto' => "a\xe7\xe3o e caf\xe9"])
            === ['nome' => 'João', 'texto' => 'ação e café'];
    },
    'UTF-8 existente preservado' => static function (): bool {
        $value = ['nome' => 'João', 'texto' => 'ação e café', 'simbolo' => '🎓'];
        return Uteis::utf8_converter($value) === $value;
    },
    'Conversão preserva null, números e booleanos' => static function (): bool {
        $value = ['nulo' => null, 'inteiro' => 7, 'decimal' => 1.5, 'falso' => false, 'zero' => 0];
        return Uteis::utf8_converter($value) === $value;
    },
    'Trim preserva os tipos e remove espaços somente de strings' => static function (): bool {
        return Uteis::trim_recursivo(['nome' => ' João ', 'nulo' => null, 'n' => 7, 'falso' => false])
            === ['nome' => 'João', 'nulo' => null, 'n' => 7, 'falso' => false];
    },
    'Conversão recursiva gera JSON com acentos e nulos' => static function (): bool {
        $value = ['linhas' => [['nome' => " Jo\xe3o ", 'obs' => null, 'ativo' => true]], 'vazio' => []];
        $converted = Uteis::trim_recursivo(Uteis::utf8_converter($value));
        return json_decode(json_encode($converted, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR)
            === ['linhas' => [['nome' => 'João', 'obs' => null, 'ativo' => true]], 'vazio' => []];
    },
];

// Substitui apenas o driver; DB::fetch/fetchAll, conversão e trim são os reais.
class ReplicadoFixtureStatement extends PDOStatement
{
    public function __construct() {}

    public function execute(?array $params = null): bool
    {
        return true;
    }

    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    {
        return ['nome' => " Jo\xe3o ", 'obs' => null, 'n' => 7];
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        return [$this->fetch()];
    }
}

class ReplicadoFixturePDO extends PDO
{
    public function __construct() {}

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return new ReplicadoFixtureStatement();
    }
}

$cases['Fluxo DB::fetch e DB::fetchAll preserva acentos e nulos'] = static function (): bool {
    $config = Replicado::getInstance([
        'host' => '127.0.0.1', 'port' => 1433, 'database' => 'fixture',
        'username' => 'fixture', 'password' => 'fixture', 'usarCache' => false,
    ]);
    $config->sybase = true;
    $config->usarCache = false;
    $config->debugLevel = 0;
    $instance = new ReflectionProperty(DB::class, 'instance');
    $previous = $instance->getValue();
    $instance->setValue(null, new ReplicadoFixturePDO());
    try {
        $expected = ['nome' => 'João', 'obs' => null, 'n' => 7];
        return DB::fetch('SELECT fixture') === $expected
            && DB::fetchAll('SELECT fixture') === [$expected];
    } finally {
        $instance->setValue(null, $previous);
    }
};

$failed = 0;
foreach ($cases as $name => $case) {
    try {
        $ok = $case();
    } catch (Throwable $error) {
        $ok = false;
        fwrite(STDERR, $error->getMessage() . PHP_EOL);
    }
    echo ($ok ? '[OK] ' : '[FALHA] ') . $name . PHP_EOL;
    $failed += $ok ? 0 : 1;
}
echo count($cases) . ' cenários, ' . $failed . ' falhas.' . PHP_EOL;
exit($failed === 0 ? 0 : 1);
