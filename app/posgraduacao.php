<?php

use Uspdev\Replicado\Posgraduacao;
use Uspdev\Replicado_ws\Auth;
use Uspdev\Replicado_ws\Colegiados;

Flight::route('/posgraduacao', function () {
    global $help;
    Flight::jsonf($help);
});

$help['verifica'] = [
    'url' => DOMINIO . '/posgraduacao/verifica/{codpes}',
    'descricao' => 'verifica se aluno (codpes) tem matrícula ativa na pós-graduação da unidade',
];
Flight::route('/posgraduacao/verifica/@codpes:[0-9]+', function ($codpes) {
    Auth::auth();
    $res = Colegiados::verificar(fn ($codigo) => Posgraduacao::verifica($codpes, $codigo));
    Flight::json($res);
});

$help['ativos'] = [
    'url' => DOMINIO . '/posgraduacao/ativos/',
    'descricao' => 'retorna todos os alunos de pós-graduação ativos na unidade',
];
Flight::route('/posgraduacao/ativos', function () {
    global $c;
    Auth::auth();
    $res = Colegiados::consultar(fn ($codigo) => $c->getCached('\Uspdev\Replicado\Posgraduacao::ativos', $codigo));
    Flight::json($res);
});

$help['programas'] = [
    'url' => DOMINIO . '/posgraduacao/programas/{codcur}',
    'descricao' => 'retorna todos os programas de pós-graduação da unidade ou quando informado o código do curso/programa retorna somente os dados do programa solicitado',
];
Flight::route('/posgraduacao/programas(/@codcur:[0-9]+)', function ($codcur) {
    global $c;
    $res = Colegiados::consultar(fn ($codigo) => Posgraduacao::programas($codigo, $codcur));
    Flight::json($res);
});

// em uso no site da PG do SET, 9/2019
$help['orientadores'] = [
    'url' => DOMINIO . '/posgraduacao/orientadores/{codare}',
    'descricao' => 'retorna os orientadores credenciados na área de concentração (codare) do programa de pós graduação correspondente',
];
Flight::route('/posgraduacao/orientadores/@codare:[0-9]+', function ($codare) {
    global $c;
    $res = $c->getCached('\Uspdev\Replicado\Posgraduacao::orientadores', $codare);
    Flight::json($res);
});

// em uso no site da PG do SET, 9/2019
$help['catalogodisciplinas'] = [
    'url' => DOMINIO . '/posgraduacao/catalogodisciplinas/{codare}(?l=completo)',
    'descricao' => 'retorna o catálogo das disciplinas pertencentes à área de concentração',
];
Flight::route('/posgraduacao/catalogodisciplinas/@codare:[0-9]+', function ($codare) {
    $l = Flight::request()->query['l'];
    global $c;
    if ($l == 'completo') {
        // como o replicado não tem essa consulta vamos usar uma classe própria para isso
        $res = $c->getCached('\Uspdev\Replicado_ws\Model\Posgraduacao::catalogoDisciplinas', $codare);
    } else {
        $res = $c->getCached('\Uspdev\Replicado\Posgraduacao::catalogoDisciplinas', $codare);
    }
    Flight::json($res);

});

// em uso no site da PG do SET, 9/2019
$help['disciplinas_oferecimento'] = [
    'url' => DOMINIO . '/posgraduacao/disciplinas_oferecimento/{codare}',
    'descricao' => 'retorna as disciplinas em oferecimento na área de concentração (codare) do programa de pós graduação correspondente',
];
Flight::route('/posgraduacao/disciplinas_oferecimento/@codare:[0-9]+', function ($codare) {
    $l = Flight::request()->query['l'];
    global $c;
    if ($l == 'completo') {
        // como o replicado não tem essa consulta vamos usar uma classe própria para isso
        $res = $c->getCached('\Uspdev\Replicado_ws\Model\Posgraduacao::disciplinasOferecimento', $codare);
    } else {
        $res = $c->getCached('\Uspdev\Replicado\Posgraduacao::disciplinasOferecimento', $codare);

    }
    Flight::json($res);
});

// igc
$help['areasProgramas'] = [
    'url' => DOMINIO . '/posgraduacao/areasProgramas/{codcur}',
    'descricao' => 'retorna áreas de concentração (codare) do programa de pós-graduação correspondente (codcur)',
];
Flight::route('/posgraduacao/areasProgramas/@codcur:[0-9]+', function ($codcur) {
    $res = Colegiados::consultar(fn ($codigo) => Posgraduacao::areasProgramas($codigo, $codcur));
    Flight::json($res);
});

// igc
$help['alunosPrograma'] = [
    'url' => DOMINIO . '/posgraduacao/alunosPrograma/{codcur}',
    'descricao' => 'retorna os alunos ativos das áreas de concentração (codare) do programa de pós-graduação correspondente (codcur)',
];
Flight::route('/posgraduacao/alunosPrograma/@codcur:[0-9]+', function ($codcur) {
    $res = Colegiados::consultar(fn ($codigo) => \Uspdev\Replicado_ws\Model\Posgraduacao::alunosPrograma($codigo, $codcur));
    Flight::json($res);
});
