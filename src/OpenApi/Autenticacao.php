<?php

namespace Uspdev\Replicado_ws\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/login',
    tags: ['Autenticação'],
    operationId: 'login',
    summary: 'Validar credenciais HTTP Basic',
    description: 'Use Authorize antes de executar. Retorna Login success com credenciais válidas. Não cria sessão nem token; envie Basic em todas as rotas protegidas.',
    responses: [
        new OA\Response(
            response: '200',
            description: 'Resposta da consulta.',
            content: [
                new OA\MediaType(
                    mediaType: 'text/html',
                    schema: new OA\Schema(
                        type: 'string',
                    ),
                    example: 'Login success',
                ),
            ],
        ),
        new OA\Response(
            response: '403',
            ref: '#/components/responses/Forbidden',
        ),
        new OA\Response(
            response: '401',
            description: 'Credenciais ausentes ou inválidas. Pode incluir WWW-Authenticate: Basic.',
            headers: [
                new OA\Header(
                    header: 'WWW-Authenticate',
                    schema: new OA\Schema(
                        type: 'string',
                        example: 'Basic realm="use this hash key to encode"',
                    ),
                ),
            ],
            content: [
                new OA\MediaType(
                    mediaType: 'text/html',
                    schema: new OA\Schema(
                        type: 'string',
                    ),
                    examples: [
                        new OA\Examples(
                            example: 'ausentes', summary: 'Credenciais ausentes',
                            value: 'Você deve digitar um login e senha válidos para acessar este recurso\\n',
                        ),
                        new OA\Examples(
                            example: 'invalidas', summary: 'Credenciais inválidas',
                            value: 'Credenciais inválidas',
                        ),
                    ],
                ),
            ],
        ),
    ],
    security: [['basicAuth' => []]],
)]
#[OA\Get(
    path: '/logout',
    tags: ['Autenticação'],
    operationId: 'logout',
    summary: 'Solicitar nova autenticação ao navegador',
    description: 'Sempre retorna 401, texto logout e WWW-Authenticate: Basic. Não revoga credenciais. Para limpar o Swagger UI, use Authorize e Logout.',
    responses: [
        new OA\Response(
            response: '401',
            description: 'Solicita autenticação HTTP Basic ao navegador.',
            content: [
                new OA\MediaType(
                    mediaType: 'text/html',
                    schema: new OA\Schema(
                        type: 'string',
                    ),
                    example: 'logout',
                ),
            ],
            headers: [
                new OA\Header(
                    header: 'WWW-Authenticate',
                    schema: new OA\Schema(
                        type: 'string',
                        example: 'Basic realm="use this hash key to encode"',
                    ),
                ),
            ],
        ),
        new OA\Response(
            response: '403',
            ref: '#/components/responses/Forbidden',
        ),
    ],
)]
final class Autenticacao
{
}
