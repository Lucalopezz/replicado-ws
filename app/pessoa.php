<?php

use Uspdev\Replicado_ws\Auth;

Flight::route('/pessoa', function () {
    global $help;
    Flight::jsonf($help);
});

/**
 * Dados básicos da Pessoa pelo número usp
 */
$help['dump'] = [
    'url' => DOMINIO . '/pessoa/dump/{codpes}',
    'descricao' => 'recebe codpes e retorna todos campos da tabela Pessoa para o codpes em questão',
];
Flight::route('/pessoa/dump/@codpes:[0-9]+', function ($codpes) {
    global $c;
    Auth::auth();
    $res = $c->getCached('\Uspdev\Replicado\Pessoa::dump', $codpes);
    Flight::jsonf($res);
});

/**
 * Dados básicos da Pessoa pelo nome
 */
$help['nome'] = [
    'url' => DOMINIO . '/pessoa/nome/?q={nome}',
    'descricao' => 'recebe uma string nome e retorna os resultados para a tabela Pessoa',
];
Flight::route('/pessoa/nome', function () {
    global $c;
    $request = Flight::request();

    // se q é vazio vamos passar para a próxima rota (geralmente vai ser 'not found')
    if (empty($request->query['q'])) {
        return true;
    }
    Auth::auth();
    $res = $c->getCached('\Uspdev\Replicado\Pessoa::procurarPorNome', [$request->query['q'], false, false]);
    Flight::jsonf($res);
});

/**
 * Docentes ativos da unidade
 */
$help['docentes'] = [
    'url' => DOMINIO . '/pessoa/docentes',
    'descricao' => 'retorna todos os docentes ativos na unidade',
];
Flight::route('/pessoa/docentes', function () {
    Auth::auth();
    $res = \Uspdev\Replicado\Pessoa::listarDocentes();
    Flight::json($res);
});

/**
 * Funcionários ativos na unidade
 */
$help['servidores'] = [
    'url' => DOMINIO . '/pessoa/servidores',
    'descricao' => 'retorna todos os funcionários ativos na unidade',
];
Flight::route('/pessoa/servidores', function () {
    global $c;
    Auth::auth();
    $res = $c->getCached('\Uspdev\Replicado\Pessoa::servidores', getenv('REPLICADO_CODUNDCLG'));
    Flight::jsonf($res);
});

/**
 * Estagiários ativos na unidade
 */
$help['estagiarios'] = [
    'url' => DOMINIO . '/pessoa/estagiarios',
    'descricao' => 'retorna todos os estagiários ativos na unidade',
];
Flight::route('/pessoa/estagiarios', function () {
    global $c;
    Auth::auth();
    $res = $c->getCached('\Uspdev\Replicado\Pessoa::estagiarios', getenv('REPLICADO_CODUNDCLG'));
    Flight::jsonf($res);
});
    
/**
 * E-mail principal de uma Pessoa
 * 
 * Uso no sistema de Reserva de Espaços da EESC
 */
$help['email'] = [
    'url' => DOMINIO . '/pessoa/email/{codpes}',
    'descricao' => 'recebe codpes e retorna o e-mail principal de uma Pessoa',
];
Flight::route('/pessoa/email/@codpes:[0-9]+', function ($codpes) {
    global $c;
    Auth::auth();
    $res = $c->getCached('\Uspdev\Replicado\Pessoa::email', $codpes);
    Flight::jsonf($res);
});

$help['procura_ativo'] = [
    'url' => DOMINIO . '/pessoa/procura_ativo/?q={codpes_nome}',
    'descricao' => '(local) procura por nome ou número USP e retorna dados básicos. Implementado inicialmente no cartarecomendacao.',
];
Flight::route('/pessoa/procura_ativo/', function () {
    global $c;
    
    Auth::auth();
    $q = Flight::request()->query['q'];
    
    if (empty($q)) {
        return [];
    }
    
    $res = \Uspdev\Replicado\Pessoa::procurarPorCodigoOuNome($q);
    $rets = [];
    foreach ($res as $pessoa) {
        $email = \Uspdev\Replicado\Pessoa::email($pessoa['codpes']);
        $ret = [$pessoa['codpes'], $pessoa['nompes'], $pessoa['sexpes'], $email];
        $rets[] = $ret;
    }
    Flight::json($rets);
});

/**
 * Docentes aposentados seniores da unidade
 */
$help['docentes_seniores'] = [
    'url' => DOMINIO . '/pessoa/docentes/seniores',
    'descricao' => 'retorna todos os docentes aposentados seniores da unidade.'
];
Flight::route('/pessoa/docentes/seniores', function (){
    Auth::auth();
    $res = \Uspdev\Replicado\Pessoa::listarDocentesAposentadosSenior();
    Flight::json($res);
});
