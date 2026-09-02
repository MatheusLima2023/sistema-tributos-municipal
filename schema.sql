-- 1. Criação do Banco de Dados
CREATE DATABASE IF NOT EXISTS tributos_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE tributos_db;

-- 2. Tabela de Contribuintes (Pessoa Física ou Jurídica)
CREATE TABLE IF NOT EXISTS contribuintes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_pessoa ENUM('PF', 'PJ') NOT NULL DEFAULT 'PF',
    nome_razao VARCHAR(150) NOT NULL,
    cpf_cnpj VARCHAR(20) NOT NULL UNIQUE,
    inscricao_municipal VARCHAR(30) NULL,
    endereco VARCHAR(200) NOT NULL,
    bairro VARCHAR(80) NOT NULL,
    cidade VARCHAR(80) NOT NULL DEFAULT 'Centro do Guilherme',
    uf CHAR(2) NOT NULL DEFAULT 'MA',
    telefone VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Tabela do Documento de Arrecadação Municipal (DAM)
CREATE TABLE IF NOT EXISTS documentos_dam (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contribuinte_id INT NOT NULL,
    numero_dam VARCHAR(30) NOT NULL UNIQUE,
    receita_tributo VARCHAR(100) NOT NULL, -- Ex: IPTU, ISS, Taxa de Licença, Alvará
    exercicio INT NOT NULL,                -- Ex: 2026
    data_emissao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_vencimento DATE NOT NULL,
    valor_original DECIMAL(10, 2) NOT NULL,
    juros_multa DECIMAL(10, 2) DEFAULT 0.00,
    desconto DECIMAL(10, 2) DEFAULT 0.00,
    valor_total DECIMAL(10, 2) NOT NULL,
    observacao TEXT NULL,
    status ENUM('PENDENTE', 'PAGO', 'CANCELADO') DEFAULT 'PENDENTE',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contribuinte_id) REFERENCES contribuintes(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- 4Tabela de Certidões e Documentos Oficiais do Setor Tributário
CREATE TABLE IF NOT EXISTS certidoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contribuinte_id INT NOT NULL,
    codigo_validacao VARCHAR(50) NOT NULL UNIQUE, -- Código de autenticidade/automação
    tipo_certidao ENUM(
        'NEGATIVA', 
        'POSITIVA_COM_EFEITO_DE_NEGATIVA', 
        'COMPROVANTE_INSCRICAO_MUNICIPAL'
    ) NOT NULL,
    
    -- Campos específicos extraídos dos modelos de documentos
    ramo_atividade VARCHAR(200) NULL,             -- Ex: "Serviço de funerárias", "fins lucrativos"
    rg VARCHAR(30) NULL,                         -- Ex: "1232192993 SSP/MA" (para PF)
    tributos_referencia VARCHAR(255) DEFAULT 'ISSQN/ALVARÁ/IPTU/ITU/ITBI',
    finalidade_uso VARCHAR(255) NULL,            -- Ex: "Fazer prova de Quitação de Tributos"
    
    -- Controle de prazos e emissão
    data_emissao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    prazo_validade_dias INT DEFAULT 90,          -- Padrão de 90 dias conforme Art. 227 do CTM
    data_validade DATE NOT NULL,
    
    -- Assinatura e autoridade emissora (Portaria)
    emissor_nome VARCHAR(100) DEFAULT 'Matheus Viana Lima',
    emissor_cargo VARCHAR(100) DEFAULT 'Chefe de Arrecadação do Setor Tributário',
    emissor_portaria VARCHAR(50) DEFAULT 'Portaria 011/2025',
    
    status ENUM('VALIDA', 'EXPIRADA', 'CANCELADA') DEFAULT 'VALIDA',
    
    FOREIGN KEY (contribuinte_id) REFERENCES contribuintes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Tabela de Notas Fiscais Avulsas
CREATE TABLE IF NOT EXISTS notas_fiscais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prestador_id INT NOT NULL, -- Aponta para a tabela de contribuintes
    tomador_id INT NOT NULL,   -- Aponta para a tabela de contribuintes
    numero_nota INT NOT NULL AUTO_INCREMENT UNIQUE,
    codigo_verificacao VARCHAR(50) NOT NULL UNIQUE,
    discriminacao_servicos TEXT NOT NULL,
    valor_servico DECIMAL(10, 2) NOT NULL,
    aliquota_iss DECIMAL(5, 2) NOT NULL, -- Ex: 2.00, 3.00, 5.00 (%)
    valor_iss DECIMAL(10, 2) NOT NULL,
    data_emissao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('EMITIDA', 'CANCELADA') DEFAULT 'EMITIDA',
    FOREIGN KEY (prestador_id) REFERENCES contribuintes(id),
    FOREIGN KEY (tomador_id) REFERENCES contribuintes(id)
) ENGINE=InnoDB;

-- 6. Tabela de Usuários do Sistema (Controle de Acesso)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    login VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL, -- Senha criptografada (password_hash)
    perfil ENUM('ADMIN', 'OPERADOR') DEFAULT 'OPERADOR',
    ativo TINYINT(1) DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;