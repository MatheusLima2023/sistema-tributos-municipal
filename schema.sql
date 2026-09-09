CREATE DATABASE IF NOT EXISTS tributos_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE tributos_db;

CREATE TABLE IF NOT EXISTS contribuintes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_pessoa ENUM('PF', 'PJ') NOT NULL DEFAULT 'PF',
    nome_razao VARCHAR(150) NOT NULL,
    cpf_cnpj VARCHAR(20) NOT NULL UNIQUE,
    rg VARCHAR(30) NULL,
    inscricao_municipal VARCHAR(30) NULL,
    ramo_atividade VARCHAR(200) NULL,
    endereco VARCHAR(200) NOT NULL,
    numero VARCHAR(20) NULL,
    bairro VARCHAR(80) NOT NULL,
    cidade VARCHAR(80) NOT NULL DEFAULT 'Centro do Guilherme',
    uf CHAR(2) NOT NULL DEFAULT 'MA',
    telefone VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS documentos_dam (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contribuinte_id INT NOT NULL,
    numero_dam VARCHAR(30) NOT NULL UNIQUE,
    codigo_barras VARCHAR(60) NULL,
    receita_tributo VARCHAR(100) NOT NULL,
    exercicio INT NOT NULL,
    parcela VARCHAR(10) DEFAULT 'ÚNICA',
    valor_base DECIMAL(10, 2) DEFAULT 0.00,
    aliquota DECIMAL(5, 2) DEFAULT 0.00,
    data_emissao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_vencimento DATE NOT NULL,
    valor_original DECIMAL(10, 2) NOT NULL,
    juros_multa DECIMAL(10, 2) DEFAULT 0.00,
    desconto DECIMAL(10, 2) DEFAULT 0.00,
    valor_total DECIMAL(10, 2) NOT NULL,
    observacao TEXT NULL,
    status ENUM('PENDENTE', 'PAGO', 'CANCELADO') DEFAULT 'PENDENTE',
    FOREIGN KEY (contribuinte_id) REFERENCES contribuintes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS certidoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contribuinte_id INT NOT NULL,
    codigo_validacao VARCHAR(50) NOT NULL UNIQUE,
    tipo_certidao ENUM(
        'NEGATIVA', 
        'POSITIVA_COM_EFEITO_DE_NEGATIVA', 
        'COMPROVANTE_INSCRICAO_MUNICIPAL'
    ) NOT NULL,
    ramo_atividade VARCHAR(200) NULL,
    rg VARCHAR(30) NULL,
    tributos_referencia VARCHAR(255) DEFAULT 'ISSQN/ALVARÁ/IPTU/ITU/ITBI',
    finalidade_uso VARCHAR(255) NULL,
    data_emissao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    prazo_validade_dias INT DEFAULT 90,
    data_validade DATE NOT NULL,
    emissor_nome VARCHAR(100) DEFAULT 'Matheus Viana Lima',
    emissor_cargo VARCHAR(100) DEFAULT 'Chefe de Arrecadação do Setor Tributário',
    emissor_portaria VARCHAR(50) DEFAULT 'Portaria 011/2025',
    status ENUM('VALIDA', 'EXPIRADA', 'CANCELADA') DEFAULT 'VALIDA',
    FOREIGN KEY (contribuinte_id) REFERENCES contribuintes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tributos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    aliquota_padrao DECIMAL(5,2) DEFAULT 0.00
) ENGINE=InnoDB;

INSERT INTO tributos (nome, aliquota_padrao) VALUES 
('ISSQN', 2.00),
('IPTU', 1.00),
('Alvará de Licença e Funcionamento', 0.00),
('ITBI', 2.00),
('Taxa de Expediente', 0.00)
ON DUPLICATE KEY UPDATE id=id;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cargo VARCHAR(100) DEFAULT 'Atendente',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO usuarios (nome, usuario, senha, cargo) 
VALUES ('Matheus Viana Lima', 'admin', '$2y$10$4.kQhGjZ0vYxQp1s2u1o3.3b1eY6W7Z8Q9R0T1U2V3W4X5Y6Z7A8B', 'Chefe de Arrecadação')
ON DUPLICATE KEY UPDATE id=id;