<?php
require_once 'auth.php';
require_once 'db.php';

$contribuinte_id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM contribuintes WHERE id = :id");
$stmt->execute([':id' => $contribuinte_id]);
$contribuinte = $stmt->fetch();

$certidao_gerada = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_certidao   = $_POST['tipo_certidao'];
    $ramo_atividade  = !empty($_POST['ramo_atividade']) ? $_POST['ramo_atividade'] : $contribuinte['ramo_atividade'];
    $rg              = !empty($_POST['rg']) ? $_POST['rg'] : $contribuinte['rg'];
    $finalidade_uso  = $_POST['finalidade_uso'];

    $codigo_validacao = strtoupper(bin2hex(random_bytes(4))) . '-' . date('Y');
    $data_validade    = date('Y-m-d', strtotime('+90 days'));

    $sql = "INSERT INTO certidoes (contribuinte_id, codigo_validacao, tipo_certidao, ramo_atividade, rg, finalidade_uso, data_validade) 
            VALUES (:contribuinte_id, :codigo_validacao, :tipo_certidao, :ramo_atividade, :rg, :finalidade_uso, :data_validade)";
    $stmtI = $pdo->prepare($sql);
    $stmtI->execute([
        ':contribuinte_id' => $contribuinte_id, ':codigo_validacao' => $codigo_validacao,
        ':tipo_certidao' => $tipo_certidao, ':ramo_atividade' => $ramo_atividade,
        ':rg' => $rg, ':finalidade_uso' => $finalidade_uso, ':data_validade' => $data_validade
    ]);

    $cert_id = $pdo->lastInsertId();
    header("Location: emitir_certidao.php?id={$contribuinte_id}&certidao_id={$cert_id}");
    exit;
}

if (isset($_GET['certidao_id'])) {
    $stmtC = $pdo->prepare("SELECT * FROM certidoes WHERE id = :id");
    $stmtC->execute([':id' => $_GET['certidao_id']]);
    $certidao_gerada = $stmtC->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Emissão de Certidões</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* REGRAS PARA FORÇAR IMPRESSÃO EM 1 PÁGINA A4 */
        @media print { 
            @page {
                size: A4 portrait;
                margin: 10mm 12mm 10mm 12mm; /* Margens curtas para caber em 1 página */
            }
            body { 
                background: white !important;
                font-size: 11pt;
            }
            .no-print { display: none !important; }
            .card-certidao {
                border: none !important;
                padding: 0 !important;
                box-shadow: none !important;
            }
        }
        .text-justify {
            text-align: justify;
            text-justify: inter-word;
        }
    </style>
</head>
<body class="bg-light">
<div class="container my-4">
    <?php if (!$certidao_gerada): ?>
        <div class="card shadow-sm no-print mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Gerar Documento / Certidão para: <?= htmlspecialchars($contribuinte['nome_razao']) ?></h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tipo de Documento</label>
                            <select name="tipo_certidao" class="form-select" required>
                                <option value="NEGATIVA">Certidão Negativa de Débitos</option>
                                <option value="POSITIVA_COM_EFEITO_DE_NEGATIVA">Certidão Positiva com Efeito de Negativa</option>
                                <option value="COMPROVANTE_INSCRICAO_MUNICIPAL">Comprovante de Inscrição Municipal</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ramo de Atividade</label>
                            <input type="text" name="ramo_atividade" class="form-control" value="<?= htmlspecialchars($contribuinte['ramo_atividade'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">RG</label>
                            <input type="text" name="rg" class="form-control" value="<?= htmlspecialchars($contribuinte['rg'] ?? '') ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Finalidade</label>
                            <input type="text" name="finalidade_uso" class="form-control" value="Fazer prova de Quitação de Tributos">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-success">Gerar Certidão</button>
                        <a href="index.php" class="btn btn-secondary">Voltar ao Painel</a>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="no-print mb-3">
            <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir Certidão</button>
            <a href="emitir_certidao.php?id=<?= $contribuinte_id ?>" class="btn btn-secondary">Gerar Nova Certidão</a>
            <a href="index.php" class="btn btn-outline-dark">Painel Principal</a>
        </div>

        <!-- FOLHA DA CERTIDÃO (AJUSTADA PARA A4) -->
        <div class="card p-4 bg-white border card-certidao mx-auto" style="max-width: 800px;">
            <div class="text-center mb-3">
                <img src="img.jpeg" style="max-height: 75px;" alt="Logo Municipal" class="mb-2"><br>
                <h5 class="fw-bold mb-0">PREFEITURA MUNICIPAL DE CENTRO DO GUILHERME</h5>
                <h6 class="text-muted small">Secretaria Municipal da Fazenda Pública - Setor de Arrecadação</h6>
                <hr class="my-2">
                <h6 class="fw-bold text-uppercase mt-2">
                    <?php 
                        if ($certidao_gerada['tipo_certidao'] === 'NEGATIVA') echo "CERTIDÃO NEGATIVA DE DÉBITOS";
                        elseif ($certidao_gerada['tipo_certidao'] === 'POSITIVA_COM_EFEITO_DE_NEGATIVA') echo "CERTIDÃO POSITIVA COM EFEITO NEGATIVO DE DÉBITOS RELATIVOS AOS TRIBUTOS MUNICIPAIS E À DÍVIDA ATIVA DO MUNICÍPIO";
                        else echo "COMPROVANTE DE INSCRIÇÃO MUNICIPAL";
                    ?>
                </h6>
            </div>

            <div class="my-3 lh-base text-justify" style="font-size: 1.05rem;">
                <?php if ($certidao_gerada['tipo_certidao'] === 'COMPROVANTE_INSCRICAO_MUNICIPAL'): ?>
                    <p class="mb-2"><strong>Inscrição Municipal:</strong> <?= htmlspecialchars($contribuinte['inscricao_municipal']) ?></p>
                    <p class="mb-2"><strong>Razão Social:</strong> <?= htmlspecialchars($contribuinte['nome_razao']) ?></p>
                    <p class="mb-2"><strong>CPF/CNPJ:</strong> <?= htmlspecialchars($contribuinte['cpf_cnpj']) ?></p>
                    <p class="mb-2"><strong>Atividade Principal:</strong> <?= htmlspecialchars($certidao_gerada['ramo_atividade']) ?></p>
                    <p class="mb-2"><strong>Endereço:</strong> <?= htmlspecialchars($contribuinte['endereco']) ?>, <?= htmlspecialchars($contribuinte['bairro']) ?> - Centro do Guilherme/MA</p>
                <?php else: ?>
                    <p class="mb-3">
                        Certifico, para fins de direito que se fizerem necessários, que <?= $contribuinte['tipo_pessoa'] === 'PJ' ? 'a pessoa jurídica com a razão social denominada' : 'a pessoa física denominada' ?>: 
                        <strong><?= htmlspecialchars($contribuinte['nome_razao']) ?></strong>, 
                        <?= $certidao_gerada['ramo_atividade'] ? 'com ramo de atividade: ' . htmlspecialchars($certidao_gerada['ramo_atividade']) . ',' : '' ?>
                        localizada na <?= htmlspecialchars($contribuinte['endereco']) ?>, <?= htmlspecialchars($contribuinte['bairro']) ?>, Centro do Guilherme-MA, 
                        com inscrição no <?= $contribuinte['tipo_pessoa'] === 'PJ' ? 'CNPJ' : 'CPF' ?>: <strong><?= htmlspecialchars($contribuinte['cpf_cnpj']) ?></strong><?= $certidao_gerada['rg'] ? ', inscrito no RG nº: ' . htmlspecialchars($certidao_gerada['rg']) : '' ?>. 
                        <strong>NADA CONSTA</strong>, em relação a débitos de dívida ativa municipal, de natureza tributária, referente a <?= htmlspecialchars($certidao_gerada['tributos_referencia']) ?>, com o município de Centro do Guilherme-MA.
                    </p>
                    <p class="mb-3">
                        <strong>Fundamentação Legal:</strong> Esta certidão é expedida nos estritos termos do Artigo 225º e parágrafo único da Lei do Código Tributário Municipal de Centro do Guilherme - MA, fazendo prova de quitação de tributos municipais requerida pelo interessado. A presente certidão goza de eficácia liberatória pelo prazo de 90 (noventa) dias a contar da data de sua expedição, ressalvado o direito da Fazenda Municipal de cobrar quaisquer dívidas que venham a ser apuradas posteriormente (Art. 227º do CTM).
                    </p>
                <?php endif; ?>
            </div>

            <div class="border p-2 bg-light rounded text-center my-2">
                <small class="d-block text-muted">CÓDIGO DE VALIDAÇÃO DE AUTENTICIDADE</small>
                <strong class="font-monospace fs-6"><?= $certidao_gerada['codigo_validacao'] ?></strong>
            </div>

            <div class="mt-4 text-center">
                <p class="mb-4">Centro do Guilherme - MA, <?= date('d', strtotime($certidao_gerada['data_emissao'])) ?> de <?= date('m') ?> de <?= date('Y') ?></p>
                <p class="mb-0">__________________________________________________</p>
                <p class="fw-bold mb-0">Matheus Viana Lima</p>
                <p class="mb-0 small">Chefe de Arrecadação do Setor Tributário</p>
                <p class="text-muted small mb-0">Portaria 011/2025</p>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>