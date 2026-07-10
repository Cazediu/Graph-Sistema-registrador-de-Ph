-- Banco de dados limpo para o projeto phmetro
DROP DATABASE IF EXISTS phmetro;
CREATE DATABASE phmetro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE phmetro;

-- Tabela de usuários
CREATE TABLE usuarios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  senha VARCHAR(255) NOT NULL,
  aprovado TINYINT(1) NOT NULL DEFAULT 0,
  role VARCHAR(20) NOT NULL DEFAULT 'user',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de medições de pH
CREATE TABLE medicoes_ph (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  valor_ph DECIMAL(4,2) NOT NULL,
  temperatura DECIMAL(5,2) DEFAULT NULL,
  nome_liquido VARCHAR(100) NOT NULL,
  observacao TEXT DEFAULT NULL,
  data_medicao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_medicoes_usuario (usuario_id),
  CONSTRAINT fk_medicoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados de exemplo
-- Usuário administrador (senha hashed)
INSERT INTO usuarios (nome, email, senha, aprovado, role) VALUES ('Administrador', 'admin@gmail.com', '$2y$10$abcdefghijklmnopqrstuv1234567890123456789012345678901234', 1, 'admin');

INSERT INTO medicoes_ph (usuario_id, valor_ph, temperatura, nome_liquido, observacao) VALUES
(1, 7.00, 25.00, 'Água mineral', 'Água neutra'),
(1, 5.50, 18.00, 'Suco de limão', 'Levemente ácida'),
(1, 8.20, 30.00, 'Água sanitária', 'Levemente alcalina');
