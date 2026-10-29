<?php

// Somente o PDO e o armazenamento do cache são simulados; rotas e biblioteca são reais.
class ColegiadosFixturePDO extends PDO
{
    public function __construct() {}

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return new ColegiadosFixtureStatement($query);
    }
}

class ColegiadosFixtureStatement extends PDOStatement
{
    private static int $curso = 500;
    private array $params = [];

    public function __construct(private string $query) {}

    public function bindValue(string|int $param, mixed $value, int $type = PDO::PARAM_STR): bool
    {
        $this->params[ltrim((string) $param, ':')] = $value;
        return true;
    }

    public function execute(?array $params = null): bool
    {
        $codigo = (int) getenv('REPLICADO_CODUNDCLG');
        if (!in_array($codigo, [18, 97], true)
            || getenv('REPLICADO_CODUNDCLG') !== (string) $codigo
            || (string) Uspdev\Replicado\Replicado::getConfig('codundclg') !== (string) $codigo
            || (isset($this->params['codundclgi']) && (int) $this->params['codundclgi'] !== $codigo)
            || str_contains($this->query, 'IN ()')) {
            throw new RuntimeException('A consulta não recebeu um único colegiado válido.');
        }
        return true;
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        $codigo = (int) getenv('REPLICADO_CODUNDCLG');
        if (str_contains($this->query, 'FROM CURSO AS C')) {
            $curso = $this->params['codcur'] ?? null;
            self::$curso = (int) ($curso ?? 500);
            $programas = [
                ['codcur' => $codigo * 10, 'nomcur' => 'Programa ' . $codigo, 'codare' => $codigo * 100, 'nomare' => 'Área ' . $codigo],
                ['codcur' => 500, 'nomcur' => 'Programa compartilhado', 'codare' => 5001, 'nomare' => 'Área comum'],
            ];
            return array_values(array_filter($programas, fn ($row) => $curso === null || $row['codcur'] == $curso));
        }
        if (str_contains($this->query, 'SELECT codare FROM AREA')) {
            return [['codare' => 5001], ['codare' => $codigo * 100]];
        }
        if (str_contains($this->query, 'SELECT TOP 1 N.codcur')) {
            return [['codcur' => self::$curso, 'codare' => (int) $this->params['codare'], 'nomare' => 'Área ' . $this->params['codare']]];
        }
        if (str_contains($this->query, 'SELECT DISTINCT V.codare')) {
            return [['codpes' => $codigo, 'nompes' => 'Aluno ' . $codigo, 'codare' => (int) $this->params['codare']]];
        }
        if (str_contains($this->query, 'WHERE codpes = convert')) {
            return [['codpes' => 123, 'tipvin' => 'ALUNOPOS', 'sitatl' => 'A', 'codundclg' => 97]];
        }

        // Uma pessoa com vínculos distintos e um registro idêntico nos dois colegiados.
        return [
            ['codpes' => 42, 'nompes' => 'Pessoa', 'codundclg' => $codigo],
            ['codpes' => 99, 'nompes' => 'Registro compartilhado'],
        ];
    }
}

if (!class_exists('Memcached')) {
    class Memcached
    {
        public const RES_SUCCESS = 0;
    }
}

class ColegiadosFixtureMemoryCache
{
    private array $values = [];
    private int $result = 16;

    public function get($key)
    {
        $this->result = array_key_exists($key, $this->values) ? 0 : 16;
        return $this->values[$key] ?? false;
    }

    public function set($key, $value, $expiry): bool
    {
        $this->values[$key] = $value;
        $this->result = 0;
        return true;
    }

    public function getResultCode(): int
    {
        return $this->result;
    }
}

function replicadoColegiadosFixture(array $case): Uspdev\Replicado_ws\Cache
{
    foreach (['REPLICADO_CODUNDCLG', 'REPLICADO_CODUNDCLGS'] as $variavel) {
        $valor = $case['colegiados'][$variavel] ?? null;
        putenv($valor === null ? $variavel : $variavel . '=' . $valor);
    }
    $config = Uspdev\Replicado\Replicado::getInstance([
        'host' => '127.0.0.1', 'port' => 1433, 'database' => 'fixture',
        'username' => 'fixture', 'password' => 'fixture', 'sybase' => false, 'usarCache' => false,
    ]);
    $original = [$config->codundclg, $config->codundclgs, getenv('REPLICADO_CODUNDCLG'), getenv('REPLICADO_CODUNDCLGS')];
    register_shutdown_function(static function () use ($config, $original): void {
        if ($original !== [$config->codundclg, $config->codundclgs, getenv('REPLICADO_CODUNDCLG'), getenv('REPLICADO_CODUNDCLGS')]) {
            fwrite(STDERR, 'A consulta não restaurou a configuração.');
        }
    });
    $instance = new ReflectionProperty(Uspdev\Replicado\DB::class, 'instance');
    $instance->setValue(null, new ColegiadosFixturePDO());
    putenv('USPDEV_CACHE_DISABLE=1');
    $cache = new Uspdev\Replicado_ws\Cache();
    if ($case['cache'] ?? false) {
        $cache->disable = false;
        $cache->small = -1;
        $cache->cache = new ColegiadosFixtureMemoryCache();
    }
    return $cache;
}
