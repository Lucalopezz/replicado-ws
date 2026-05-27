<?php

// vamos ajustar os caminhos baseados no arquivo de configuracao
// dessa forma não precisamos recorrer ao RewriteBase do apache
// e podemos usar um .htaccess que não depende do deploy
Flight::request()->base = parse_url(DOMINIO, PHP_URL_PATH);
Flight::request()->url = str_replace(Flight::request()->base, '', Flight::request()->url);

function recursive_unset(&$array, $unwanted_key)
{
    unset($array[$unwanted_key]);
    foreach ($array as &$value) {
        if (is_array($value)) {
            recursive_unset($value, $unwanted_key);
        }
    }
}

function replicado_ws_json_encode_options($option)
{
    $option = (int) $option;

    if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
        $option |= JSON_INVALID_UTF8_SUBSTITUTE;
    }

    if (defined('JSON_PARTIAL_OUTPUT_ON_ERROR')) {
        $option |= JSON_PARTIAL_OUTPUT_ON_ERROR;
    }

    return $option;
}

function replicado_ws_json_payload($data, $encode, $option)
{
    if (!$encode) {
        $json = (string) $data;
        return trim($json) === '' ? 'null' : $json;
    }

    $json = json_encode($data, replicado_ws_json_encode_options($option));

    if ($json === false || $json === '') {
        $json = json_encode([
            'message' => 'Erro ao gerar JSON',
            'error' => json_last_error_msg(),
        ], replicado_ws_json_encode_options(JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    return $json === false || $json === '' ? 'null' : $json;
}

// vamos imprimir saída no formato texto
Flight::map('text', function (array $array_rows) {
    foreach ($array_rows as $k => $v) {
        echo $k . ',' . $v . PHP_EOL;
    }
    return true;
});

// Sobrescreve Flight::json para evitar corpo vazio quando json_encode falhar
// ou quando algum endpoint tentar responder sem conteúdo.
Flight::map('json', function ($data, $code = 200, $encode = true, $charset = 'utf-8', $option = 0) {
    $json = replicado_ws_json_payload($data, $encode, $option);

    Flight::response()
        ->status($code)
        ->header('Content-Type', 'application/json; charset=' . $charset)
        ->write($json)
        ->send();
});

// vamos imprimir o json formatado para humanos lerem
Flight::map('jsonf', function ($data, $code = 200) {
    if (is_array($data)) {
        recursive_unset($data, 'timestamp');
    }
    Flight::json($data, $code, true, 'utf-8', JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
});

// quando o endpoint é protegido e não houver dados de autenticação
// vamos mostrar uma mensagem indicando como enviar as credenciais
Flight::map('requireAuth', function () {
    $data['message'] = 'Requires Authentication';
    $data['documentation_url'] = DOMINIO . '/';
    Flight::jsonf($data);
});

// vamos sobrescrever a mensagem de not found para ficar mais compatível com a API
// retorna 404 mas com mensagem personalizada opcional ou mensagem padrão
Flight::map('notFound', function ($msg = null) {
    $data['message'] = empty($msg) ? 'Not Found' : $msg;
    $data['documentation_url'] = DOMINIO . '/';
    Flight::jsonf($data, 404);
    exit;
});

// Interrompe a execução com 403 - Forbidden
// usado quando negado acesso por IP
Flight::map('forbidden', function ($msg = null) {
    $data['message'] = empty($msg) ? 'Forbidden' : $msg;
    $data['documentation_url'] = DOMINIO . '/';
    Flight::jsonf($data, 403);
    exit;
});
