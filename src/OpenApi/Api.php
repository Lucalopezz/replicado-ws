<?php

namespace Uspdev\Replicado_ws\OpenApi;

use OpenApi\Attributes as OA;

// Metadados e componentes compartilhados. O JSON é gerado pelo swagger-php.
#[OA\OpenApi(
    openapi: '3.0.3',
    security: [],
)]
#[OA\Info(
    title: 'Replicado-WS',
    version: '1.0.0',
    description: 'API de consulta aos dados do Replicado USP. Número USP (codpes), nomes, e-mails e credenciais ficam vazios para preenchimento manual; preencha os campos obrigatórios antes de executar. O exemplo de codcur é 18070 (Engenharia), informado para esta instalação; não é um filtro automático da API. codare e numpat ficam vazios até que sejam informados valores de teste. Os exemplos de resposta são ilustrativos, não foram extraídos da base e mostram apenas parte dos campos. Os campos variam conforme a consulta e a versão do Replicado. As rotas por unidade usam os colegiados configurados no servidor; não há parâmetro de unidade na URL. Quando IP_ACCESS_LIST está configurado, o acesso pode retornar 403. Rotas locais definidas por ROTAS_LOCAIS não estão incluídas. As consultas estão documentadas como GET; rotas legadas sem método explícito também podem aceitar outros métodos.',
)]
#[OA\Server(
    url: '/',
    description: 'Ajuste para a URL base da instalação ao importar este arquivo; /openapi.json fornece DOMINIO automaticamente.',
)]
#[OA\Tag(
    name: 'Documentação',
    description: 'Índice e documentação interativa.',
)]
#[OA\Tag(
    name: 'Autenticação',
    description: 'HTTP Basic: use credenciais da API, não a senha pessoal USP. Envie Basic a cada consulta protegida.',
)]
#[OA\Tag(
    name: 'Pessoa',
    description: 'Pessoas, docentes, servidores e estagiários.',
)]
#[OA\Tag(
    name: 'Pós-graduação',
    description: 'Programas, áreas, alunos, orientadores e disciplinas.',
)]
#[OA\Tag(
    name: 'Graduação',
    description: 'Somente ajuda; sem consultas implementadas.',
)]
#[OA\Tag(
    name: 'Lattes',
    description: 'Identificador Lattes.',
)]
#[OA\Tag(
    name: 'Patrimônio',
    description: 'Bens patrimoniados.',
)]
#[OA\Components(
    securitySchemes: [
        new OA\SecurityScheme(
            securityScheme: 'basicAuth',
            type: 'http',
            scheme: 'basic',
            description: 'Credenciais da API configuradas em $users. Preencha manualmente no botão Authorize.',
        ),
    ],
    parameters: [
        new OA\Parameter(
            parameter: 'Codpes',
            name: 'codpes',
            in: 'path',
            required: true,
            description: 'Número USP. Preencha somente dígitos; vazio por ser dado pessoal.',
            schema: new OA\Schema(
                type: 'string',
                pattern: '^[0-9]+$',
            ),
            example: '',
        ),
        new OA\Parameter(
            parameter: 'Codcur',
            name: 'codcur',
            in: 'path',
            required: true,
            description: 'Código do curso/programa. Exemplo fornecido: 18070 (Engenharia).',
            schema: new OA\Schema(
                type: 'integer',
                minimum: 0,
            ),
            example: 18070,
        ),
        new OA\Parameter(
            parameter: 'Codare',
            name: 'codare',
            in: 'path',
            required: true,
            description: 'Código da área de concentração. Preencha com um codare retornado por /posgraduacao/areasProgramas/18070 ou informe um valor de teste. É diferente de codcur.',
            schema: new OA\Schema(
                type: 'string',
                pattern: '^[0-9]+$',
            ),
            example: '',
        ),
        new OA\Parameter(
            parameter: 'Numpat',
            name: 'numpat',
            in: 'path',
            required: true,
            description: 'Número de patrimônio, somente dígitos, sem pontos. Preencha manualmente com um valor de teste.',
            schema: new OA\Schema(
                type: 'string',
                pattern: '^[0-9]+$',
            ),
            example: '',
        ),
        new OA\Parameter(
            parameter: 'Nome',
            name: 'q',
            in: 'query',
            required: true,
            description: 'Nome ou trecho de nome. Preencha manualmente. Ausente ou vazio retorna 404.',
            schema: new OA\Schema(
                type: 'string',
                minLength: 1,
            ),
            example: '',
        ),
        new OA\Parameter(
            parameter: 'CodigoOuNome',
            name: 'q',
            in: 'query',
            required: true,
            description: 'Número USP ou trecho de nome. Preencha manualmente. Sem valor a implementação encerra a rota sem emitir JSON e pode responder com corpo vazio.',
            schema: new OA\Schema(
                type: 'string',
                minLength: 1,
            ),
            example: '',
        ),
        new OA\Parameter(
            parameter: 'Completo',
            name: 'l',
            in: 'query',
            required: false,
            description: 'Use completo para incluir detalhes. Omita para dados básicos. Outros valores também seguem a consulta básica.',
            schema: new OA\Schema(
                type: 'string',
            ),
            example: 'completo',
        ),
    ],
    schemas: [
        new OA\Schema(
            schema: 'Registro',
            type: 'object',
            additionalProperties: true,
            description: 'Registro do Replicado. Os campos variam por consulta; os exemplos mostram apenas parte do retorno.',
        ),
        new OA\Schema(
            schema: 'ListaRegistros',
            type: 'array',
            items: new OA\Items(
                ref: '#/components/schemas/Registro',
            ),
        ),
        new OA\Schema(
            schema: 'RegistroOuAusente',
            oneOf: [
                new OA\Schema(
                    type: 'object',
                    additionalProperties: true,
                    nullable: true,
                ),
                new OA\Schema(
                    type: 'boolean',
                    enum: [false],
                ),
            ],
            description: 'Registro encontrado; false ou null quando não há resultado.',
        ),
        new OA\Schema(
            schema: 'TextoOuAusente',
            oneOf: [
                new OA\Schema(
                    type: 'string',
                    nullable: true,
                ),
                new OA\Schema(
                    type: 'boolean',
                    enum: [false],
                ),
            ],
        ),
        new OA\Schema(
            schema: 'Mensagem',
            type: 'object',
            required: ['message', 'documentation_url'],
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                ),
                new OA\Property(
                    property: 'documentation_url',
                    type: 'string',
                    description: 'URL raiz da instalação.',
                ),
            ],
        ),
        new OA\Schema(
            schema: 'Ajuda',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'url',
                        type: 'string',
                    ),
                    new OA\Property(
                        property: 'descricao',
                        type: 'string',
                    ),
                ],
            ),
        ),
        new OA\Schema(
            schema: 'AreasPorPrograma',
            oneOf: [
                new OA\Schema(
                    type: 'object',
                    additionalProperties: new OA\AdditionalProperties(
                        type: 'array',
                        items: new OA\Items(
                            ref: '#/components/schemas/Registro',
                        ),
                    ),
                ),
                new OA\Schema(
                    type: 'array',
                    maxItems: 0,
                    items: new OA\Items(),
                ),
            ],
            description: 'Objeto indexado pelo código do programa, ou [] quando não houver áreas.',
        ),
        new OA\Schema(
            schema: 'PessoasAtivas',
            type: 'array',
            items: new OA\Items(
                type: 'array',
                minItems: 4,
                maxItems: 4,
                items: new OA\Items(
                    oneOf: [
                        new OA\Schema(
                            type: 'string',
                            nullable: true,
                        ),
                        new OA\Schema(
                            type: 'integer',
                        ),
                        new OA\Schema(
                            type: 'boolean',
                            enum: [false],
                        ),
                    ],
                ),
                description: 'Cada linha contém, nesta ordem: número USP, nome, sexo e e-mail. E-mail pode ser false.',
            ),
        ),
    ],
    responses: [
        new OA\Response(
            response: 'Forbidden',
            description: 'Acesso negado pelo controle de IP.',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        ref: '#/components/schemas/Mensagem',
                    ),
                    example: ['message' => 'Forbidden', 'documentation_url' => '/'],
                ),
            ],
        ),
        new OA\Response(
            response: 'NotFound',
            description: 'Rota não encontrada, parâmetro numérico inválido ou busca por nome sem q.',
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        ref: '#/components/schemas/Mensagem',
                    ),
                    example: ['message' => 'Not Found', 'documentation_url' => '/'],
                ),
            ],
        ),
    ],
)]
final class Api
{
}
