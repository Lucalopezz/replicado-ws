<?php
namespace Uspdev\Replicado_ws\Model;

use \Uspdev\Replicado\Posgraduacao as UspdevPosgraduacao;

class Posgraduacao
{
    // Evita chamar o método legado sem áreas quando o curso não pertence ao colegiado.
    public static function alunosPrograma(int $codundclg, int $codcur): array
    {
        $programas = UspdevPosgraduacao::areasProgramas($codundclg, $codcur);
        $alunos = [];
        foreach ($programas[$codcur] ?? [] as $area) {
            $alunos = array_merge($alunos, UspdevPosgraduacao::alunosPrograma($codundclg, $codcur, (int) $area['codare']));
        }
        return $alunos;
    }

    // o uspdev/replicado pega somente os dados básicos da disciplina no método catálogodisciplina.
    // vamos pegar todos os dados aqui
    public static function catalogoDisciplinas($codare)
    {
        $catalogo = UspdevPosgraduacao::catalogoDisciplinas($codare);
        for ($i = 0; $i < count($catalogo); $i++) {
            $sgldis = $catalogo[$i]['sgldis'];
            $disciplina = UspdevPosgraduacao::disciplina($sgldis);
            $catalogo[$i] = array_merge($catalogo[$i], $disciplina);

            // vamos omitir alguns campos
            unset($catalogo[$i]['codpesalt']);
            unset($catalogo[$i]['dtaultalt']);
            unset($catalogo[$i]['dtadtvdis']);
            unset($catalogo[$i]['timestamp']);
        }
        return $catalogo;
    }

    // o uspdev/replicado pega somente os dados básicos da disciplina no método disciplinasOferecimento.
    // vamos pegar todos os dados aqui recursivamente
    public static function disciplinasOferecimento($codare)
    {
        $disciplinas = UspdevPosgraduacao::disciplinasOferecimento($codare);

        for ($i = 0; $i < count($disciplinas); $i++) {
            #vamos remover timestamps que dá problema em json
            $sgldis = $disciplinas[$i]['sgldis'];
            $numofe = $disciplinas[$i]['numofe'];
            $oferecimento = UspdevPosgraduacao::oferecimento($sgldis, $numofe);
            unset($oferecimento['timestamp']);
            unset($oferecimento['espacoturma'][0]['timestamp']);
            $disciplinas[$i] = array_merge($disciplinas[$i], $oferecimento);
        }
        return $disciplinas;
    }
}
