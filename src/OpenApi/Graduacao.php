<?php

namespace Uspdev\Replicado_ws\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/graduacao',
    tags: ['Graduação'],
    operationId: 'ajudaGraduacao',
    summary: 'Ajuda do módulo graduacao',
    description: 'Sem consultas registradas. Atualmente retorna null porque não há entradas de ajuda.',
    responses: [
        new OA\Response(
            response: '200',
            description: 'Resposta da consulta.',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                        nullable: true,
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
final class Graduacao
{
}
