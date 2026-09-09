<?php
// topo dos arquivos emissao_nfa.php, salvar_nfa.php e imprimir_nfa.php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['sucesso' => false, 'erro' => 'Método de requisição inválido.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Garante a numeração crescente automática da NFA
    $stmt = $pdo->query("SELECT COALESCE(MAX(numero_nfa), 0) + 1 AS proximo FROM notas_fiscais_avulsas FOR UPDATE");
    $numeroNfa = $stmt->fetchColumn();

    // 2. Coleta e sanitização de dados
    $valorServicos = (float) ($_POST['valor_servicos'] ?? 0);
    $aliquotaIss   = 5.00;
    $valorIss      = $valorServicos * ($aliquotaIss / 100);
    
    $aliquotaIrrf  = (float) ($_POST['aliquota_irrf'] ?? 0);
    $valorIrrf     = $valorServicos * ($aliquotaIrrf / 100);
    
    $valorLiquido  = $valorServicos - $valorIss - $valorIrrf;

    // 3. Insere a Nota Fiscal Avulsa
    $sqlNfa = "INSERT INTO notas_fiscais_avulsas (
        numero_nfa, prestador_nome, prestador_cpf_cnpj, prestador_endereco, prestador_bairro, prestador_cidade_uf, prestador_cep, prestador_inscricao,
        tomador_nome, tomador_cpf_cnpj, tomador_endereco, tomador_bairro, tomador_cidade_uf, tomador_complemento, tomador_cep,
        discriminacao_servico, descricao_servico, valor_servicos, aliquota_iss, valor_iss, aliquota_irrf, valor_irrf, valor_liquido,
        mes_competencia, data_vencimento_iss
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmtNfa = $pdo->prepare($sqlNfa);
    $stmtNfa->execute([
        $numeroNfa,
        $_POST['prestador_nome'] ?? '',
        $_POST['prestador_cpf_cnpj'] ?? '',
        $_POST['prestador_endereco'] ?? '',
        $_POST['prestador_bairro'] ?? '',
        $_POST['prestador_cidade_uf'] ?? '',
        $_POST['prestador_cep'] ?? '',
        $_POST['prestador_inscricao'] ?? '',
        $_POST['tomador_nome'] ?? '',
        $_POST['tomador_cpf_cnpj'] ?? '',
        $_POST['tomador_endereco'] ?? '',
        $_POST['tomador_bairro'] ?? '',
        $_POST['tomador_cidade_uf'] ?? '',
        $_POST['tomador_complemento'] ?? '',
        $_POST['tomador_cep'] ?? '',
        $_POST['discriminacao_servico'] ?? '',
        $_POST['descricao_servico'] ?? '',
        $valorServicos,
        $aliquotaIss,
        $valorIss,
        $aliquotaIrrf,
        $valorIrrf,
        $valorLiquido,
        $_POST['mes_competencia'] ?? date('m/Y'),
        $_POST['data_vencimento_iss'] ?? date('Y-m-d')
    ]);

    $nfaId = $pdo->lastInsertId();

    // 4. Inserção automática do DAM com código de receita padrão para ISS NFA
    $sqlDam = "INSERT INTO documentos_dam (
        nfa_id, codigo_receita, descricao_receita, valor_imposto, data_vencimento, status
    ) VALUES (?, '1113.05.00', ?, ?, ?, 'PENDENTE')";

    $stmtDam = $pdo->prepare($sqlDam);
    $stmtDam->execute([
        $nfaId,
        "ISSQN REF. NOTA FISCAL AVULSA Nº " . $numeroNfa,
        $valorIss,
        $_POST['data_vencimento_iss'] ?? date('Y-m-d')
    ]);

    $damId = $pdo->lastInsertId();

    $pdo->commit();

    echo json_encode([
        'sucesso' => true,
        'nfa_id' => $nfaId,
        'dam_id' => $damId,
        'numero_nfa' => sprintf('%05d', $numeroNfa)
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}