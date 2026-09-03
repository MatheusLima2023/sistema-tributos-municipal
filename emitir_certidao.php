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

$certidao_gerada = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_certidao   = $_POST['tipo_certidao'];
    $ramo_atividade  = trim($_POST['ramo_atividade'] ?? '');
    $rg              = trim($_POST['rg'] ?? '');
    $finalidade_uso  = trim($_POST['finalidade_uso'] ?? 'Fazer prova de Quitação de Tributos');

    // Automação: Gera código de verificação único e calcula validade (90 dias)
    $codigo_validacao = strtoupper(bin2hex(random_bytes(4))) . '-' . date('Y');
    $data_validade    = date('Y-m-d', strtotime('+90 days'));

    try {
        $sql = "INSERT INTO certidoes 
                (contribuinte_id, codigo_validacao, tipo_certidao, ramo_atividade, rg, finalidade_uso, data_validade) 
                VALUES (:contribuinte_id, :codigo_validacao, :tipo_certidao, :ramo_atividade, :rg, :finalidade_uso, :data_validade)";
        
        $stmtInsert = $pdo->prepare($sql);
        $stmtInsert->execute([
            ':contribuinte_id' => $contribuinte_id,
            ':codigo_validacao' => $codigo_validacao,
            ':tipo_certidao'   => $tipo_certidao,
            ':ramo_atividade'  => $ramo_atividade,
            ':rg'              => $rg,
            ':finalidade_uso'  => $finalidade_uso,
            ':data_validade'    => $data_validade
        ]);

        $certidao_id = $pdo->lastInsertId();
        
        // Redireciona para o modelo de visualização/impressão
        header("Location: emitir_certidao.php?id={$contribuinte_id}&certidao_id={$certidao_id}");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao registrar certidão: " . $e->getMessage();
    }
}

// Se já tiver uma certidão emitida recente para visualização
if (isset($_GET['certidao_id'])) {
    $stmtCert = $pdo->prepare("SELECT * FROM certidoes WHERE id = :id");
    $stmtCert->execute([':id' => $_GET['certidao_id']]);
    $certidao_gerada = $stmtCert->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emissão de Certidão - Setor Tributário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .certidao-box { border: none !important; shadow: none !important; }
        }
    </style>
</head>
<body class="bg-light">

    <div class="container my-4">
        <!-- SEÇÃO DE FORMULÁRIO (Visível apenas na tela) -->
        <?php if (!$certidao_gerada): ?>
            <div class="card shadow-sm no-print mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Gerar Documento/Certidão para: <strong><?= htmlspecialchars($contribuinte['nome_razao']) ?></strong></h5>
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
                                <label class="form-label">Ramo de Atividade (Se aplicável)</label>
                                <input type="text" name="ramo_atividade" class="form-control" placeholder="Ex: Serviços de funerárias / Comercio">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">RG (Para Pessoa Física)</label>
                                <input type="text" name="rg" class="form-control" placeholder="Ex: 1232192993 SSP/MA">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Finalidade / Observações</label>
                                <input type="text" name="finalidade_uso" class="form-control" value="Fazer prova de Quitação de Tributos">
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-success">Gerar e Imprimir Certidão</button>
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- MODELO DA CERTIDÃO OFICIAL IMPRESSA -->
        <?php if ($certidao_gerada): ?>
            <div class="no-print mb-3">
                <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir Documento</button>
                <a href="index.php" class="btn btn-secondary">Voltar ao Painel</a>
            </div>

            <div class="card p-5 bg-white certidao-box shadow-sm border" style="min-height: 800px;">
                <div class="text-center mb-4">
                    <h5 class="fw-bold mb-1">PREFEITURA MUNICIPAL DE CENTRO DO GUILHERME</h5>
                    <h6 class="text-muted">Secretaria Municipal da Fazenda Pública</h6>
                    <hr class="my-3">
                    <h5 class="fw-bold text-uppercase mt-3">
                        <?php 
                            if ($certidao_gerada['tipo_certidao'] === 'NEGATIVA') echo "CERTIDÃO NEGATIVA DE DÉBITOS";
                            elseif ($certidao_gerada['tipo_certidao'] === 'POSITIVA_COM_EFEITO_DE_NEGATIVA') echo "CERTIDÃO POSITIVA COM EFEITO NEGATIVO DE DÉBITOS RELATIVOS AOS TRIBUTOS MUNICIPAIS E À DÍVIDA ATIVA DO MUNICÍPIO";
                            else echo "COMPROVANTE DE INSCRIÇÃO MUNICIPAL";
                        ?>
                    </h5>
                </div>

                <div class="my-4 lh-lg" style="font-size: 1.1rem; text-align: justify;">
                    <?php if ($certidao_gerada['tipo_certidao'] === 'COMPROVANTE_INSCRICAO_MUNICIPAL'): ?>
                        <p><strong>Inscrição Municipal:</strong> <?= htmlspecialchars($contribuinte['inscricao_municipal'] ?: '00027') ?></p>
                        <p><strong>Razão Social:</strong> <?= htmlspecialchars($contribuinte['nome_razao']) ?></p>
                        <p><strong>CPF/CNPJ:</strong> <?= htmlspecialchars($contribuinte['cpf_cnpj']) ?></p>
                        <p><strong>Atividade Principal:</strong> <?= htmlspecialchars($certidao_gerada['ramo_atividade'] ?: 'Não informada') ?></p>
                        <p><strong>Endereço:</strong> <?= htmlspecialchars($contribuinte['endereco']) ?>, <?= htmlspecialchars($contribuinte['bairro']) ?></p>
                        <p><strong>Município:</strong> Centro do Guilherme - MA</p>
                    <?php else: ?>
                        <p>
                            Certifico, para os fins de direito que se fizerem necessários, que <?= $contribuinte['tipo_pessoa'] === 'PJ' ? 'a pessoa jurídica com a razão social denominada' : 'a pessoa física denominada' ?>: 
                            <strong><?= htmlspecialchars($contribuinte['nome_razao']) ?></strong>, 
                            <?php if ($certidao_gerada['ramo_atividade']): ?>com ramo de atividade: <?= htmlspecialchars($certidao_gerada['ramo_atividade']) ?>,<?php endif; ?>
                            localizada na <?= htmlspecialchars($contribuinte['endereco']) ?>, <?= htmlspecialchars($contribuinte['bairro']) ?>, Centro do Guilherme-MA, 
                            com inscrição no <?= $contribuinte['tipo_pessoa'] === 'PJ' ? 'CNPJ' : 'CPF' ?>: <strong><?= htmlspecialchars($contribuinte['cpf_cnpj']) ?></strong>
                            <?= $certidao_gerada['rg'] ? ", inscrito no RG nº: " . htmlspecialchars($certidao_gerada['rg']) : '' ?>.
                        </p>
                        <p>
                            <strong>NADA CONSTA</strong> em relação a débitos de dívida ativa municipal, de natureza tributária, referente a <?= htmlspecialchars($certidao_gerada['tributos_referencia']) ?>, com o município de Centro do Guilherme-MA.
                        </p>
                        <p class="mt-4">
                            <strong>Fundamentação Legal:</strong> Esta certidão é expedida nos estritos termos do Artigo 225º e parágrafo único da Lei do Código Tributário Municipal de Centro do Guilherme - MA, fazendo prova de quitação de tributos municipais requerida pelo interessado. A presente certidão goza de eficácia liberatória pelo prazo de 90 (noventa) dias a contar da data de sua expedição, ressalvado o direito da Fazenda Municipal de cobrar quaisquer dívidas que venham a ser apuradas posteriormente (Art. 227º do CTM).
                        </p>
                    <?php endif; ?>
                </div>

                <div class="mt-auto pt-5 text-center">
                    <p>Centro do Guilherme - MA, <?= date('d', strtotime($certidao_gerada['data_emissao'])) ?> de <?= date('m') ?> de <?= date('Y') ?></p>
                    <br><br>
                    <p class="mb-0">__________________________________________________</p>
                    <p class="fw-bold mb-0"><?= htmlspecialchars($certidao_gerada['emissor_nome']) ?></p>
                    <p class="mb-0"><?= htmlspecialchars($certidao_gerada['emissor_cargo']) ?></p>
                    <p class="text-muted small"><?= htmlspecialchars($certidao_gerada['emissor_portaria']) ?></p>
                </div>

                <div class="border-top pt-2 mt-4 text-center text-muted small">
                    <p class="mb-0">Código de Autenticidade: <strong><?= htmlspecialchars($certidao_gerada['codigo_validacao']) ?></strong></p>
                    <p class="mb-0">Rua do Comércio, nº 263 - Centro - CNPJ: 01.612.328/0001-21 - CEP: 65.288.000 - Centro do Guilherme</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>