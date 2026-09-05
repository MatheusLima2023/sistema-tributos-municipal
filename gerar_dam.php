<?php
require_once 'db.php';

$contribuinte_id = $_GET['id'] ?? null;
$dam_id          = $_GET['dam_id'] ?? null;
$edit_dam_id     = $_GET['edit_dam_id'] ?? null;

$stmt = $pdo->prepare("SELECT * FROM contribuintes WHERE id = :id");
$stmt->execute([':id' => $contribuinte_id]);
$contribuinte = $stmt->fetch();

$dam_edit = null;
if ($edit_dam_id) {
    $stmtE = $pdo->prepare("SELECT * FROM documentos_dam WHERE id = :id");
    $stmtE->execute([':id' => $edit_dam_id]);
    $dam_edit = $stmtE->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receita_tributo = $_POST['receita_tributo'];
    $exercicio       = (int)$_POST['exercicio'];
    $parcela         = $_POST['parcela'];
    $data_vencimento = $_POST['data_vencimento'];
    $valor_base      = (float)$_POST['valor_base'];
    $aliquota        = (float)$_POST['aliquota'];
    
    // Fórmula: Valor Base x Alíquota % = Imposto
    $valor_original  = $valor_base * ($aliquota / 100);
    $juros_multa     = (float)$_POST['juros_multa'];
    $desconto        = (float)$_POST['desconto'];
    $valor_total     = ($valor_original + $juros_multa) - $desconto;
    $observacao      = $_POST['observacao'];
    $status          = $_POST['status'] ?? 'PENDENTE';

    if ($edit_dam_id) {
        $sql = "UPDATE documentos_dam SET receita_tributo=:receita_tributo, exercicio=:exercicio, parcela=:parcela, 
                data_vencimento=:data_vencimento, valor_base=:valor_base, aliquota=:aliquota, valor_original=:valor_original, 
                juros_multa=:juros_multa, desconto=:desconto, valor_total=:valor_total, observacao=:observacao, status=:status 
                WHERE id=:id";
        $stmtI = $pdo->prepare($sql);
        $stmtI->execute([
            ':receita_tributo' => $receita_tributo, ':exercicio' => $exercicio, ':parcela' => $parcela,
            ':data_vencimento' => $data_vencimento, ':valor_base' => $valor_base, ':aliquota' => $aliquota,
            ':valor_original' => $valor_original, ':juros_multa' => $juros_multa, ':desconto' => $desconto,
            ':valor_total' => $valor_total, ':observacao' => $observacao, ':status' => $status, ':id' => $edit_dam_id
        ]);
        $target_id = $edit_dam_id;
    } else {
        $numero_dam = 'DAM' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
        $sql = "INSERT INTO documentos_dam (contribuinte_id, numero_dam, receita_tributo, exercicio, parcela, data_vencimento, valor_base, aliquota, valor_original, juros_multa, desconto, valor_total, observacao, status) 
                VALUES (:contribuinte_id, :numero_dam, :receita_tributo, :exercicio, :parcela, :data_vencimento, :valor_base, :aliquota, :valor_original, :juros_multa, :desconto, :valor_total, :observacao, :status)";
        $stmtI = $pdo->prepare($sql);
        $stmtI->execute([
            ':contribuinte_id' => $contribuinte_id, ':numero_dam' => $numero_dam, ':receita_tributo' => $receita_tributo,
            ':exercicio' => $exercicio, ':parcela' => $parcela, ':data_vencimento' => $data_vencimento,
            ':valor_base' => $valor_base, ':aliquota' => $aliquota, ':valor_original' => $valor_original,
            ':juros_multa' => $juros_multa, ':desconto' => $desconto, ':valor_total' => $valor_total,
            ':observacao' => $observacao, ':status' => $status
        ]);
        $target_id = $pdo->lastInsertId();
    }

    header("Location: gerar_dam.php?id={$contribuinte_id}&dam_id={$target_id}");
    exit;
}

$dam_gerado = null;
if ($dam_id) {
    $stmtD = $pdo->prepare("SELECT * FROM documentos_dam WHERE id = :id");
    $stmtD->execute([':id' => $dam_id]);
    $dam_gerado = $stmtD->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>DAM - Centro do Guilherme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function calcularImposto() {
            let base = parseFloat(document.getElementById('valor_base').value) || 0;
            let aliquota = parseFloat(document.getElementById('aliquota').value) || 0;
            let imposto = base * (aliquota / 100);
            document.getElementById('valor_original').value = imposto.toFixed(2);
        }
    </script>
    <style>@media print { .no-print { display: none !important; } }</style>
</head>
<body class="bg-light">
<div class="container my-4">
    <?php if (!$dam_gerado): ?>
        <div class="card shadow-sm no-print mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><?= $edit_dam_id ? 'Editar' : 'Emitir' ?> DAM - Contribuinte: <?= htmlspecialchars($contribuinte['nome_razao']) ?></h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tributo</label>
                            <select name="receita_tributo" class="form-select">
                                <option value="ISSQN">ISSQN</option>
                                <option value="IPTU">IPTU</option>
                                <option value="Alvará de Licença">Alvará de Licença</option>
                            </select>
                        </div>
                        <div class="col-md-2"><label class="form-label">Exercício</label><input type="number" name="exercicio" class="form-control" value="<?= $dam_edit['exercicio'] ?? date('Y') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Parcela</label><input type="text" name="parcela" class="form-control" value="<?= $dam_edit['parcela'] ?? 'ÚNICA' ?>"></div>
                        <div class="col-md-3"><label class="form-label">Vencimento</label><input type="date" name="data_vencimento" class="form-control" value="<?= $dam_edit['data_vencimento'] ?? date('Y-m-d', strtotime('+15 days')) ?>"></div>

                        <div class="col-md-3">
                            <label class="form-label">Valor Base (R$)</label>
                            <input type="number" step="0.01" id="valor_base" name="valor_base" class="form-control" oninput="calcularImposto()" value="<?= $dam_edit['valor_base'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Alíquota (%)</label>
                            <input type="number" step="0.01" id="aliquota" name="aliquota" class="form-control" oninput="calcularImposto()" value="<?= $dam_edit['aliquota'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Imposto Calculado (R$)</label>
                            <input type="number" step="0.01" id="valor_original" name="valor_original" class="form-control" readonly value="<?= $dam_edit['valor_original'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status Pagamento</label>
                            <select name="status" class="form-select">
                                <option value="PENDENTE" <?= ($dam_edit['status'] ?? '') == 'PENDENTE' ? 'selected' : '' ?>>PENDENTE</option>
                                <option value="PAGO" <?= ($dam_edit['status'] ?? '') == 'PAGO' ? 'selected' : '' ?>>PAGO</option>
                            </select>
                        </div>

                        <div class="col-md-6"><label class="form-label">Juros / Multa (R$)</label><input type="number" step="0.01" name="juros_multa" class="form-control" value="<?= $dam_edit['juros_multa'] ?? '0.00' ?>"></div>
                        <div class="col-md-6"><label class="form-label">Desconto (R$)</label><input type="number" step="0.01" name="desconto" class="form-control" value="<?= $dam_edit['desconto'] ?? '0.00' ?>"></div>
                        <div class="col-md-12"><label class="form-label">Observações</label><input type="text" name="observacao" class="form-control" value="<?= $dam_edit['observacao'] ?? '' ?>"></div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Salvar e Gerar DAM</button>
                        <a href="index.php" class="btn btn-secondary">Voltar ao Painel</a>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="no-print mb-3">
            <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir</button>
            <a href="gerar_dam.php?id=<?= $contribuinte_id ?>" class="btn btn-secondary">Voltar para Gerar/Editar DAM</a>
            <a href="index.php" class="btn btn-outline-dark">Painel Principal</a>
        </div>

        <!-- IMPRESSÃO DO DAM COM LOGO E DADOS BANCÁRIOS -->
        <div class="card p-4 bg-white border border-dark">
            <div class="row align-items-center border-bottom pb-3 mb-3">
                <div class="col-2 text-center">
                    <img src="img.jpeg" style="max-height: 75px;" alt="Logo Municipal">
                </div>
                <div class="col-7">
                    <h5 class="fw-bold mb-0">PREFEITURA MUNICIPAL DE CENTRO DO GUILHERME</h5>
                    <small>Secretaria Municipal da Fazenda Pública - CNPJ: 01.612.328/0001-21</small>
                </div>
                <div class="col-3 text-end">
                    <span class="badge bg-dark fs-6"><?= $dam_gerado['numero_dam'] ?></span>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-8 border p-2"><small class="text-muted d-block">CONTRIBUINTE</small><strong><?= htmlspecialchars($contribuinte['nome_razao']) ?></strong></div>
                <div class="col-4 border p-2"><small class="text-muted d-block">CPF / CNPJ</small><strong><?= htmlspecialchars($contribuinte['cpf_cnpj']) ?></strong></div>
            </div>

            <div class="row mb-2">
                <div class="col-3 border p-2"><small class="text-muted d-block">BASE DE CÁLCULO</small>R$ <?= number_format($dam_gerado['valor_base'], 2, ',', '.') ?></div>
                <div class="col-2 border p-2"><small class="text-muted d-block">ALÍQUOTA</small><?= number_format($dam_gerado['aliquota'], 2, ',', '.') ?>%</div>
                <div class="col-3 border p-2"><small class="text-muted d-block">IMPOSTO</small>R$ <?= number_format($dam_gerado['valor_original'], 2, ',', '.') ?></div>
                <div class="col-4 border p-2 bg-light"><small class="text-muted d-block">VALOR TOTAL</small><strong class="fs-6">R$ <?= number_format($dam_gerado['valor_total'], 2, ',', '.') ?></strong></div>
            </div>

            <div class="border p-3 my-3 bg-light">
                <h6 class="fw-bold mb-1">DADOS BANCÁRIOS PARA PAGAMENTO:</h6>
                <p class="mb-0"><strong>Banco:</strong> Bradesco | <strong>Agência:</strong> 1772-8 | <strong>Conta Corrente:</strong> 8413-1</p>
                <p class="mb-0"><strong>Favorecido:</strong> P.M.C.G TRIBUTOS</p>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>