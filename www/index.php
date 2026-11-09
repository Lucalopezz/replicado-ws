<?php
require_once '../config/config.php';
require_once '../vendor/autoload.php';

// aqui tem várias personalizações do Flight e outras configurações
require_once '../app/bootstrap.php';

use Uspdev\Replicado_ws\Auth;
use Uspdev\Cache\Cache;

$c = new Cache();

// vamos colocar as rotas principais aqui.

// na raiz vamos colocar a documentação
Flight::route('/', function () {
    $api['graduacao_url'] = DOMINIO . '/graduacao';
    $api['posgraduacao_url'] = DOMINIO . '/posgraduacao';
    $api['pessoa_url'] = DOMINIO . '/pessoa';
    $api['lattes_url'] = DOMINIO . '/lattes';
    $api['bempatrimoniado_url'] = DOMINIO . '/bempatrimoniado';
    $api['login_url'] = DOMINIO . '/login';
    $api['logout_url'] = DOMINIO . '/logout';
    Flight::jsonf($api);
});

Flight::route('*', function() {
    Auth::ip_control();
    return true;
});

Flight::route('/login', function () {
    Auth::login();
});

Flight::route('/logout', function () {
    Auth::logout();
});

// vamos incluir as rotas secundárias aqui com base na 1a pasta da url
// para criar uma nova rota secundária, crie o arquivo ../app/<nome da rota>.php
$url = Flight::request()->url;
$app = explode('/', $url)[1];

if (file_exists('../app/' . $app . '.php')) {
    require_once '../app/' . $app . '.php';
} 

// incluindo rotas locais
if (defined('ROTAS_LOCAIS') and file_exists(ROTAS_LOCAIS)) {
    include_once ROTAS_LOCAIS;
}

Flight::start();
