-- Banco de dados para o Sistema Registrador de pH
DROP DATABASE IF EXISTS phmetro;
CREATE DATABASE phmetro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE phmetro;

CREATE TABLE usuarios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  senha VARCHAR(255) NOT NULL,
  aprovado TINYINT(1) NOT NULL DEFAULT 0,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  role VARCHAR(20) NOT NULL DEFAULT 'user',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE medicoes_ph (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  valor_ph DECIMAL(4,2) NOT NULL,
  temperatura DECIMAL(5,2) NOT NULL,
  amostra VARCHAR(100) NOT NULL,
  observacao TEXT DEFAULT NULL,
  data_medicao DATETIME NOT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME DEFAULT NULL,
  atualizado_por INT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_medicoes_usuario (usuario_id),
  KEY idx_medicoes_atualizado_em (atualizado_em),
  CONSTRAINT fk_medicoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuário administrador padrão
INSERT INTO usuarios (nome, email, senha, aprovado, ativo, role)
VALUES ('Administrador', 'admin@gmail.com', '$2y$10$bwUtNnXexDu511Y3su54IO27jd5rwaIi10Hqo4pdLoGPIwxYimE.C', 1, 1, 'admin');

-- Dados de exemplo
INSERT INTO medicoes_ph (usuario_id, valor_ph, temperatura, amostra, observacao, data_medicao, criado_em)
VALUES
(1, 7.00, 25.00, 'Água mineral', 'Água neutra', '2026-08-08 10:30:00', NOW()),
(1, 5.50, 18.00, 'Suco de limão', 'Levemente ácida', '2026-08-08 11:00:00', NOW()),
(1, 8.20, 30.00, 'Água sanitária', 'Levemente alcalina', '2026-08-08 12:15:00', NOW());
