<?php

ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

// nao sei se os locales são necessários, copiado de outra aplicacao, mas de qualquer forma mal não faz
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

// caminho raiz do projeto. Não precisa ser mudado
define('DIR', __DIR__ . '/..');

// isso é usado para informar na API os endpoints disponíveis
// sem barra no final
define("DOMINIO", "http://dominio/caminho"); 

// se for usar rotas locais tem de definir aqui
//define('ROTAS_LOCAIS', DIR . '/local/rotas/rota_local.php');

// vamos deixar generico para qualquer unidade poder usar
// deprecado para usar o REPLICADO_CODUNDCLG em 1/2/2022
//define('UNIDADE', 18);

// configuracoes para usar o uspdev/replicado
putenv('REPLICADO_HOST=192.168.100.89');
putenv('REPLICADO_PORT=1498');
putenv('REPLICADO_DATABASE=rep_dbc');
putenv('REPLICADO_USERNAME=dbmaint_read');
putenv('REPLICADO_PASSWORD=secret');
// Unidade principal: um único código. Ajuste os exemplos para sua unidade.
putenv('REPLICADO_CODUNDCLG=18');
// Colegiados da unidade, separados por vírgula. Para um só, repita o código principal.
// As rotas por unidade consultam todos os códigos desta lista.
// Se vazia, a aplicação usa REPLICADO_CODUNDCLG (inclusive listas de configurações antigas).
putenv('REPLICADO_CODUNDCLGS=18,97');
putenv('REPLICADO_SYBASE=1');
putenv('REPLICADO_PATHLOG=' . DIR . '/local/replicado.log'); // se não pusermos nada vai para default do replicado que é /tmp/log.log

// Usuários da API
// por enquanto ficamos aqui no config, depois podemos pensar em usar DB
$users = [
    'masaki' => md5('masaki'),
    'andre' => md5('andre'),
    'aplicacao' => md5('senha_da_aplicacao'),
];

// Controle de acesso por IP
// Permite que limitemos o acesso aos endpoints com base no IP do cliente
// se estiver definido IP_ACCESS_LIST, vai realizar controle de acesso
// e negar tudo que não estiver na lista.
// Se IP_ACCESS_LIST não estiver definido, o acesso por IP não é implementado
// e os endpoints estarão liberados para qualquer IP solicitante
define ('IP_ACCESS_LIST', array(
    ['182.168.0.0','24'], // libera sua rede privada
    ['143.107.0.0','16'], // libera qualquer endereço da USP
    ['10.233.0.10','32'], // libera um IP específico
));
