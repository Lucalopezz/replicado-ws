<?php

namespace Uspdev\Replicado_ws\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/',
    tags: ['Documentação'],
    operationId: 'indice',
    summary: 'Índice da API',
    description: 'Links dos módulos e da documentação.',
    responses: [
        new OA\Response(
            response: '200',
            description: 'Resposta da consulta.',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                        additionalProperties: new OA\AdditionalProperties(
                            type: 'string',
                        ),
                    ),
                    example: ['pessoa_url' => '/pessoa', 'posgraduacao_url' => '/posgraduacao', 'graduacao_url' => '/graduacao', 'lattes_url' => '/lattes', 'bempatrimoniado_url' => '/bempatrimoniado', 'login_url' => '/login', 'logout_url' => '/logout', 'docs_url' => '/docs', 'openapi_url' => '/openapi.json'],
                ),
            ],
        ),
        new OA\Response(
            response: '403',
            ref: '#/components/responses/Forbidden',
        ),
    ],
)]
#[OA\Get(
    path: '/docs',
    tags: ['Documentação'],
    operationId: 'swaggerUi',
    summary: 'Swagger UI',
    description: 'Documentação interativa usando a página e os arquivos do pacote oficial swagger-api/swagger-ui, servidos pela própria API.',
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
                ),
            ],
        ),
        new OA\Response(
            response: '403',
            ref: '#/components/responses/Forbidden',
        ),
    ],
)]
#[OA\Get(
    path: '/openapi.json',
    tags: ['Documentação'],
    operationId: 'openapi',
    summary: 'Especificação OpenAPI',
    description: 'OpenAPI 3.0.3 com a URL desta instalação em servers.',
    responses: [
        new OA\Response(
            response: '200',
            description: 'Resposta da consulta.',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                        additionalProperties: true,
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
final class Documentacao
{
}
