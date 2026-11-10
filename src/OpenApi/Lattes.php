<?php

namespace Uspdev\Replicado_ws\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/lattes',
    tags: ['Lattes'],
    operationId: 'ajudaLattes',
    summary: 'Ajuda do módulo lattes',
    description: 'Lista URLs e descrições das consultas.',
    responses: [
        new OA\Response(
            response: '200',
            description: 'Resposta da consulta.',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        ref: '#/components/schemas/Ajuda',
                    ),
                    example: ['idLattes' => ['url' => '/lattes/idLattes/{codpes}', 'descricao' => 'Consulta o identificador Lattes pelo número USP.']],
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
    path: '/lattes/idLattes/{codpes}',
    tags: ['Lattes'],
    operationId: 'lattesId',
    summary: 'Consultar identificador Lattes',
    description: 'Identificador Lattes como string JSON ou false quando não encontrado. Sem exigência de HTTP Basic na implementação atual.',
    responses: [
        new OA\Response(
            response: '200',
            description: 'Resposta da consulta.',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        ref: '#/components/schemas/TextoOuAusente',
                    ),
                    example: '',
                ),
            ],
        ),
        new OA\Response(
            response: '403',
            ref: '#/components/responses/Forbidden',
        ),
        new OA\Response(
            response: '404',
            ref: '#/components/responses/NotFound',
        ),
    ],
    parameters: [
        new OA\Parameter(
            ref: '#/components/parameters/Codpes',
        ),
    ],
)]
final class Lattes
{
}
