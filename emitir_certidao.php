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
    $ramo_atividade  = !empty($_POST['ramo_atividade']) ? $_POST['ramo_atividade'] : ($contribuinte['ramo_atividade'] ?? '');
    $rg              = !empty($_POST['rg']) ? $_POST['rg'] : ($contribuinte['rg'] ?? '');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emissão de Certidões</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <style>
        @media print { 
            @page {
                size: A4 portrait;
                margin: 10mm 12mm 10mm 12mm;
            }
            body { 
                background: white !important;
                color: black !important;
                font-size: 11pt;
            }
            .no-print { display: none !important; }
            .card-certidao {
                border: none !important;
                padding: 0 !important;
                box-shadow: none !important;
                min-height: 277mm !important;
            }
            .certidao-cabecalho img { max-height: 100px !important; }
            .card-certidao::before {
                background-size: 480px auto !important;
                opacity: 0.16 !important;
                filter: grayscale(100%) contrast(1.5);
            }
        }
        .text-justify {
            text-align: justify;
            text-justify: inter-word;
        }

        /* ===== Layout formal/tradicional da certidão ===== */
        .card-certidao {
            font-family: Arial, Helvetica, sans-serif;
            position: relative;
            background: #fffdf9;
            padding: 1.75rem 2rem !important;
            border: 1px solid #b8a97a !important;
            box-shadow: 0 0 0 1px #b8a97a inset, 0 0 0 6px #fffdf9 inset, 0 0 0 7px #b8a97a inset !important;
            display: flex;
            flex-direction: column;
            min-height: 277mm;
        }
        .card-certidao::before {
            content: "";
            position: absolute;
            inset: 0;
            background: url('img.jpeg') center center no-repeat;
            background-size: 460px auto;
            opacity: 0.1;
            filter: grayscale(60%) contrast(1.2);
            pointer-events: none;
        }
        .card-certidao > * { position: relative; z-index: 1; }
        .certidao-cabecalho .estado {
            font-size: 0.75rem;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #555;
            margin-bottom: 3px;
        }
        .certidao-cabecalho h5 { letter-spacing: 0.5px; }
        .certidao-cabecalho img { max-height: 80px !important; }
        .certidao-titulo-box {
            border-top: 3px double #333;
            border-bottom: 3px double #333;
            padding: 6px;
            margin: 10px 0 14px;
            font-size: 1rem;
            letter-spacing: 1px;
        }
        .certidao-dados-grid {
            border: 1px solid #999;
            margin-bottom: 1rem;
        }
        .certidao-dados-grid .campo {
            border: 1px solid #ddd;
            padding: 6px 12px;
            font-size: 0.9rem;
        }
        .certidao-dados-grid .rotulo {
            font-size: 0.65rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #777;
            display: block;
            margin-bottom: 2px;
        }
        .certidao-assinatura {
            border-top: 1px solid #999;
            padding-top: 1rem;
            margin-top: auto;
        }
        .certidao-rodape-legal {
            font-size: 0.7rem;
            color: #888;
            letter-spacing: 0.3px;
        }
    </style>
</head>
<body class="bg-light">

<div class="position-fixed top-0 end-0 p-3 no-print" style="z-index: 1050;">
    <button id="theme-toggle" class="theme-toggle-btn btn btn-sm btn-outline-secondary rounded-circle" title="Alternar Tema">🌙</button>
</div>

<div class="container my-4">
    <?php if (!$certidao_gerada): ?>
        <div class="card shadow-sm no-print mb-4 border-0">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Gerar Documento / Certidão para: <?= htmlspecialchars($contribuinte['nome_razao'] ?? '') ?></h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tipo de Documento</label>
                            <select name="tipo_certidao" class="form-select" required>
                                <option value="NEGATIVA">Certidão Negativa de Débitos</option>
                                <option value="POSITIVA_COM_EFEITO_DE_NEGATIVA">Certidão Negativa de Débitos da Dívida Ativa Municipal</option>
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

        <div class="card p-4 bg-white border card-certidao mx-auto text-dark" style="max-width: 800px;">
            <div class="text-center mb-2 certidao-cabecalho">
                <img src="img.jpeg" style="max-height: 80px;" alt="Brasão Municipal" class="mb-2"><br>
                <div class="estado">Estado do Maranhão</div>
                <h5 class="fw-bold mb-0">PREFEITURA MUNICIPAL DE CENTRO DO GUILHERME</h5>
                <h6 class="text-muted small mb-0">Secretaria Municipal da Fazenda Pública — Setor de Arrecadação</h6>
            </div>

            <div class="text-center fw-bold text-uppercase certidao-titulo-box">
                <?php 
                    if ($certidao_gerada['tipo_certidao'] === 'NEGATIVA') echo "CERTIDÃO NEGATIVA DE DÉBITOS";
                    elseif ($certidao_gerada['tipo_certidao'] === 'POSITIVA_COM_EFEITO_DE_NEGATIVA') echo "Certidão Negativa de Débitos da Dívida Ativa Municipal";
                    else echo "COMPROVANTE DE INSCRIÇÃO MUNICIPAL";
                ?>
            </div>

            <?php if ($certidao_gerada['tipo_certidao'] === 'COMPROVANTE_INSCRICAO_MUNICIPAL'): ?>
                <div class="certidao-dados-grid">
                    <div class="row g-0">
                        <div class="col-md-6 campo">
                            <span class="rotulo">Inscrição Municipal</span>
                            <?= htmlspecialchars($contribuinte['inscricao_municipal'] ?? '') ?>
                        </div>
                        <div class="col-md-6 campo">
                            <span class="rotulo">CPF/CNPJ</span>
                            <?= htmlspecialchars($contribuinte['cpf_cnpj'] ?? '') ?>
                        </div>
                        <div class="col-md-12 campo">
                            <span class="rotulo">Razão Social</span>
                            <?= htmlspecialchars($contribuinte['nome_razao'] ?? '') ?>
                        </div>
                        <div class="col-md-6 campo">
                            <span class="rotulo">Atividade Principal</span>
                            <?= htmlspecialchars($certidao_gerada['ramo_atividade']) ?>
                        </div>
                        <div class="col-md-6 campo">
                            <span class="rotulo">Endereço</span>
                            <?= htmlspecialchars($contribuinte['endereco'] ?? '') ?>, <?= htmlspecialchars($contribuinte['numero'] ?? 'S/N') ?> - <?= htmlspecialchars($contribuinte['bairro'] ?? '') ?> - Centro do Guilherme/MA
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="certidao-dados-grid">
                    <div class="row g-0">
                        <div class="col-md-8 campo">
                            <span class="rotulo"><?= ($contribuinte['tipo_pessoa'] ?? 'PF') === 'PJ' ? 'Razão Social' : 'Nome' ?></span>
                            <?= htmlspecialchars($contribuinte['nome_razao'] ?? '') ?>
                        </div>
                        <div class="col-md-4 campo">
                            <span class="rotulo"><?= ($contribuinte['tipo_pessoa'] ?? 'PF') === 'PJ' ? 'CNPJ' : 'CPF' ?></span>
                            <?= htmlspecialchars($contribuinte['cpf_cnpj'] ?? '') ?>
                        </div>
                        <?php if (!empty($certidao_gerada['rg'])): ?>
                        <div class="col-md-4 campo">
                            <span class="rotulo">RG</span>
                            <?= htmlspecialchars($certidao_gerada['rg']) ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($certidao_gerada['ramo_atividade'])): ?>
                        <div class="col-md-<?= !empty($certidao_gerada['rg']) ? '8' : '12' ?> campo">
                            <span class="rotulo">Ramo de Atividade</span>
                            <?= htmlspecialchars($certidao_gerada['ramo_atividade']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-12 campo">
                            <span class="rotulo">Endereço</span>
                            <?= htmlspecialchars($contribuinte['endereco'] ?? '') ?>, <?= htmlspecialchars($contribuinte['numero'] ?? 'S/N') ?> - <?= htmlspecialchars($contribuinte['bairro'] ?? '') ?>, Centro do Guilherme-MA
                        </div>
                    </div>
                </div>

                <div class="lh-base text-justify" style="font-size: 0.95rem;">
                    <p class="mb-2">
                        Certifico, para fins de direito que se fizerem necessários, que <?= ($contribuinte['tipo_pessoa'] ?? 'PF') === 'PJ' ? 'a pessoa jurídica com a razão social denominada' : 'a pessoa física denominada' ?>: 
                        <strong><?= htmlspecialchars($contribuinte['nome_razao'] ?? '') ?></strong>, 
                        <?= $certidao_gerada['ramo_atividade'] ? 'com ramo de atividade: ' . htmlspecialchars($certidao_gerada['ramo_atividade']) . ',' : '' ?>
                        localizada na <?= htmlspecialchars($contribuinte['endereco'] ?? '') ?>, nº<?= htmlspecialchars($contribuinte['numero'] ?? 'S/N') ?>, <?= htmlspecialchars($contribuinte['bairro'] ?? '') ?>, Centro do Guilherme-MA, 
                        com inscrição no <?= ($contribuinte['tipo_pessoa'] ?? 'PF') === 'PJ' ? 'CNPJ' : 'CPF' ?>: <strong><?= htmlspecialchars($contribuinte['cpf_cnpj'] ?? '') ?></strong><?= $certidao_gerada['rg'] ? ', inscrito no RG nº: ' . htmlspecialchars($certidao_gerada['rg']) : '' ?>. 
                        <strong>NADA CONSTA</strong>, em relação a débitos de dívida ativa municipal, de natureza tributária, referente a <?= htmlspecialchars($certidao_gerada['tributos_referencia']) ?>, com o município de Centro do Guilherme-MA.
                    </p>
                    <p class="mb-2 fst-italic certidao-rodape-legal" style="font-size: 0.8rem; border-top: 1px solid #ddd; padding-top: 8px;">
                        <strong>Fundamentação Legal:</strong> Esta certidão é expedida nos estritos termos do Artigo 225° e parágrafo único da Lei do Código Tributário Municipal de Centro do Guilherme - MA, fazendo prova de quitação de tributos municipais requerida pelo interessado. A presente certidão goza de eficácia liberatória pelo prazo de 90 (noventa) dias a contar da data de sua expedição, ressalvado o direito da Fazenda Municipal de cobrar quaisquer dívidas que venham a ser apuradas posteriormente (Art. 227° do CTM).
                    </p>
                </div>
            <?php endif; ?>

            <div class="text-center certidao-assinatura">
                <p class="mb-3">Centro do Guilherme - MA, <?= date('d', strtotime($certidao_gerada['data_emissao'])) ?> de <?php $meses = [ '01'=>'janeiro', '02' => 'fevereiro', '03' => 'março', '04' => 'abril',
    '05' => 'maio', '06' => 'junho', '07' => 'julho', '08' => 'agosto',
    '09' => 'setembro', '10' => 'outubro', '11' => 'novembro', '12' => 'dezembro'
];
echo $meses[date('m')];
?> de <?= date('Y') ?></p>
                <p></p>
                <p class="mb-0">__________________________________________________</p>
                <p class="fw-bold mb-0"><?= htmlspecialchars($certidao_gerada['emissor_nome'] ?? 'Matheus Viana Lima') ?></p>
                <p class="mb-0 small"><?= htmlspecialchars($certidao_gerada['emissor_cargo'] ?? 'Chefe de Arrecadação do Setor Tributário') ?></p>
                <p class="text-muted small mb-0"><?= htmlspecialchars($certidao_gerada['emissor_portaria'] ?? 'Portaria 011/2025') ?></p>
                <p class="certidao-rodape-legal mt-2 mb-0">Selo de Autenticidade Digital: <strong><?= $certidao_gerada['codigo_validacao'] ?></strong> — A autenticidade pode ser conferida junto à Prefeitura Municipal de Centro do Guilherme.</p>
                <p class="certidao-rodape-legal mb-0">Documento emitido eletronicamente pelo Sistema de Arrecadação Municipal.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="assets/js/theme-toggle.js"></script>
</body>
</html>