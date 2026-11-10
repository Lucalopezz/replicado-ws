<?php

namespace Uspdev\Replicado_ws\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/posgraduacao',
    tags: ['Pós-graduação'],
    operationId: 'ajudaPosgraduacao',
    summary: 'Ajuda do módulo posgraduacao',
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
                    example: ['programas' => ['url' => '/posgraduacao/programas/{codcur}', 'descricao' => 'Consulta programas de pós-graduação.']],
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
    path: '/posgraduacao/verifica/{codpes}',
    tags: ['Pós-graduação'],
    operationId: 'posgraduacaoVerifica',
    summary: 'Verificar matrícula ativa',
    description: 'true se houver matrícula de pós-graduação ativa em qualquer colegiado configurado; caso contrário, false. Requer HTTP Basic. Credenciais ausentes ou inválidas retornam HTTP 200 com message = Requires Authentication, em vez de 401.',
    responses: [
        new OA\Response(
            response: '200',
            description: 'Resposta da consulta.',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        anyOf: [
                            new OA\Schema(
                                type: 'boolean',
                                example: true,
                            ),
                            new OA\Schema(
                                ref: '#/components/schemas/Mensagem',
                            ),
                        ],
                    ),
                    examples: [
                        new OA\Examples(
                            example: 'semCredenciais',
                            summary: 'Credenciais ausentes ou inválidas (HTTP 200)',
                            value: ['message' => 'Requires Authentication', 'documentation_url' => '/'],
                        ),
                    ],
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
    security: [['basicAuth' => []]],
)]
#[OA\Get(
    path: '/posgraduacao/ativos',
    tags: ['Pós-graduação'],
    operationId: 'posgraduacaoAtivos',
    summary: 'Listar alunos ativos de pós-graduação',
    description: 'Retorna alunos ativos dos colegiados configurados. Requer HTTP Basic. Credenciais ausentes ou inválidas retornam HTTP 200 com message = Requires Authentication, em vez de 401.',
    responses: [
        new OA\Response(
            response: '200',
            description: 'Resposta da consulta.',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        anyOf: [
                            new OA\Schema(
                                ref: '#/components/schemas/ListaRegistros',
                            ),
                            new OA\Schema(
                                ref: '#/components/schemas/Mensagem',
                            ),
                        ],
                    ),
                    examples: [
                        new OA\Examples(
                            example: 'sucesso',
                            summary: 'Exemplo ilustrativo; dados pessoais vazios',
                            value: [['codpes' => '', 'nompes' => '', 'sexpes' => '']],
                        ),
                        new OA\Examples(
                            example: 'semCredenciais',
                            summary: 'Credenciais ausentes ou inválidas (HTTP 200)',
                            value: ['message' => 'Requires Authentication', 'documentation_url' => '/'],
                        ),
                    ],
                ),
            ],
        ),
        new OA\Response(
            response: '403',
            ref: '#/components/responses/Forbidden',
        ),
    ],
    security: [['basicAuth' => []]],
)]
#[OA\Get(
    path: '/posgraduacao/programas',
    tags: ['Pós-graduação'],
    operationId: 'posgraduacaoProgramas',
    summary: 'Listar programas de pós-graduação',
    description: 'Lista todos os programas. Para filtrar, use /posgraduacao/programas/{codcur}.',
    responses: [
        new OA\Response(
            response: '200',
            description: 'Resposta da consulta.',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        ref: '#/components/schemas/ListaRegistros',
                    ),
                    example: [['codcur' => 18070, 'nomcur' => 'Engenharia', 'codare' => '']],
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
    path: '/posgraduacao/programas/{codcur}',
    tags: ['Pós-graduação'],
    operationId: 'posgraduacaoPrograma',
    summary: 'Consultar programa pelo código do curso',
    description: 'Retorna lista de dados do programa, incluindo suas áreas. Exemplo: 18070 (Engenharia).',
    responses: [
        new OA\Response(
            response: '200',
            description: 'Resposta da consulta.',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        ref: '#/components/schemas/ListaRegistros',
                    ),
                    example: [['codcur' => 18070, 'nomcur' => 'Engenharia', 'codare' => '']],
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
            ref: '#/components/parameters/Codcur',
        ),
    ],
)]
#[OA\Get(
    path: '/posgraduacao/orientadores/{codare}',
    tags: ['Pós-graduação'],
    operationId: 'posgraduacaoOrientadores',
    summary: 'Listar orientadores credenciados na área',
    description: 'Retorna orientadores com credenciamento vigente na área de concentração.',
    responses: [
        new OA\Response(
            response: '200',
            description: 'Resposta da consulta.',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        ref: '#/components/schemas/ListaRegistros',
                    ),
                    example: [['codpes' => '', 'nompes' => '', 'sexpes' => '']],
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
            ref: '#/components/parameters/Codare',
        ),
    ],
)]
#[OA\Get(
    path: '/posgraduacao/catalogodisciplinas/{codare}',
    tags: ['Pós-graduação'],
    operationId: 'posgraduacaoCatalogodisciplinas',
    summary: 'Consultar catálogo de disciplinas',
    description: 'Disciplinas da área de concentração. l=completo inclui dados adicionais da disciplina.',
    responses: [
        new OA\Response(
            response: '200',
            description: 'Resposta da consulta.',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        ref: '#/components/schemas/ListaRegistros',
                    ),
                    example: [],
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
            ref: '#/components/parameters/Codare',
        ),
        new OA\Parameter(
            ref: '#/components/parameters/Completo',
        ),
    ],
)]
#[OA\Get(
    path: '/posgraduacao/disciplinas_oferecimento/{codare}',
    tags: ['Pós-graduação'],
    operationId: 'posgraduacaoDisciplinas_oferecimento',
    summary: 'Consultar disciplinas em oferecimento',
    description: 'Oferecimentos da área de concentração. l=completo inclui detalhes como local e ministrantes.',
    responses: [
        new OA\Response(
            response: '200',
            description: 'Resposta da consulta.',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        ref: '#/components/schemas/ListaRegistros',
                    ),
                    example: [],
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
            ref: '#/components/parameters/Codare',
        ),
        new OA\Parameter(
            ref: '#/components/parameters/Completo',
        ),
    ],
)]
#[OA\Get(
    path: '/posgraduacao/areasProgramas/{codcur}',
    tags: ['Pós-graduação'],
    operationId: 'posgraduacaoAreasProgramas',
    summary: 'Listar áreas de concentração do programa',
    description: 'Áreas agrupadas pelo código do curso. Use um codare retornado aqui nas consultas por área.',
    responses: [
        new OA\Response(
            response: '200',
            description: 'Resposta da consulta.',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        ref: '#/components/schemas/AreasPorPrograma',
                    ),
                    example: ['18070' => [['codare' => '', 'nomare' => '']]],
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
            ref: '#/components/parameters/Codcur',
        ),
    ],
)]
#[OA\Get(
    path: '/posgraduacao/alunosPrograma/{codcur}',
    tags: ['Pós-graduação'],
    operationId: 'posgraduacaoAlunosPrograma',
    summary: 'Listar alunos ativos do programa',
    description: 'Consulta todas as áreas do curso nos colegiados configurados e retorna os alunos ativos.',
    responses: [
        new OA\Response(
            response: '200',
            description: 'Resposta da consulta.',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        ref: '#/components/schemas/ListaRegistros',
                    ),
                    example: [['codpes' => '', 'nompes' => '', 'sexpes' => '']],
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
            ref: '#/components/parameters/Codcur',
        ),
    ],
)]
final class Posgraduacao
{
}
