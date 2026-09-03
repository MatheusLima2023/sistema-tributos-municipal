<?php
require_once 'db.php';

$contribuinte_id = $_GET['id'] ?? null;

if (!$contribuinte_id) {
    header("Location: index.php");
    exit;
}

// Busca os dados cadastrais do contribuinte
$stmt = $pdo->prepare("SELECT * FROM contribuintes WHERE id = :id");
$stmt->execute([':id' => $contribuinte_id]);
$contribuinte = $stmt->fetch();

if (!$contribuinte) {
    die("Contribuinte não encontrado.");
}

$dam_gerado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receita_tributo = trim($_POST['receita_tributo'] ?? 'IPTU');
    $exercicio       = (int)($_POST['exercicio'] ?? date('Y'));
    $parcela         = trim($_POST['parcela'] ?? 'ÚNICA');
    $data_vencimento = $_POST['data_vencimento'];
    $valor_original  = (float)($_POST['valor_original'] ?? 0.00);
    $juros_multa     = (float)($_POST['juros_multa'] ?? 0.00);
    $desconto        = (float)($_POST['desconto'] ?? 0.00);
    $observacao      = trim($_POST['observacao'] ?? '');

    // Cálculo do valor total
    $valor_total = ($valor_original + $juros_multa) - $desconto;

    // Automação: Gera número do DAM único (Ex: DAM2026-X8F9)
    $numero_dam = 'DAM' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

    try {
        $sql = "INSERT INTO documentos_dam 
                (contribuinte_id, numero_dam, receita_tributo, exercicio, parcela, data_vencimento, valor_original, juros_multa, desconto, valor_total, observacao) 
                VALUES (:contribuinte_id, :numero_dam, :receita_tributo, :exercicio, :parcela, :data_vencimento, :valor_original, :juros_multa, :desconto, :valor_total, :observacao)";
        
        $stmtInsert = $pdo->prepare($sql);
        $stmtInsert->execute([
            ':contribuinte_id' => $contribuinte_id,
            ':numero_dam'      => $numero_dam,
            ':receita_tributo' => $receita_tributo,
            ':exercicio'       => $exercicio,
            ':parcela'         => $parcela,
            ':data_vencimento' => $data_vencimento,
            ':valor_original'  => $valor_original,
            ':juros_multa'     => $juros_multa,
            ':desconto'        => $desconto,
            ':valor_total'     => $valor_total,
            ':observacao'      => $observacao
        ]);

        $dam_id = $pdo->lastInsertId();
        
        // Redireciona para visualização do DAM gerado
        header("Location: gerar_dam.php?id={$contribuinte_id}&dam_id={$dam_id}");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao gerar DAM: " . $e->getMessage();
    }
}

// Se já tiver um DAM emitido para visualização/impressão
if (isset($_GET['dam_id'])) {
    $stmtDam = $pdo->prepare("SELECT * FROM documentos_dam WHERE id = :id");
    $stmtDam->execute([':id' => $_GET['dam_id']]);
    $dam_gerado = $stmtDam->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar DAM - Setor Tributário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .dam-box { border: 2px solid #000 !important; }
        }
    </style>
</head>
<body class="bg-light">

    <div class="container my-4">
        <!-- FORMULÁRIO DE EMISSÃO DO DAM (Visível apenas na tela) -->
        <?php if (!$dam_gerado): ?>
            <div class="card shadow-sm no-print mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Gerar Documento de Arrecadação (DAM) - Contribuinte: <strong><?= htmlspecialchars($contribuinte['nome_razao']) ?></strong></h5>
                </div>
                <div class="card-body">
                    <?php if (isset($erro)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Receita / Tributo *</label>
                                <select name="receita_tributo" class="form-select" required>
                                    <option value="ISSQN">ISSQN - Imposto Sobre Serviços</option>
                                    <option value="IPTU">IPTU - Imposto Predial e Territorial Urbano</option>
                                    <option value="Taxa de Licença e Funcionamento (Alvará)">Taxa de Licença e Funcionamento (Alvará)</option>
                                    <option value="ITBI">ITBI - Transmissão de Bens Imóveis</option>
                                    <option value="Taxa de Expediente">Taxa de Expediente</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Exercício</label>
                                <input type="number" name="exercicio" class="form-control" value="<?= date('Y') ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Parcela</label>
                                <input type="text" name="parcela" class="form-control" value="ÚNICA" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Data de Vencimento *</label>
                                <input type="date" name="data_vencimento" class="form-control" value="<?= date('Y-m-d', strtotime('+15 days')) ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Valor Original (R$) *</label>
                                <input type="number" step="0.01" name="valor_original" class="form-control" placeholder="0.00" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Juros / Multa (R$)</label>
                                <input type="number" step="0.01" name="juros_multa" class="form-control" value="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Desconto (R$)</label>
                                <input type="number" step="0.01" name="desconto" class="form-control" value="0.00">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Observações / Instruções de Pagamento</label>
                                <input type="text" name="observacao" class="form-control" placeholder="Ex: Pagável na Tesouraria Municipal ou Banco Conveniado">
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Emmitir DAM</button>
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- MODELO DE IMPRESSÃO DO DAM -->
        <?php if ($dam_gerado): ?>
            <div class="no-print mb-3">
                <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir DAM</button>
                <a href="index.php" class="btn btn-secondary">Voltar ao Painel</a>
            </div>

            <div class="card p-4 bg-white dam-box shadow-sm border border-dark">
                <div class="row border-bottom pb-3 mb-3 align-items-center">
                    <div class="col-8">
                        <h5 class="fw-bold mb-0">PREFEITURA MUNICIPAL DE CENTRO DO GUILHERME</h5>
                        <p class="mb-0 text-muted small">Secretaria Municipal da Fazenda Pública - Setor Tributário</p>
                        <p class="mb-0 text-muted small">CNPJ: 01.612.328/0001-21</p>
                    </div>
                    <div class="col-4 text-end">
                        <h6 class="fw-bold mb-0">DAM - GUIA DE ARRECADAÇÃO</h6>
                        <span class="badge bg-dark fs-6"><?= htmlspecialchars($dam_gerado['numero_dam']) ?></span>
                    </div>
                </div>

                <!-- DADOS DO CONTRIBUINTE -->
                <div class="row mb-3">
                    <div class="col-8 border p-2">
                        <small class="text-muted d-block">CONTRIBUINTE / RAZÃO SOCIAL</small>
                        <strong><?= htmlspecialchars($contribuinte['nome_razao']) ?></strong>
                    </div>
                    <div class="col-4 border p-2">
                        <small class="text-muted d-block">CPF / CNPJ</small>
                        <strong><?= htmlspecialchars($contribuinte['cpf_cnpj']) ?></strong>
                    </div>
                    <div class="col-12 border border-top-0 p-2">
                        <small class="text-muted d-block">ENDEREÇO</small>
                        <span><?= htmlspecialchars($contribuinte['endereco']) ?>, <?= htmlspecialchars($contribuinte['bairro']) ?> - <?= htmlspecialchars($contribuinte['cidade']) ?>/<?= htmlspecialchars($contribuinte['uf']) ?></span>
                    </div>
                </div>

                <!-- DETALHES DO TRIBUTO E VALORES -->
                <div class="row mb-3">
                    <div class="col-4 border p-2">
                        <small class="text-muted d-block">RECEITA / TRIBUTO</small>
                        <strong><?= htmlspecialchars($dam_gerado['receita_tributo']) ?></strong>
                    </div>
                    <div class="col-2 border p-2">
                        <small class="text-muted d-block">EXERCÍCIO</small>
                        <strong><?= htmlspecialchars($dam_gerado['exercicio']) ?></strong>
                    </div>
                    <div class="col-2 border p-2">
                        <small class="text-muted d-block">PARCELA</small>
                        <strong><?= htmlspecialchars($dam_gerado['parcela']) ?></strong>
                    </div>
                    <div class="col-4 border p-2 bg-light">
                        <small class="text-muted d-block">DATA DE VENCIMENTO</small>
                        <strong class="text-danger fs-6"><?= date('d/m/Y', strtotime($dam_gerado['data_vencimento'])) ?></strong>
                    </div>
                </div>

                <!-- COMPOSIÇÃO DO VALOR -->
                <div class="row mb-3">
                    <div class="col-3 border p-2">
                        <small class="text-muted d-block">VALOR ORIGINAL</small>
                        <span>R$ <?= number_format($dam_gerado['valor_original'], 2, ',', '.') ?></span>
                    </div>
                    <div class="col-3 border p-2">
                        <small class="text-muted d-block">(+) JUROS / MULTA</small>
                        <span>R$ <?= number_format($dam_gerado['juros_multa'], 2, ',', '.') ?></span>
                    </div>
                    <div class="col-3 border p-2">
                        <small class="text-muted d-block">(-) DESCONTO</small>
                        <span>R$ <?= number_format($dam_gerado['desconto'], 2, ',', '.') ?></span>
                    </div>
                    <div class="col-3 border p-2 bg-light">
                        <small class="text-muted d-block">(=) VALOR TOTAL</small>
                        <strong class="fs-6">R$ <?= number_format($dam_gerado['valor_total'], 2, ',', '.') ?></strong>
                    </div>
                </div>

                <?php if ($dam_gerado['observacao']): ?>
                    <div class="border p-2 mb-3">
                        <small class="text-muted d-block">INSTRUÇÕES / OBSERVAÇÕES</small>
                        <span><?= htmlspecialchars($dam_gerado['observacao']) ?></span>
                    </div>
                <?php endif; ?>

                <div class="border-top pt-3 text-center text-muted small">
                    <p class="mb-0">Data de Emissão: <?= date('d/m/Y H:i:s', strtotime($dam_gerado['data_emissao'])) ?></p>
                    <p class="mb-0">Rua do Comércio, nº 263 - Centro - Centro do Guilherme - MA</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>