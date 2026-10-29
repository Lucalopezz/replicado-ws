<?php

namespace Uspdev\Replicado_ws;

use InvalidArgumentException;
use Uspdev\Replicado\Replicado;

/** Adapta as consultas por unidade do Replicado à lista de colegiados da aplicação. */
class Colegiados
{
    public static function consultar(callable $consulta): array
    {
        $resultado = [];
        foreach (self::codigos() as $codigo) {
            $resultado = self::mesclar($resultado, self::executar($codigo, $consulta));
        }
        return $resultado;
    }

    public static function verificar(callable $consulta): bool
    {
        foreach (self::codigos() as $codigo) {
            if (self::executar($codigo, $consulta)) {
                return true;
            }
        }
        return false;
    }

    private static function codigos(): array
    {
        $lista = trim((string) getenv('REPLICADO_CODUNDCLGS'));
        if ($lista === '') {
            $lista = trim((string) getenv('REPLICADO_CODUNDCLG'));
        }

        $codigos = [];
        foreach (explode(',', $lista) as $valor) {
            $valor = trim($valor);
            $codigo = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (!preg_match('/^[0-9]+$/D', $valor) || $codigo === false) {
                throw new InvalidArgumentException('Configure REPLICADO_CODUNDCLGS com códigos inteiros positivos separados por vírgula, ou REPLICADO_CODUNDCLG com um código.');
            }
            $codigos[$codigo] = $codigo;
        }
        return array_values($codigos);
    }

    private static function executar(int $codigo, callable $consulta)
    {
        // Alguns métodos recebem o código por argumento; outros ainda leem getenv().
        $config = Replicado::getInstance();
        $ambiente = [];
        $anterior = [];
        foreach (['codundclg' => 'REPLICADO_CODUNDCLG', 'codundclgs' => 'REPLICADO_CODUNDCLGS'] as $campo => $variavel) {
            $ambiente[$variavel] = getenv($variavel);
            $anterior[$campo] = $config->$campo;
            putenv($variavel . '=' . $codigo);
            $config->$campo = (string) $codigo;
        }

        try {
            return $consulta($codigo);
        } finally {
            foreach ($ambiente as $variavel => $valor) {
                putenv($valor === false ? $variavel : $variavel . '=' . $valor);
            }
            foreach ($anterior as $campo => $valor) {
                $config->$campo = $valor;
            }
        }
    }

    private static function mesclar(array $resultado, array $novos): array
    {
        if ($novos === []) {
            return $resultado;
        }
        if (array_is_list($novos)) {
            // Preserva vínculos distintos; remove somente registros idênticos.
            $vistos = [];
            $unicos = [];
            foreach (array_merge($resultado, $novos) as $registro) {
                $chave = serialize($registro);
                if (!isset($vistos[$chave])) {
                    $vistos[$chave] = true;
                    $unicos[] = $registro;
                }
            }
            return $unicos;
        }

        // areasProgramas retorna um mapa de cursos, cada um com sua lista de áreas.
        foreach ($novos as $curso => $areas) {
            $resultado[$curso] = self::mesclar($resultado[$curso] ?? [], $areas);
        }
        return $resultado;
    }
}
