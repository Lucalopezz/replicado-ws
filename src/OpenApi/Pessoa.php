<?php

namespace Uspdev\Replicado_ws\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/pessoa',
    tags: ['Pessoa'],
    operationId: 'ajudaPessoa',
    summary: 'Ajuda do módulo pessoa',
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
                    example: ['dump' => ['url' => '/pessoa/dump/{codpes}', 'descricao' => 'Consulta os dados de uma pessoa pelo número USP.']],
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
    path: '/pessoa/dump/{codpes}',
    tags: ['Pessoa'],
    operationId: 'pessoaDump',
    summary: 'Consultar todos os campos de uma pessoa',
    description: 'Consulta a tabela PESSOA pelo número USP. Campos timestamp são removidos da resposta. Requer HTTP Basic. Credenciais ausentes ou inválidas retornam HTTP 200 com message = Requires Authentication, em vez de 401.',
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
                                ref: '#/components/schemas/RegistroOuAusente',
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
                            value: ['codpes' => '', 'nompes' => '', 'sexpes' => ''],
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
    path: '/pessoa/nome',
    tags: ['Pessoa'],
    operationId: 'pessoaNome',
    summary: 'Pesquisar pessoas pelo nome',
    description: 'Busca sem restringir apenas a pessoas ativas. q obrigatório; vazio ou ausente retorna 404. Requer HTTP Basic. Credenciais ausentes ou inválidas retornam HTTP 200 com message = Requires Authentication, em vez de 401.',
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
        new OA\Response(
            response: '404',
            ref: '#/components/responses/NotFound',
        ),
    ],
    parameters: [
        new OA\Parameter(
            ref: '#/components/parameters/Nome',
        ),
    ],
    security: [['basicAuth' => []]],
)]
#[OA\Get(
    path: '/pessoa/docentes',
    tags: ['Pessoa'],
    operationId: 'pessoaDocentes',
    summary: 'Listar docentes ativos',
    description: 'Consulta os colegiados configurados no servidor. Requer HTTP Basic. Credenciais ausentes ou inválidas retornam HTTP 200 com message = Requires Authentication, em vez de 401.',
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
    path: '/pessoa/servidores',
    tags: ['Pessoa'],
    operationId: 'pessoaServidores',
    summary: 'Listar servidores ativos',
    description: 'Consulta os colegiados configurados no servidor. Requer HTTP Basic. Credenciais ausentes ou inválidas retornam HTTP 200 com message = Requires Authentication, em vez de 401.',
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
    path: '/pessoa/estagiarios',
    tags: ['Pessoa'],
    operationId: 'pessoaEstagiarios',
    summary: 'Listar estagiários ativos',
    description: 'Consulta os colegiados configurados no servidor. Requer HTTP Basic. Credenciais ausentes ou inválidas retornam HTTP 200 com message = Requires Authentication, em vez de 401.',
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
    path: '/pessoa/docentes/seniores',
    tags: ['Pessoa'],
    operationId: 'pessoaDocentes_seniores',
    summary: 'Listar docentes aposentados seniores',
    description: 'Consulta os colegiados configurados no servidor. Requer HTTP Basic. Credenciais ausentes ou inválidas retornam HTTP 200 com message = Requires Authentication, em vez de 401.',
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
    path: '/pessoa/email/{codpes}',
    tags: ['Pessoa'],
    operationId: 'pessoaEmail',
    summary: 'Consultar e-mail principal',
    description: 'Retorna e-mail principal como string JSON ou false quando não encontrado. Requer HTTP Basic. Credenciais ausentes ou inválidas retornam HTTP 200 com message = Requires Authentication, em vez de 401.',
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
                                ref: '#/components/schemas/TextoOuAusente',
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
                            value: '',
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
    path: '/pessoa/procura_ativo/',
    tags: ['Pessoa'],
    operationId: 'pessoaProcuraAtivo',
    summary: 'Pesquisar pessoas ativas pelo número USP ou nome',
    description: 'Retorna linhas posicionais: [número USP, nome, sexo, e-mail]. Sem q, pode retornar corpo vazio. Requer HTTP Basic. Credenciais ausentes ou inválidas retornam HTTP 200 com message = Requires Authentication, em vez de 401.',
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
                                ref: '#/components/schemas/PessoasAtivas',
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
                            value: [['', '', '', '']],
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
        new OA\Response(
            response: '404',
            ref: '#/components/responses/NotFound',
        ),
    ],
    parameters: [
        new OA\Parameter(
            ref: '#/components/parameters/CodigoOuNome',
        ),
    ],
    security: [['basicAuth' => []]],
)]
final class Pessoa
{
}
