# 🧪 Sistema GrapH — Registrador de pH · IFSEMG

> Sistema web acadêmico-profissional para controle, gerenciamento e histórico de medições de pH e temperatura de amostras de laboratório, desenvolvido para o **Instituto Federal do Sudeste de Minas Gerais (IFSEMG)**.

---

## 📋 Índice

- [Descrição do Projeto](#-descrição-do-projeto)
- [Funcionalidades](#-funcionalidades)
- [Tecnologias Utilizadas](#%EF%B8%8F-tecnologias-utilizadas)
- [Dependências e Versões](#-dependências-e-versões)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Variáveis de Ambiente](#%EF%B8%8F-variáveis-de-ambiente)
- [Avisos de Segurança (.env)](#%EF%B8%8F-avisos-de-segurança-env)
- [Como Subir com Docker](#-como-subir-com-docker)
- [Como Executar Localmente (WAMP/XAMPP)](#-como-executar-localmente-wampxampp)
- [Banco de Dados](#%EF%B8%8F-banco-de-dados)
- [Primeiro Acesso](#-primeiro-acesso)

---

## 📖 Descrição do Projeto

O **Sistema GrapH** é uma aplicação web desenvolvida em **PHP puro** com o objetivo de informatizar e organizar o registro de medições de pH e temperatura realizadas em laboratório. Ele permite que técnicos e pesquisadores do IFSEMG registrem leituras de diferentes amostras e gerenciem usuários.

O sistema foi projetado com foco em uma interface simples e direta para o ambiente de laboratório.
---

## ✨ Funcionalidades

### 🔐 Autenticação e Controle de Acesso
| Funcionalidade | Arquivo | Descrição |
|---|---|---|
| Login / Logout | `login.php`, `logout.php` | Sessões seguras em PHP |
| Autenticação de rota | `auth.php` | Protege páginas de usuários não logados |
| Cadastro de usuário | `cadastro.php` | Usuários podem se auto-registrar |
| Painel administrativo | `admin.php` | Aprovação e gerenciamento de contas |
| Níveis de acesso | — | Papéis `user` (comum) e `admin` |

### 📊 Gestão de Medições de pH (CRUD)
| Operação | Arquivo | Descrição |
|---|---|---|
| **Create** – Registrar leitura | `medicoes_cadastrar.php` | pH, temperatura, amostra, observação e data |
| **Read** – Histórico | `medicoes_listar.php` | Tabela com paginação e filtros |
| **Update** – Editar leitura | `medicoes_editar.php` | Correção de dados; rastreamento de quem editou |
| **Delete** – Excluir leitura | `medicoes_excluir.php` | Remoção de registros |
| Painel admin de medições | `admin_medicoes.php` | Visão global para administradores |

---

## 🛠️ Tecnologias Utilizadas

| Categoria | Tecnologia | Versão |
|---|---|---|
| Linguagem backend | PHP | 8.2 |
| Banco de dados | MySQL | 8.0 |
| Servidor web | Apache (embutido na imagem PHP) | 2.4+ |
| Containerização | Docker | 20.10+ |
| Orquestração | Docker Compose | 2.x (plugin v2) |
| Frontend | HTML5 + CSS3 (Vanilla) | — |
| Extensão PHP | `mysqli` | nativa do PHP 8.2 |

---

## 📦 Dependências e Versões

### Ambiente de Produção / Docker

| Dependência | Versão mínima | Observação |
|---|---|---|
| **Docker Engine** | 20.10+ | [Instalar Docker](https://docs.docker.com/get-docker/) |
| **Docker Compose** | 2.0+ (plugin `docker compose`) | Já incluso no Docker Desktop |
| **Imagem `php:8.2-apache`** | 8.2 | Baixada automaticamente |
| **Imagem `mysql:8.0`** | 8.0 | Baixada automaticamente |

> **Nota:** Versões mais antigas do Docker Compose (standalone `docker-compose`) também são suportadas, mas o uso do plugin integrado (`docker compose`) é recomendado.

### Ambiente Local (WAMP / XAMPP)

| Dependência | Versão mínima | Observação |
|---|---|---|
| **PHP** | 8.1+ | Com extensão `mysqli` habilitada |
| **MySQL / MariaDB** | 8.0 / 10.5+ | — |
| **Apache** | 2.4+ | Com `mod_rewrite` habilitado |
| **WAMP64** | 3.3+ | [wampserver.com](https://www.wampserver.com/) |
| **XAMPP** | 8.2+ | [apachefriends.org](https://www.apachefriends.org/) |

---

## 📁 Estrutura do Projeto

```
Sistema-Registrador-de-pH---IFSEMG/
├── docker/
│   ├── Dockerfile            # Imagem PHP 8.2 + Apache + extensão mysqli
│   └── docker-compose.yml    # Orquestra os containers web e db
│
├── imagens/                  # Assets de imagem do front-end
│
├── .env                      # ⚠️ Variáveis de ambiente (NÃO versionar!)
├── .env.example              # Modelo seguro das variáveis (versionado)
├── .gitignore                # Arquivos ignorados pelo Git
│
├── banco.sql                 # Script de criação do banco e dados de exemplo
├── conexao.php               # Lê o .env e abre a conexão com o MySQL
├── auth.php                  # Middleware de autenticação
├── index.php                 # Redireciona para login ou dashboard
├── login.php                 # Tela de login
├── logout.php                # Encerra a sessão
├── cadastro.php              # Cadastro de novos usuários
├── admin.php                 # Painel de gerenciamento de usuários
├── admin_medicoes.php        # Painel de medições para admin
├── medicoes_cadastrar.php    # Registrar nova medição
├── medicoes_listar.php       # Histórico e listagem de medições
├── medicoes_editar.php       # Editar uma medição existente
├── medicoes_excluir.php      # Excluir uma medição
├── footer.php                # Componente de rodapé reutilizável
└── estilo.css                # Folha de estilos principal
```

---

## ⚙️ Variáveis de Ambiente

O projeto usa um arquivo `.env` na **raiz do projeto** para centralizar toda a configuração sensível.

### Configuração

1. Copie o arquivo de exemplo:
   ```bash
   # Linux / macOS / Git Bash
   cp .env.example .env

   # Windows (PowerShell)
   Copy-Item .env.example .env
   ```

2. Abra o arquivo `.env` e preencha com seus valores reais:

   ```dotenv
   # ──────────────────────────────────────────
   # Configurações do Banco de Dados
   # ──────────────────────────────────────────

   # Host do banco. Em Docker, o Compose sobrescreve automaticamente para "db"
   DB_HOST=localhost

   # Usuário do banco de dados (não use "root" em produção)
   DB_USER=seu_usuario_aqui

   # Senha do usuário do banco de dados
   DB_PASS=sua_senha_segura_aqui

   # Nome do banco de dados
   DB_NAME=phmetro

   # Senha do usuário root do MySQL (usada apenas pelo container Docker)
   DB_ROOT_PASS=sua_senha_root_segura_aqui

   # ──────────────────────────────────────────
   # Configurações de Portas do Docker
   # ──────────────────────────────────────────

   # Porta do host para acessar o sistema web (padrão: 8080)
   DOCKER_WEB_PORT=8080

   # Porta do host para acessar o banco de dados (padrão: 3306)
   DOCKER_DB_PORT=3306
   ```

### Referência de Variáveis

| Variável | Obrigatória | Padrão (fallback) | Descrição |
|---|:---:|---|---|
| `DB_HOST` | ✅ | `localhost` | Endereço do servidor MySQL |
| `DB_USER` | ✅ | `graph` | Usuário de acesso ao banco |
| `DB_PASS` | ✅ | — | Senha do usuário do banco |
| `DB_NAME` | ✅ | `phmetro` | Nome do banco de dados |
| `DB_ROOT_PASS` | ✅ (Docker) | — | Senha root do MySQL (apenas Docker) |
| `DOCKER_WEB_PORT` | ❌ | `8080` | Porta local do servidor web |
| `DOCKER_DB_PORT` | ❌ | `3306` | Porta local do banco de dados |

> **Atenção:** Em ambiente Docker, o `DB_HOST` é automaticamente sobrescrito para `db` pelo `docker-compose.yml`. Você **não** precisa alterá-lo manualmente.

---

## ⚠️ Avisos de Segurança (.env)

> **Leia com atenção antes de versionar ou compartilhar o projeto.**

### 🔴 NUNCA versione o arquivo `.env`

O arquivo `.env` contém **credenciais reais** (senhas de banco de dados). Expô-lo em repositórios públicos (GitHub, GitLab) é uma **falha crítica de segurança** que pode comprometer todo o ambiente.

**Adicione `.env` ao seu `.gitignore` imediatamente:**

```gitignore
# Arquivo de variáveis de ambiente — NUNCA versionar
.env
```

Verifique se ele não foi acidentalmente adicionado ao Git:
```bash
git status
# Se .env aparecer como "new file" ou "modified", remova-o do tracking:
git rm --cached .env
git commit -m "chore: remove .env do controle de versão"
```

---

### 🟡 Senhas fracas são um risco

Evite senhas óbvias como `123456`, `root`, `admin` ou `senha`. Use senhas geradas aleatoriamente:

```bash
# Gerar uma senha segura de 32 caracteres (Linux/macOS/Git Bash)
openssl rand -base64 32
```

---

### 🟡 Credenciais expostas no código-fonte

O arquivo `conexao.php` possui um **fallback de credenciais hardcoded** nas linhas `getenv(... ?: '...')`. Isso serve apenas para desenvolvimento local e **deve ser removido ou neutralizado em produção**. Configure sempre o `.env` corretamente para que o fallback nunca seja acionado em ambiente real.

---

### 🟡 Cuidado ao compartilhar o projeto

- Ao enviar o projeto para colegas ou professores, entregue somente o `.env.example` — **nunca o `.env` real**.
- Em hospedagens (cPanel, Hostinger, Railway), configure as variáveis de ambiente pelo painel da plataforma, sem subir o `.env` via FTP ou Git.

---

### 🟢 O arquivo `.env.example` é seguro para versionar

O `.env.example` contém **apenas placeholders** sem valores reais. Ele serve como documentação viva do que precisa ser configurado e **deve ser mantido no repositório**.

---

## 🐳 Como Subir com Docker

O Docker levanta toda a infraestrutura (servidor PHP/Apache + banco MySQL) com um único comando, sem necessidade de instalar PHP ou MySQL localmente.

### Pré-requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado e **em execução**.
- Arquivo `.env` configurado na raiz do projeto (veja a seção acima).

### Passo a Passo Completo

**1. Clone o repositório:**
```bash
git clone https://github.com/seu-usuario/Sistema-Registrador-de-pH---IFSEMG.git
cd Sistema-Registrador-de-pH---IFSEMG
```

**2. Configure as variáveis de ambiente:**
```bash
# Linux / macOS / Git Bash
cp .env.example .env

# Windows (PowerShell)
Copy-Item .env.example .env
```
> ✏️ Edite o `.env` com suas senhas antes de continuar.

**3. Navegue até a pasta Docker:**
```bash
cd docker
```

**4. Construa as imagens e suba os containers em segundo plano:**
```bash
docker compose up -d --build
```
> - `--build` → reconstrói a imagem PHP com as últimas alterações do projeto.  
> - `-d` → executa em background (detached), liberando o terminal.

**5. Acompanhe a inicialização do banco (aguarde a mensagem `ready for connections`):**
```bash
docker compose logs -f db
```
> Pressione `Ctrl+C` para sair dos logs. Os containers continuam rodando.

**6. Acesse o sistema no navegador:**
```
http://localhost:8080
```
> Substitua `8080` pela porta definida em `DOCKER_WEB_PORT` no seu `.env`.

---

### Outros Comandos Úteis

| Ação | Comando (dentro da pasta `docker/`) |
|---|---|
| Subir containers (com rebuild) | `docker compose up -d --build` |
| Subir containers (sem rebuild) | `docker compose up -d` |
| Parar os containers (sem remover) | `docker compose stop` |
| Parar e remover containers | `docker compose down` |
| **Remover containers E o volume do banco** | `docker compose down -v` |
| Ver logs em tempo real (todos) | `docker compose logs -f` |
| Ver logs de um serviço específico | `docker compose logs -f web` |
| Ver status dos containers | `docker compose ps` |
| Acessar terminal do container web | `docker compose exec web bash` |
| Acessar terminal do container do banco | `docker compose exec db bash` |
| Conectar ao MySQL dentro do container | `docker compose exec db mysql -u root -p` |

> **⚠️ Atenção:** O comando `docker compose down -v` **apaga permanentemente todos os dados do banco**. Use somente para resetar o ambiente do zero.

---

### Visão Geral dos Containers

| Serviço | Container | Imagem base | Porta host → container |
|---|---|---|---|
| `web` | `phmetro_web` | `php:8.2-apache` (customizada) | `DOCKER_WEB_PORT` → `80` |
| `db` | `phmetro_db` | `mysql:8.0` | `DOCKER_DB_PORT` → `3306` |

O `banco.sql` é executado **automaticamente** pelo MySQL na **primeira** criação do container `db`. Nas execuções seguintes (com volume existente), o script não é re-executado.

---

## 🖥️ Como Executar Localmente (WAMP/XAMPP)

1. Clone ou baixe este repositório no diretório público do servidor:
   - **WAMP64:** `C:\wamp64\www\`
   - **XAMPP:** `C:\xampp\htdocs\`

2. Acesse o phpMyAdmin e importe o arquivo `banco.sql`.
   > 💡 **Hospedagens online (cPanel, Hostinger):** remova as 3 primeiras linhas do `banco.sql` (`DROP DATABASE`, `CREATE DATABASE`, `USE`), crie o banco manualmente no painel e importe o arquivo editado.

3. Configure o arquivo `.env` na raiz do projeto com as credenciais do seu servidor local.

4. Inicie o Apache e o MySQL pelo painel do WAMP/XAMPP.

5. Acesse no navegador:
   ```
   http://localhost/Sistema-Registrador-de-pH---IFSEMG/
   ```

---

## 🗄️ Banco de Dados

O banco `phmetro` é composto por duas tabelas relacionais em `InnoDB` com charset `utf8mb4`:

### Tabela `usuarios`
| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT UNSIGNED PK | Identificador único |
| `nome` | VARCHAR(100) | Nome completo |
| `email` | VARCHAR(150) UNIQUE | E-mail (login) |
| `senha` | VARCHAR(255) | Hash `bcrypt` da senha |
| `aprovado` | TINYINT(1) | `0` = pendente · `1` = aprovado |
| `ativo` | TINYINT(1) | `0` = inativo · `1` = ativo |
| `role` | VARCHAR(20) | `user` ou `admin` |
| `criado_em` | DATETIME | Data de cadastro |

### Tabela `medicoes_ph`
| Coluna | Tipo | Descrição |
|---|---|---|
| `id` | INT UNSIGNED PK | Identificador único |
| `usuario_id` | INT UNSIGNED FK | Referência ao usuário que criou |
| `valor_ph` | DECIMAL(4,2) | Valor de pH medido |
| `temperatura` | DECIMAL(5,2) | Temperatura em °C |
| `amostra` | VARCHAR(100) | Nome da amostra analisada |
| `observacao` | TEXT | Observações adicionais |
| `data_medicao` | DATETIME | Data/hora real da leitura |
| `criado_em` | DATETIME | Data de registro no sistema |
| `atualizado_em` | DATETIME | Data da última edição |
| `atualizado_por` | INT UNSIGNED | ID do usuário que editou |

---

## 🔑 Primeiro Acesso

O script `banco.sql` cria automaticamente um **usuário administrador padrão** e três medições de exemplo:

| Campo | Valor |
|---|---|
| **E-mail** | `graphadmin@gmail.com` |
| **Senha** | (verifique com o responsável pelo projeto) |
| **Role** | `admin` |

> Se não souber a senha (o banco armazena apenas o hash `bcrypt`), cadastre um novo usuário pela tela de registro e peça a um administrador para aprová-lo e promovê-lo via painel (`admin.php`).

---

## 👥 Contribuição

Este é um projeto acadêmico do IFSEMG. Para dúvidas, sugestões ou problemas, abra uma **Issue** ou envie um **Pull Request** neste repositório.

---

<p align="center">
  Desenvolvido com ❤️ para o <strong>IFSEMG</strong>
</p>
