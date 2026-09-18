# Sistema GrapH - IFSEMG

Este é um sistema web acadêmico e profissional desenvolvido em PHP puro para realizar o controle, gerenciamento e histórico de medições de pH e temperatura de diferentes amostras. O sistema foi desenvolvido para facilitar rotinas de laboratório no IFSEMG, garantindo que as leituras fiquem salvas e organizadas com segurança.

## 🚀 Funcionalidades Principais

### Autenticação e Controle de Acesso
- **Login e Logout:** Sessões seguras em PHP para garantir que apenas usuários autorizados acessem os dados (`login.php`, `logout.php`, `auth.php`).
- **Níveis de Acesso (Roles):** Sistema com distinção de usuários comuns (`user`) e administradores (`admin`).
- **Cadastro de Usuários e Aprovação:** Novos usuários podem se cadastrar (`cadastro.php`), mas um administrador pode ter o poder de aprovar e gerenciar as contas ativas através do painel (`admin.php`).

### Gestão de Medições de pH (CRUD)
- **Registrar Leitura (Create):** Formulário para inserir os valores de pH, temperatura, nome da amostra, observações e data da medição (`medicoes_cadastrar.php`).
- **Histórico e Relatório (Read):** Tabela contendo todo o histórico de amostras registradas, com paginação/visualização organizada (`medicoes_listar.php`).
- **Edição (Update):** Correção de dados caso uma medição tenha sido registrada incorretamente (`medicoes_editar.php`). O sistema rastreia quem alterou (via `atualizado_por`).
- **Exclusão (Delete):** Remoção de dados do sistema (`medicoes_excluir.php`).

## 🗄️ Estrutura do Banco de Dados
O banco de dados relacional (`banco.sql`) é estruturado, no modelo InnoDB, em duas tabelas principais:
1. **`usuarios`**: Armazena as credenciais, nível de acesso (`role`), status de aprovação (`aprovado`) e registro dos usuários.
2. **`medicoes_ph`**: Armazena todos os atributos de uma leitura (pH, temperatura, nome da amostra) e cria um relacionamento (Chave Estrangeira) com o usuário que a cadastrou.

## 🛠️ Tecnologias Utilizadas

- **Linguagem Principal:** PHP (Vanilla)
- **Banco de Dados:** MySQL 8.0 / MariaDB
- **Estilização e Layout:** HTML5 e CSS3 (`estilo.css`)
- **Infraestrutura:** Docker e Docker Compose

---

## ⚙️ Como Executar o Projeto

Existem duas maneiras de rodar este projeto: usando **Docker** (recomendado, pois já prepara tudo) ou um **Servidor Local** tradicional.

### Opção 1: Usando Docker Compose (Recomendado)

O projeto conta com um ambiente completo já configurado no arquivo `docker-compose.yml`. Ele vai subir dois containers: um para o servidor web (PHP) e outro para o banco de dados (MySQL).

1. Certifique-se de ter o [Docker](https://www.docker.com/) instalado no seu computador.
2. Abra o terminal na raiz do projeto (na mesma pasta onde está o arquivo `docker-compose.yml` dentro de `docker/`, ou navegue até a raiz).
3. Execute o comando para subir os containers em segundo plano:
   ```bash
   cd docker
   docker-compose up -d --build
   ```
4. O servidor estará rodando na porta `8080`.
5. Acesse no navegador: [http://localhost:8080/](http://localhost:8080/)
   > **Nota:** O banco de dados MySQL é importado automaticamente pelo Docker usando o arquivo `banco.sql`.

### Opção 2: Servidor Local (WAMP, XAMPP, etc.)

1. Clone ou baixe este repositório no diretório público do seu servidor web (ex: `C:\wamp64\www\Sistema-Registrador-de-pH---IFSEMG` ou `C:\xampp\htdocs\`).
2. Acesse seu SGBD (ex: phpMyAdmin) e importe o arquivo `banco.sql` fornecido na raiz do projeto. Isso criará o banco `phmetro` e as tabelas com dados de exemplo.
   > **💡 Dica para Hospedagens Online (cPanel, Hostinger, etc):** O arquivo `banco.sql` possui comandos para criar o banco de dados automaticamente. Servidores de hospedagem costumam bloquear isso por segurança. Caso dê erro na importação online, basta abrir o arquivo `banco.sql`, **apagar as três primeiras linhas** (que contêm `DROP DATABASE` e `CREATE DATABASE`), criar o banco manualmente no painel da sua hospedagem e então importar o arquivo novamento.
3. *(Opcional)* Se você usar senhas diferentes no MySQL, edite o arquivo `conexao.php` atualizando `DB_USER` e `DB_PASS`.
4. Inicie o Apache e o MySQL pelo seu painel de controle.
5. Acesse no navegador: `http://localhost/Sistema-Registrador-de-pH---IFSEMG/`

---

## 🧑‍💻 Primeiro Acesso
O arquivo de banco de dados (`banco.sql`) já cria um usuário administrador padrão e alguns dados de exemplo. 
- **Email:** `admin@gmail.com`
- (Verifique a senha no script ou, caso não a saiba devido ao hash `$2y$10$...`, sinta-se à vontade para registrar um novo usuário através da tela de cadastro do sistema!).
