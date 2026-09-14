-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 14/09/2026 às 14:20
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `tributos_db`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `certidoes`
--

CREATE TABLE `certidoes` (
  `id` int(11) NOT NULL,
  `contribuinte_id` int(11) NOT NULL,
  `codigo_validacao` varchar(50) NOT NULL,
  `tipo_certidao` enum('NEGATIVA','POSITIVA_COM_EFEITO_DE_NEGATIVA','COMPROVANTE_INSCRICAO_MUNICIPAL') NOT NULL,
  `ramo_atividade` varchar(200) DEFAULT NULL,
  `rg` varchar(30) DEFAULT NULL,
  `tributos_referencia` varchar(255) DEFAULT 'ISSQN/ALVARÁ/IPTU/ITU/ITBI',
  `finalidade_uso` varchar(255) DEFAULT NULL,
  `data_emissao` timestamp NOT NULL DEFAULT current_timestamp(),
  `prazo_validade_dias` int(11) DEFAULT 90,
  `data_validade` date NOT NULL,
  `emissor_nome` varchar(100) DEFAULT 'Matheus Viana Lima',
  `emissor_cargo` varchar(100) DEFAULT 'Chefe de Arrecadação do Setor Tributário',
  `emissor_portaria` varchar(50) DEFAULT 'Portaria 011/2025',
  `status` enum('VALIDA','EXPIRADA','CANCELADA') DEFAULT 'VALIDA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `certidoes`
--

INSERT INTO `certidoes` (`id`, `contribuinte_id`, `codigo_validacao`, `tipo_certidao`, `ramo_atividade`, `rg`, `tributos_referencia`, `finalidade_uso`, `data_emissao`, `prazo_validade_dias`, `data_validade`, `emissor_nome`, `emissor_cargo`, `emissor_portaria`, `status`) VALUES
(1, 3, 'ECBB9BEE-2026', 'NEGATIVA', '', '', 'ISSQN/ALVARÁ/IPTU/ITU/ITBI', 'Fazer prova de Quitação de Tributos', '2026-09-10 12:03:08', 90, '2026-12-09', 'Matheus Viana Lima', 'Chefe de Arrecadação do Setor Tributário', 'Portaria 011/2025', 'VALIDA'),
(2, 3, 'A65632B3-2026', 'POSITIVA_COM_EFEITO_DE_NEGATIVA', '', '', 'ISSQN/ALVARÁ/IPTU/ITU/ITBI', 'Fazer prova de Quitação de Tributos', '2026-09-10 12:09:41', 90, '2026-12-09', 'Matheus Viana Lima', 'Chefe de Arrecadação do Setor Tributário', 'Portaria 011/2025', 'VALIDA'),
(3, 2, 'E05F46C6-2026', 'NEGATIVA', '', '', 'ISSQN/ALVARÁ/IPTU/ITU/ITBI', 'Fazer prova de Quitação de Tributos', '2026-09-10 12:09:59', 90, '2026-12-09', 'Matheus Viana Lima', 'Chefe de Arrecadação do Setor Tributário', 'Portaria 011/2025', 'VALIDA'),
(4, 2, '6BC71B8F-2026', 'COMPROVANTE_INSCRICAO_MUNICIPAL', '', '', 'ISSQN/ALVARÁ/IPTU/ITU/ITBI', 'Fazer prova de Quitação de Tributos', '2026-09-10 12:10:09', 90, '2026-12-09', 'Matheus Viana Lima', 'Chefe de Arrecadação do Setor Tributário', 'Portaria 011/2025', 'VALIDA'),
(5, 1, '48F8BA5C-2026', 'NEGATIVA', '', '', 'ISSQN/ALVARÁ/IPTU/ITU/ITBI', 'Fazer prova de Quitação de Tributos', '2026-09-10 12:10:21', 90, '2026-12-09', 'Matheus Viana Lima', 'Chefe de Arrecadação do Setor Tributário', 'Portaria 011/2025', 'VALIDA'),
(6, 3, '3DBA479A-2026', 'NEGATIVA', '', '', 'ISSQN/ALVARÁ/IPTU/ITU/ITBI', 'Fazer prova de Quitação de Tributos', '2026-09-10 12:16:33', 90, '2026-12-09', 'Matheus Viana Lima', 'Chefe de Arrecadação do Setor Tributário', 'Portaria 011/2025', 'VALIDA'),
(7, 4, 'A0A7D7C9-2026', 'NEGATIVA', 'JHJHGFAJJGHFHJHNAF', '100022332', 'ISSQN/ALVARÁ/IPTU/ITU/ITBI', 'Fazer prova de Quitação de Tributos', '2026-09-10 14:03:33', 90, '2026-12-09', 'Matheus Viana Lima', 'Chefe de Arrecadação do Setor Tributário', 'Portaria 011/2025', 'VALIDA'),
(8, 4, '80B216CD-2026', 'POSITIVA_COM_EFEITO_DE_NEGATIVA', 'JHJHGFAJJGHFHJHNAF', '100022332', 'ISSQN/ALVARÁ/IPTU/ITU/ITBI', 'Fazer prova de Quitação de Tributos', '2026-09-10 14:03:38', 90, '2026-12-09', 'Matheus Viana Lima', 'Chefe de Arrecadação do Setor Tributário', 'Portaria 011/2025', 'VALIDA'),
(9, 4, 'AF80D8CC-2026', 'COMPROVANTE_INSCRICAO_MUNICIPAL', 'JHJHGFAJJGHFHJHNAF', '100022332', 'ISSQN/ALVARÁ/IPTU/ITU/ITBI', 'Fazer prova de Quitação de Tributos', '2026-09-10 14:03:42', 90, '2026-12-09', 'Matheus Viana Lima', 'Chefe de Arrecadação do Setor Tributário', 'Portaria 011/2025', 'VALIDA'),
(10, 5, '14DF6F8C-2026', 'NEGATIVA', 'shjkafhjfhjkcnkndgnoierjfioergk', '2525252552', 'ISSQN/ALVARÁ/IPTU/ITU/ITBI', 'Fazer prova de Quitação de Tributos', '2026-09-10 19:32:45', 90, '2026-12-09', 'Matheus Viana Lima', 'Chefe de Arrecadação do Setor Tributário', 'Portaria 011/2025', 'VALIDA'),
(11, 5, '12134F62-2026', 'POSITIVA_COM_EFEITO_DE_NEGATIVA', 'shjkafhjfhjkcnkndgnoierjfioergk', '2525252552', 'ISSQN/ALVARÁ/IPTU/ITU/ITBI', 'Fazer prova de Quitação de Tributos', '2026-09-10 19:32:55', 90, '2026-12-09', 'Matheus Viana Lima', 'Chefe de Arrecadação do Setor Tributário', 'Portaria 011/2025', 'VALIDA'),
(12, 5, '84807EA4-2026', 'COMPROVANTE_INSCRICAO_MUNICIPAL', 'shjkafhjfhjkcnkndgnoierjfioergk', '2525252552', 'ISSQN/ALVARÁ/IPTU/ITU/ITBI', 'Fazer prova de Quitação de Tributos', '2026-09-10 19:33:01', 90, '2026-12-09', 'Matheus Viana Lima', 'Chefe de Arrecadação do Setor Tributário', 'Portaria 011/2025', 'VALIDA'),
(13, 6, 'AC5B61C4-2026', 'POSITIVA_COM_EFEITO_DE_NEGATIVA', 'vAIOHJIHIFDJZDS', '5456465465', 'ISSQN/ALVARÁ/IPTU/ITU/ITBI', 'Fazer prova de Quitação de Tributos', '2026-09-14 11:58:52', 90, '2026-12-13', 'Matheus Viana Lima', 'Chefe de Arrecadação do Setor Tributário', 'Portaria 011/2025', 'VALIDA');

-- --------------------------------------------------------

--
-- Estrutura para tabela `contribuintes`
--

CREATE TABLE `contribuintes` (
  `id` int(11) NOT NULL,
  `tipo_pessoa` enum('PF','PJ') NOT NULL DEFAULT 'PF',
  `nome_razao` varchar(150) NOT NULL,
  `cpf_cnpj` varchar(20) NOT NULL,
  `rg` varchar(30) DEFAULT NULL,
  `inscricao_municipal` varchar(30) DEFAULT NULL,
  `ramo_atividade` varchar(200) DEFAULT NULL,
  `endereco` varchar(200) NOT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `bairro` varchar(80) NOT NULL,
  `cidade` varchar(80) NOT NULL DEFAULT 'Centro do Guilherme',
  `uf` char(2) NOT NULL DEFAULT 'MA',
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `contribuintes`
--

INSERT INTO `contribuintes` (`id`, `tipo_pessoa`, `nome_razao`, `cpf_cnpj`, `rg`, `inscricao_municipal`, `ramo_atividade`, `endereco`, `numero`, `bairro`, `cidade`, `uf`, `telefone`, `email`, `criado_em`) VALUES
(1, 'PF', 'Matheus Viana Lima', '171.313.628-94', NULL, '0005', NULL, '', NULL, 'Centro', 'Centro do Guilherme', 'MA', '98986074767', 'matheuslima2023xxi@gmail.com', '2026-09-09 13:52:23'),
(2, 'PF', 'JOÃO', '10.220.030/0001-31', NULL, '0111', NULL, 'Rua do Norte, nº245', NULL, 'Centro', 'Centro do Guilherme', 'MA', '', '', '2026-09-09 13:53:04'),
(3, 'PF', 'Maria Viana Lima', '123.456.789-10', '100022332', '12345', 'jhjkjkjkljklDHhdA', '411 Rua do Comércio', '<br /><b>Deprecated<', 'Centro', 'Centro do Guilherme', 'MA', '98986074767', 'matheuslima2023xxi@gmail.com', '2026-09-09 14:29:23'),
(4, 'PF', 'Marcos lima', '123456789', '100022332', '2346', 'JHJHGFAJJGHFHJHN', 'Rua do Comercio', 'S/N', 'Centro', 'Centro do Guilherme', 'MA', NULL, NULL, '2026-09-10 12:18:18'),
(5, 'PF', 'Andre Viado', '2424242424', '2525252552', '202020', 'shjkafhjfhjkcnkndgnoier', 'Rua Nova II', '100', 'São Jose', 'Centro do Guilherme', 'MA', NULL, NULL, '2026-09-10 19:31:41'),
(6, 'PF', 'mjkhjh', '10000221351456', '5456465465', '02500', 'vAIOHJIHIFDJZDS', 'Rua do Norte, nº245', '400', 'São Jose', 'Centro do Guilherme', 'MA', NULL, NULL, '2026-09-14 11:58:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `documentos_dam`
--

CREATE TABLE `documentos_dam` (
  `id` int(11) NOT NULL,
  `contribuinte_id` int(11) NOT NULL,
  `numero_dam` varchar(30) NOT NULL,
  `receita_tributo` varchar(100) NOT NULL,
  `exercicio` int(11) NOT NULL,
  `valor_base` decimal(10,2) NOT NULL DEFAULT 0.00,
  `aliquota` decimal(5,2) DEFAULT 0.00,
  `parcela` varchar(10) DEFAULT 'ÚNICA',
  `data_emissao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_vencimento` date NOT NULL,
  `valor_original` decimal(10,2) NOT NULL,
  `juros_multa` decimal(10,2) DEFAULT 0.00,
  `desconto` decimal(10,2) DEFAULT 0.00,
  `valor_total` decimal(10,2) NOT NULL,
  `observacao` text DEFAULT NULL,
  `status` enum('PENDENTE','PAGO','CANCELADO') DEFAULT 'PENDENTE',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `nfa_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `documentos_dam`
--

INSERT INTO `documentos_dam` (`id`, `contribuinte_id`, `numero_dam`, `receita_tributo`, `exercicio`, `valor_base`, `aliquota`, `parcela`, `data_emissao`, `data_vencimento`, `valor_original`, `juros_multa`, `desconto`, `valor_total`, `observacao`, `status`, `criado_em`, `nfa_id`) VALUES
(1, 4, 'DAM2026-B7BE95', 'Alvará de Licença e Funcionamento', 2026, 1000.00, 5.00, 'ÚNICA', '2026-09-10 12:23:03', '2026-09-25', 50.00, 0.00, 0.00, 50.00, 'venda', 'PENDENTE', '2026-09-10 12:23:03', NULL),
(2, 4, 'DAM2026-5CE401', 'Alvará de Licença e Funcionamento', 2026, 1000.00, 5.00, 'ÚNICA', '2026-09-10 14:03:22', '2026-09-25', 50.00, 0.00, 0.00, 50.00, '', 'PENDENTE', '2026-09-10 14:03:22', NULL),
(3, 2, 'DAM2026-0E9748', 'Alvará de Licença e Funcionamento', 2026, 1000.00, 5.00, 'ÚNICA', '2026-09-10 19:32:11', '2026-09-25', 50.00, 0.00, 0.00, 50.00, 'fakghdkjgcnknc', 'PENDENTE', '2026-09-10 19:32:11', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `notas_fiscais`
--

CREATE TABLE `notas_fiscais` (
  `id` int(11) NOT NULL,
  `numero_nota` varchar(20) NOT NULL,
  `prestador_id` int(11) DEFAULT NULL,
  `prestador_nome` varchar(255) NOT NULL,
  `prestador_cpf_cnpj` varchar(20) NOT NULL,
  `tomador_nome` varchar(255) NOT NULL,
  `tomador_cpf_cnpj` varchar(20) NOT NULL,
  `descricao_servico` text NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `aliquota_iss` decimal(5,2) DEFAULT 2.00,
  `valor_iss` decimal(10,2) NOT NULL,
  `data_emissao` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `notas_fiscais`
--

INSERT INTO `notas_fiscais` (`id`, `numero_nota`, `prestador_id`, `prestador_nome`, `prestador_cpf_cnpj`, `tomador_nome`, `tomador_cpf_cnpj`, `descricao_servico`, `valor_total`, `aliquota_iss`, `valor_iss`, `data_emissao`) VALUES
(1, '2026098499', NULL, 'Matheus Viana Lima', '171.313.628-94', 'JOÃO', '10.220.030/0001-31', 'Programa para prefeitura', 0.00, 2.00, 0.00, '2026-09-09 15:58:04'),
(2, '2026095665', NULL, 'Matheus Viana Lima', '171.313.628-94', 'JOÃO', '10.220.030/0001-31', 'Programação', 1000.00, 5.00, 50.00, '2026-09-09 15:58:47'),
(3, '2026099708', NULL, 'Matheus Viana Lima', '171.313.628-94', 'JOÃO', '10.220.030/0001-31', 'Programação', 1000.00, 5.00, 50.00, '2026-09-09 16:01:42'),
(4, '2026097098', NULL, 'Matheus Viana Lima', '171.313.628-94', 'JOÃO', '10.220.030/0001-31', 'jkhjkhjkhjkhjk', 1000.00, 2.00, 20.00, '2026-09-09 16:07:37'),
(5, '2026098038', 2, 'JOÃO', '10.220.030/0001-31', 'Matheus', '10.220.030/0001-31', 'Programação', 10000.00, 5.00, 500.00, '2026-09-09 16:11:05'),
(6, '2026091548', 2, 'JOÃO', '10.220.030/0001-31', 'JOÃO', '10.220.030/0001-31', 'teste', 99999999.99, 2.00, 99999999.99, '2026-09-09 16:15:31');

-- --------------------------------------------------------

--
-- Estrutura para tabela `notas_fiscais_avulsas`
--

CREATE TABLE `notas_fiscais_avulsas` (
  `id` int(11) NOT NULL,
  `contribuinte_prestador_id` int(11) NOT NULL,
  `contribuinte_tomador_id` int(11) DEFAULT NULL,
  `numero_nfa` varchar(50) NOT NULL,
  `data_emissao` datetime DEFAULT current_timestamp(),
  `prestador_id` int(11) DEFAULT NULL,
  `prestador_nome` varchar(255) NOT NULL,
  `prestador_cpf_cnpj` varchar(20) NOT NULL,
  `prestador_endereco` varchar(255) DEFAULT NULL,
  `prestador_bairro` varchar(100) DEFAULT NULL,
  `prestador_cidade_uf` varchar(100) DEFAULT NULL,
  `prestador_cep` varchar(10) DEFAULT NULL,
  `prestador_inscricao` varchar(50) DEFAULT NULL,
  `tomador_nome` varchar(255) NOT NULL,
  `tomador_cpf_cnpj` varchar(20) NOT NULL,
  `tomador_endereco` varchar(255) DEFAULT NULL,
  `tomador_bairro` varchar(100) DEFAULT NULL,
  `tomador_cidade_uf` varchar(100) DEFAULT NULL,
  `tomador_complemento` varchar(100) DEFAULT NULL,
  `tomador_cep` varchar(10) DEFAULT NULL,
  `discriminacao_servico` text NOT NULL,
  `descricao_servico` text DEFAULT NULL,
  `valor_servico` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_servicos` decimal(10,2) NOT NULL,
  `aliquota_iss` decimal(5,2) DEFAULT 5.00,
  `valor_iss` decimal(10,2) NOT NULL,
  `aliquota_irrf` decimal(5,2) DEFAULT 0.00,
  `valor_irrf` decimal(10,2) DEFAULT 0.00,
  `valor_liquido` decimal(10,2) NOT NULL,
  `mes_competencia` varchar(7) NOT NULL,
  `data_vencimento_iss` date NOT NULL,
  `status` enum('EMITIDA','CANCELADA') DEFAULT 'EMITIDA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `notas_fiscais_avulsas`
--

INSERT INTO `notas_fiscais_avulsas` (`id`, `contribuinte_prestador_id`, `contribuinte_tomador_id`, `numero_nfa`, `data_emissao`, `prestador_id`, `prestador_nome`, `prestador_cpf_cnpj`, `prestador_endereco`, `prestador_bairro`, `prestador_cidade_uf`, `prestador_cep`, `prestador_inscricao`, `tomador_nome`, `tomador_cpf_cnpj`, `tomador_endereco`, `tomador_bairro`, `tomador_cidade_uf`, `tomador_complemento`, `tomador_cep`, `discriminacao_servico`, `descricao_servico`, `valor_servico`, `valor_servicos`, `aliquota_iss`, `valor_iss`, `aliquota_irrf`, `valor_irrf`, `valor_liquido`, `mes_competencia`, `data_vencimento_iss`, `status`) VALUES
(12, 3, 1, 'NFA-2026-1C9E6', '2026-09-10 14:05:11', NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', 'Serviço de programador', 1000.00, 0.00, 5.00, 50.00, 0.00, 0.00, 0.00, '', '0000-00-00', 'EMITIDA'),
(17, 5, 4, 'NFA-2026-EF6FD', '2026-09-10 16:34:09', NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', 'hsgdhgafhgahjdshfjhfjncncncsklkvjeoigjoeijgdfjireiutyivhzduyvgbscxnbjkvkszdnfie hnifveiouor oicdwmpokawmpofm ieugyheghe fkjeje fkwifnwa hfwf', 10000000.00, 0.00, 5.00, 500000.00, 0.00, 0.00, 0.00, '', '0000-00-00', 'CANCELADA'),
(18, 1, 2, 'NFA-2026-3D715', '2026-09-14 08:54:11', NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', 'gfdfgdghfhjghjghj', 15000.00, 0.00, 5.00, 750.00, 0.00, 0.00, 0.00, '', '0000-00-00', 'EMITIDA'),
(19, 4, 2, 'NFA-2026-C4E99', '2026-09-14 09:14:33', NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', 'Programa', 10000.00, 0.00, 5.00, 500.00, 0.00, 0.00, 0.00, '', '0000-00-00', 'EMITIDA');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tributos`
--

CREATE TABLE `tributos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `aliquota_padrao` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `tributos`
--

INSERT INTO `tributos` (`id`, `nome`, `aliquota_padrao`) VALUES
(1, 'ISSQN', 2.00),
(2, 'IPTU', 1.00),
(3, 'Alvará de Licença e Funcionamento', 0.00),
(4, 'ITBI', 2.00),
(5, 'Taxa de Expediente', 0.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `cargo` varchar(100) DEFAULT 'Atendente',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `senha`, `cargo`, `criado_em`) VALUES
(1, 'Matheus Viana Lima', 'admin', '$2y$10$4.kQhGjZ0vYxQp1s2u1o3.3b1eY6W7Z8Q9R0T1U2V3W4X5Y6Z7A8B', 'Chefe de Arrecadação', '2026-09-09 12:05:17'),
(2, 'Matheus Viana Lima', 'Matheus', '$2y$10$oXvjeD53LVYsJ1q3rnOVs.wKfg8./eBi5JU20D6GooGv6AviHfHKS', 'Admin', '2026-09-10 11:52:10');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `certidoes`
--
ALTER TABLE `certidoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_validacao` (`codigo_validacao`),
  ADD KEY `contribuinte_id` (`contribuinte_id`);

--
-- Índices de tabela `contribuintes`
--
ALTER TABLE `contribuintes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf_cnpj` (`cpf_cnpj`);

--
-- Índices de tabela `documentos_dam`
--
ALTER TABLE `documentos_dam`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_dam` (`numero_dam`),
  ADD KEY `contribuinte_id` (`contribuinte_id`),
  ADD KEY `fk_dam_nfa` (`nfa_id`);

--
-- Índices de tabela `notas_fiscais`
--
ALTER TABLE `notas_fiscais`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `notas_fiscais_avulsas`
--
ALTER TABLE `notas_fiscais_avulsas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_nfa` (`numero_nfa`),
  ADD KEY `prestador_id` (`prestador_id`);

--
-- Índices de tabela `tributos`
--
ALTER TABLE `tributos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `certidoes`
--
ALTER TABLE `certidoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `contribuintes`
--
ALTER TABLE `contribuintes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `documentos_dam`
--
ALTER TABLE `documentos_dam`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `notas_fiscais`
--
ALTER TABLE `notas_fiscais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `notas_fiscais_avulsas`
--
ALTER TABLE `notas_fiscais_avulsas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `tributos`
--
ALTER TABLE `tributos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `certidoes`
--
ALTER TABLE `certidoes`
  ADD CONSTRAINT `certidoes_ibfk_1` FOREIGN KEY (`contribuinte_id`) REFERENCES `contribuintes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `documentos_dam`
--
ALTER TABLE `documentos_dam`
  ADD CONSTRAINT `documentos_dam_ibfk_1` FOREIGN KEY (`contribuinte_id`) REFERENCES `contribuintes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dam_nfa` FOREIGN KEY (`nfa_id`) REFERENCES `notas_fiscais_avulsas` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `notas_fiscais_avulsas`
--
ALTER TABLE `notas_fiscais_avulsas`
  ADD CONSTRAINT `notas_fiscais_avulsas_ibfk_1` FOREIGN KEY (`prestador_id`) REFERENCES `contribuintes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
