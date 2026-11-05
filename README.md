# DTN API

API para sincronização de mensagens DTN, desenvolvida com Laravel.

## Requisitos

- Git
- Docker Desktop (Windows/macOS) ou Docker Engine com o plugin Docker Compose
  (Linux)
- Docker em execução

PHP, Composer, Node.js e MySQL não precisam estar instalados no computador: os
serviços e dependências da aplicação são preparados pelos containers.

## Instalação e inicialização

### 1. Clonar o repositório

```sh
git clone https://github.com/Damacenno/dtn-api.git
cd dtn-api
```

### 2. Criar e configurar o `.env`

No PowerShell:

```powershell
Copy-Item .env.example .env
```

No macOS/Linux:

```sh
cp .env.example .env
```

Confirme no `.env` as configurações usadas pelo Docker Compose:

```dotenv
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=dtn_database
DB_USERNAME=dtn_user
DB_PASSWORD=dtn_password
DB_ROOT_PASSWORD=dtn_root_password
```

Essas credenciais são apenas para desenvolvimento local; use senhas próprias
fora desse ambiente. O arquivo `.env` contém configurações locais e não deve
ser enviado ao Git.

### 3. Construir e iniciar os containers

Na raiz do projeto:

```sh
docker compose up --build -d
```

O Compose inicia o MySQL e aguarda o banco ficar saudável antes de iniciar a
API. O container da aplicação gera uma `APP_KEY` na primeira inicialização e
executa automaticamente as migrations com `php artisan migrate --force`.
Assim, o banco `dtn_database` e a tabela `dtn_messages` são criados sem uma
etapa manual.

### 4. Confirmar que a API está disponível

Confira o estado dos containers e os logs:

```sh
docker compose ps
docker compose logs -f app
```

Quando o container `app` estiver em execução, verifique a rota de saúde:

```sh
curl http://localhost:8000/up
```

A API estará disponível em `http://localhost:8000`. Se a porta 8000 já estiver
ocupada, defina `APP_PORT` no `.env` (por exemplo, `APP_PORT=8080`) e reinicie
os containers. No PowerShell, use `curl.exe` nos exemplos abaixo para evitar o
alias `curl` do Windows.

## Migrations

As migrations são executadas automaticamente na inicialização do container da
API, depois que o serviço MySQL passa no healthcheck. Se precisar executá-las
manualmente:

```sh
docker compose exec app php artisan migrate --force
```

Para consultar o estado:

```sh
docker compose exec app php artisan migrate:status
```

## Endpoints

### `POST /api/dtn/sync`

Envia mensagens e consulta quais IDs já foram entregues:

```sh
curl -X POST http://localhost:8000/api/dtn/sync \
  -H "Content-Type: application/json" \
  --data-raw '{"device_id":"device-demo","messages":[],"known_ids":[]}'
```

Cada mensagem aceita os campos `id`, `originDevice`, `code`, `createdAt` e
`expiresAt`. Os campos `createdAt` e `expiresAt` são timestamps Unix em
milissegundos. Os códigos aceitos são `001` a `008`. `messages` e `known_ids`
devem ser enviados, mesmo quando forem listas vazias.

### `GET /api/dtn/delivered-ids`

Lista IDs de mensagens recebidas. Opcionalmente, use `after_id` como cursor e
`limit` para limitar a página (máximo de 500):

```sh
curl "http://localhost:8000/api/dtn/delivered-ids?after_id=0&limit=100"
```

## Operações úteis

Parar os containers sem apagar os dados:

```sh
docker compose down
```

O banco MySQL e os arquivos de storage ficam em volumes Docker e são mantidos
por esse comando. Para apagar também os dados persistidos:

```sh
docker compose down -v
```
