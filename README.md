# Gestão RH

Sistema de gestão de recursos humanos que permite aos funcionários submeter pedidos de RH (férias, horas extras, justificações, etc.) e acompanhar o seu estado através de um fluxo de aprovação hierárquico.

---

## Índice

- [Visão Geral](#visão-geral)
- [Tecnologias](#tecnologias)
- [Pré-requisitos](#pré-requisitos)
- [Arrancar o Projeto](#arrancar-o-projeto)
- [Variáveis de Ambiente](#variáveis-de-ambiente)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Tipos de Pedido](#tipos-de-pedido)
- [Fluxo de Aprovação](#fluxo-de-aprovação)
- [Perfis de Utilizador](#perfis-de-utilizador)
- [API](#api)

---

## Visão Geral

O **Gestão RH** é uma aplicação web fullstack que digitaliza o processo de submissão e aprovação de pedidos de recursos humanos. Os funcionários submetem pedidos através de formulários específicos por tipo, e esses pedidos percorrem um fluxo de aprovação automático baseado no tipo de pedido e nos perfis dos aprovadores.

**Funcionalidades principais:**

- 14 tipos de pedido com formulários específicos
- Fluxo de aprovação com múltiplos níveis hierárquicos
- Histórico completo de cada pedido
- Upload e download de anexos por pedido
- Dashboard com resumo adaptado ao perfil do utilizador
- Gestão de organizações, setores, utilizadores e períodos (administração)
- Controlo de acesso baseado em roles e permissões (RBAC)

---

## Tecnologias

### Backend
| Tecnologia | Versão |
|---|---|
| PHP | ^8.2 |
| Laravel | ^12.0 |
| Laravel Sanctum | ^4.3 |
| Spatie Laravel Permission | ^6.25 |
| MySQL | 8.x |

### Frontend
| Tecnologia | Versão |
|---|---|
| Angular | ^19.2 |
| Angular Material | ^19.2 |
| TypeScript | ~5.7 |
| RxJS | ~7.8 |

---

## Pré-requisitos

Antes de começar, certifica-te de que tens instalado:

- **PHP** >= 8.2
- **Composer** >= 2.x
- **Node.js** >= 20.x e **npm** >= 10.x
- **MySQL** >= 8.x
- **Angular CLI** >= 19.x (`npm install -g @angular/cli`)

---

## Arrancar o Projeto

### 1. Clonar o repositório

```bash
git clone <url-do-repositório>
cd gestaorh
```

### 2. Configurar o Backend

```bash
cd backend

# Instalar dependências PHP
composer install

# Copiar e configurar o ficheiro de ambiente
cp .env.example .env

# Gerar a chave da aplicação
php artisan key:generate
```

Edita o ficheiro `.env` com as credenciais da tua base de dados (ver [Variáveis de Ambiente](#variáveis-de-ambiente)).

```bash
# Criar a base de dados no MySQL
mysql -u root -p -e "CREATE DATABASE gestao_rh CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Importar o schema de negócio
mysql -u root gestao_rh < database/sql/schema_negocio.sql

# Executar as migrations do Laravel (Sanctum, Permissions, Cache, Jobs, Sessions)
php artisan migrate

# Popular a base de dados com dados de referência e utilizador administrador
php artisan db:seed
```

### 3. Configurar o Frontend

```bash
cd ../frontend

# Instalar dependências Node
npm install
```

### 4. Arrancar os servidores

**Terminal 1 — Backend:**
```bash
cd backend
php artisan serve
# API disponível em http://127.0.0.1:8000
```

**Terminal 2 — Frontend:**
```bash
cd frontend
ng serve
# Aplicação disponível em http://localhost:4200
```

> O frontend está configurado com um proxy que redireciona `/api/*` para `http://127.0.0.1:8000`, pelo que não é necessária qualquer configuração adicional de CORS em desenvolvimento.

### Credenciais padrão (após seed)

| Campo | Valor |
|---|---|
| Email | `admin@gestaorh.pt` |
| Password | `password` |
| Perfil | Diretora Executiva |

---

## Variáveis de Ambiente

Ficheiro: `backend/.env`

```dotenv
APP_NAME="Gestão RH"
APP_ENV=local
APP_KEY=                        # Gerado por: php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Europe/Lisbon

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestao_rh
DB_USERNAME=root
DB_PASSWORD=                    # Password do teu MySQL

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

FILESYSTEM_DISK=local
```

---

## Estrutura do Projeto

```
gestaorh/
├── backend/                        # API Laravel
│   ├── app/
│   │   ├── Actions/Pedido/         # Lógica de negócio por operação
│   │   ├── Enums/                  # EstadoPedido, TipoPedido, TipoUtilizador
│   │   ├── Exceptions/             # WorkflowException, PedidoException
│   │   ├── Http/
│   │   │   ├── Controllers/API/V1/ # Controllers REST
│   │   │   ├── Requests/           # Validação por tipo de pedido
│   │   │   └── Resources/          # Transformação de resposta JSON
│   │   ├── Models/                 # 22 modelos Eloquent
│   │   ├── Policies/               # Autorização por recurso
│   │   ├── Repositories/           # Abstração de acesso a dados
│   │   └── Services/               # Orquestração de operações
│   ├── database/
│   │   ├── migrations/             # Migrations Laravel
│   │   ├── seeders/                # Dados de referência e utilizador admin
│   │   └── sql/schema_negocio.sql  # Schema completo de negócio
│   └── routes/api.php              # Definição de todas as rotas
│
└── frontend/                       # SPA Angular
    └── src/app/
        ├── core/
        │   ├── guards/             # AuthGuard, GuestGuard, RoleGuard
        │   ├── interceptors/       # Auth token, tratamento de erros
        │   ├── models/             # Interfaces TypeScript
        │   └── services/           # Serviços HTTP
        ├── features/
        │   ├── admin/              # Organizações, setores, utilizadores, períodos
        │   ├── aprovacoes/         # Lista e ações de aprovação
        │   ├── auth/               # Login
        │   ├── dashboard/          # Resumo por perfil
        │   ├── pedidos/            # Lista, detalhe e criação de pedidos
        │   └── perfil/             # Perfil do utilizador
        └── shared/                 # Componentes reutilizáveis (shell, dialogs)
```

---

## Tipos de Pedido

| Tipo | Descrição |
|---|---|
| Horas Extras | Registo de horas trabalhadas além do horário normal |
| Justificação de Faltas | Justificação de ausências ao trabalho |
| Marcação de Férias | Pedido de marcação de período de férias |
| Alteração de Férias | Alteração de férias já marcadas |
| Troca de Horário | Troca de horário com um colega (requer aprovação do colega) |
| Troca de Folga com Instituição | Troca de folga com a instituição |
| Interrupção de Atividade | Pedido de interrupção de atividade |
| Folga de Aniversário | Pedido de folga no dia de aniversário |
| Assiduidade | Registo de ausência de picagem |
| Licença de Nojo | Licença por falecimento de familiar |
| Formação | Pedido de participação em formação |
| Motivos Académicos | Ausência por motivos académicos |
| Compensação de Entrada Tardia | Justificação e compensação de entrada tardia |
| Compensação de Saída Antecipada | Justificação e compensação de saída antecipada |

---

## Fluxo de Aprovação

```
                        ┌─────────────┐
                        │   RASCUNHO  │
                        └──────┬──────┘
                               │ submeter
                               ▼
                        ┌─────────────┐
                        │   PENDENTE  │
                        └──────┬──────┘
                               │
               ┌───────────────┴───────────────┐
               │ (Troca de Horário)             │ (restantes tipos)
               ▼                               ▼
  ┌────────────────────────┐     ┌────────────────────────────┐
  │  EM APROVAÇÃO (Colega) │     │ EM APROVAÇÃO (Executiva)   │
  └────────────┬───────────┘     └────────────┬───────────────┘
               │ aprovado colega              │ aprovado
               ▼                             ▼
  ┌────────────────────────┐        ┌──────────────┐
  │ EM APROVAÇÃO (Execut.) │───────►│   APROVADO   │
  └────────────────────────┘        └──────────────┘

Em qualquer estado intermédio:
  → REJEITADO  (por aprovador com permissão)
  → CANCELADO  (pelo próprio utilizador)
  → RASCUNHO   (devolvido pela Diretora Executiva)
```

---

## Perfis de Utilizador

| Perfil | Permissões |
|---|---|
| **Funcionário** | Criar, submeter e acompanhar os seus próprios pedidos; aprovar como colega em Trocas de Horário |
| **Diretor Técnico** | Tudo do Funcionário + visualizar pedidos do setor |
| **Diretora Executiva** | Acesso total — aprovar, rejeitar, devolver qualquer pedido; gerir organizações, setores, utilizadores e períodos |

---

## API

A API segue o padrão REST e responde sempre em JSON.

**Base URL:** `http://127.0.0.1:8000/api/v1`

**Autenticação:** Bearer Token (Laravel Sanctum)

### Endpoints principais

| Método | Endpoint | Descrição |
|---|---|---|
| `POST` | `/auth/login` | Autenticação |
| `POST` | `/auth/logout` | Terminar sessão |
| `GET` | `/auth/me` | Utilizador autenticado |
| `GET` | `/pedidos` | Listar pedidos |
| `GET` | `/pedidos/{id}` | Detalhe do pedido |
| `POST` | `/pedidos/{tipo}` | Criar pedido por tipo |
| `POST` | `/pedidos/{id}/submeter` | Submeter pedido |
| `POST` | `/pedidos/{id}/aprovar` | Aprovar pedido |
| `POST` | `/pedidos/{id}/rejeitar` | Rejeitar pedido |
| `POST` | `/pedidos/{id}/devolver` | Devolver pedido |
| `GET` | `/pedidos/{id}/historico` | Histórico do pedido |
| `POST` | `/pedidos/{id}/anexos` | Upload de anexo |
| `GET` | `/dashboard/resumo` | Resumo do dashboard |
| `PUT` | `/perfil` | Atualizar perfil |

> Endpoints de administração (organizações, setores, utilizadores, períodos) requerem o perfil **Diretora Executiva**.
