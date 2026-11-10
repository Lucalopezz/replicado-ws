<?php

namespace Uspdev\Replicado_ws\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/bempatrimoniado',
    tags: ['Patrimônio'],
    operationId: 'ajudaBempatrimoniado',
    summary: 'Ajuda do módulo bempatrimoniado',
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
                    example: ['dump' => ['url' => '/bempatrimoniado/dump/{numpat}', 'descricao' => 'Consulta os dados de um bem.']],
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
    path: '/bempatrimoniado/dump/{numpat}',
    tags: ['Patrimônio'],
    operationId: 'patrimonioDump',
    summary: 'Consultar todos os campos de um bem',
    description: 'Campos de BEMPATRIMONIADO. Sem exigência de HTTP Basic na implementação atual.',
    responses: [
        new OA\Response(
            response: '200',
            description: 'Resposta da consulta.',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        ref: '#/components/schemas/RegistroOuAusente',
                    ),
                    example: ['numpat' => '', 'stabem' => 'Ativo'],
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
            ref: '#/components/parameters/Numpat',
        ),
    ],
)]
final class Patrimonio
{
}
