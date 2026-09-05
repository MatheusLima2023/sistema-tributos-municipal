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

-- Tabela do Documento de Arrecadação Municipal (DAM)
CREATE TABLE IF NOT EXISTS documentos_dam (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contribuinte_id INT NOT NULL,              -- Liga direto com o Contribuinte (Nome, CPF/CNPJ, Endereço, etc.)
    numero_dam VARCHAR(30) NOT NULL UNIQUE,     -- Código/Nosso Número do DAM
    codigo_barras VARCHAR(60) NULL,             -- Linha digitável ou código de barras
    
    -- Dados da Receita Tributária
    receita_tributo VARCHAR(100) NOT NULL,      -- Ex: IPTU, ISSQN, Taxa de Licença e Funcionamento, Alvará
    exercicio INT NOT NULL,                     -- Ex: 2026
    parcela VARCHAR(10) DEFAULT 'ÚNICA',        -- Ex: 'ÚNICA', '01/05', '02/05'
    
    -- Datas e Automação
    data_emissao TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Gera a data/hora exata automaticamente
    data_vencimento DATE NOT NULL,
    
    -- Valores Monetários
    valor_original DECIMAL(10, 2) NOT NULL,
    juros_multa DECIMAL(10, 2) DEFAULT 0.00,
    desconto DECIMAL(10, 2) DEFAULT 0.00,
    valor_total DECIMAL(10, 2) NOT NULL,        -- (Valor Original + Juros/Multa) - Desconto
    
    -- Detalhes Adicionais e Status
    observacao TEXT NULL,                       -- Informações complementares impressas no DAM
    status ENUM('PENDENTE', 'PAGO', 'CANCELADO') DEFAULT 'PENDENTE',
    
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
    id INT AUTO_INCREMENT PRIMARY KEY,          -- Apenas o ID é AUTO_INCREMENT
    prestador_id INT NOT NULL,
    tomador_id INT NOT NULL,
    numero_nota INT NOT NULL UNIQUE,            -- O número sequencial da nota
    codigo_verificacao VARCHAR(50) NOT NULL UNIQUE,
    discriminacao_servicos TEXT NOT NULL,
    valor_servico DECIMAL(10, 2) NOT NULL,
    aliquota_iss DECIMAL(5, 2) NOT NULL,
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

-- Adiciona campos fixos no cadastro do contribuinte
ALTER TABLE contribuintes 
ADD COLUMN ramo_atividade VARCHAR(200) NULL AFTER inscricao_municipal,
ADD COLUMN rg VARCHAR(30) NULL AFTER cpf_cnpj;

-- Adiciona campos de cálculo de imposto na tabela de DAMs
ALTER TABLE documentos_dam 
ADD COLUMN valor_base DECIMAL(10, 2) DEFAULT 0.00 AFTER parcela,
ADD COLUMN aliquota DECIMAL(5, 2) DEFAULT 0.00 AFTER valor_base;