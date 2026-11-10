# Replicado-WS

Web Service para acesso aos dados do Replicado USP

## Configuração

- Copiar o arquivo config/config.sample.php para config/config.php
- Inserir no arquivo config.php
  - Domínio de acesso ao Web Service
  - Credenciais para acesso à base de dados de replicacao da USP
- Configurar o Apache para apontar para a pasta `www/`
- Instalar as dependências do composer
  - `composer update`

Para unidades com mais de um colegiado, configure o código principal e a lista
conforme a [documentação do Replicado](https://github.com/uspdev/replicado#explica%C3%A7%C3%B5es-das-vari%C3%A1veis):

```php
putenv('REPLICADO_CODUNDCLG=18');
putenv('REPLICADO_CODUNDCLGS=18,97');
```

As rotas por unidade de pessoas e pós-graduação consultam todos os códigos de
`REPLICADO_CODUNDCLGS`. Se essa variável estiver ausente ou vazia, usam
`REPLICADO_CODUNDCLG`, aceitando também listas de configurações antigas.
Espaços e códigos repetidos são normalizados; os códigos devem ser inteiros
positivos, sem itens vazios.

A [adaptação de colegiados](src/Colegiados.php) consulta cada código separadamente
para atender também aos métodos da biblioteca que exigem uma única unidade.
Os resultados mantêm a ordem dos colegiados e de cada consulta, removendo apenas
registros idênticos e preservando vínculos distintos da mesma pessoa. As áreas
continuam agrupadas por curso, e a verificação de matrícula retorna verdadeiro
se encontrar vínculo em qualquer colegiado. O cache das consultas por unidade
usa chaves distintas para cada código.

A API faz uso do uspdev/cache. Essa biblioteca de cache funciona com o memcached e ele precisa ser instalado e configurado. Para mais informações consulte http://github.com/uspdev/cache.

Para executar com o cache desabilitado, defina em `config/config.php`:

```php
putenv('USPDEV_CACHE_DISABLE=1');
putenv('REPLICADO_USAR_CACHE=false');
```

## Updates

11/2026 — Atualizadas as dependências para Replicado 1.28.9, FlightPHP 3.19.5,
`uspdev/cache` 1.0.0 e Illuminate Collections 13.40.0, componente da linha
Laravel 13. O conjunto atual de dependências requer PHP 8.3 ou superior.

2/2022 - deixou de usar a contante UNIDADE para usar a variável de ambiente REPLICADO_CODUNDCLG. Atualizeo arquivo de configuraçãoapropriadamente.

## Testes

Há alguns testes simples de verificação do funcionamento. Longe de ser unit teste ou algo assim mas se rodar direitinho quer dizer que as configurações gerais estão corretas.

```bash
php test/not_found.php
php test/require_auth.php
```

## Utilização

Acessar URL do serviço desejado com os parâmetros necessários. O retorno dos dados será em JSON.

### Swagger UI e OpenAPI

Acesse `DOMINIO/docs` para visualizar todas as rotas e executar consultas pelo
Swagger UI. A especificação OpenAPI 3.0.3 fica em `DOMINIO/openapi.json`, com a
URL base desta instalação preenchida automaticamente, inclusive quando a API
é publicada em um subdiretório. Os dois endereços também aparecem no índice `/`.

1. Abra a rota desejada e clique em **Try it out**.
2. Preencha os parâmetros obrigatórios. Número USP (`codpes`), nome, e-mail e
   credenciais ficam vazios; `codcur` vem com o exemplo `18070` (Engenharia).
   Esse exemplo não altera o filtro padrão da API.
3. Para rotas com cadeado, use **Authorize** e informe o usuário e a senha da API
   configurados no servidor. A autenticação é HTTP Basic, sem emissão de token.
4. Clique em **Execute** para enviar uma consulta real à API. Os exemplos de
   resposta são ilustrativos e não foram coletados do banco.


### Exemplo 1

Acessando o endereço `http://{dominio}/` pelo navegador, vai retornar

```
{
    "posgraduacao_url": "http://{dominio}/posgraduacao",
    "pessoa_url": "http://{dominio}/pessoa",
    "login_url": "http://{dominio}/login",
    "logout_url": "http://{dominio}/logout"
}
```

### Exemplo 2

Para mostrar os programas de Pós-Graduação da unidade devemos acessar:

```
http://{dominio}/posgraduacao/programas/
```

## Consumidores

Há alguns exemplos de aplicações consumindo dados da API em <code>/consumer</code>.

## Informações disponíveis

Em princípio estarão disponíveis os métodos do uspdev/replicado mas nem todos estão implementados.

Navegando pela API ele mostrará os endpoints disponíveis com a respectiva documentação.

[Lista de endpoints disponíveis](doc/endpoints.md)

## Contribuindo nesse projeto

Ao inserir um novo endpoint ou modificar alguma coisa é importante atentar ao `$help` do endpoint. Essa documentação é exibida para o consumidor e é usada para gerar o arquivo `doc/endpoints.md`.

Para atualizar esse arquivo rode `php doc-generator.php` que ele irá coletar a documentação de fato exibida considerando o config.php local.

Antes de mexer no código abra uma issue e discuta com os mantenedores. Só depois faça um pull request seguindo o modelo fork->branch_da_issue->pull request.
