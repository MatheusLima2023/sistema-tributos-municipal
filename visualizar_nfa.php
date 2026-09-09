<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$idNota = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($idNota <= 0) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM notas_fiscais WHERE id = :id");
$stmt->execute([':id' => $idNota]);
$nota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nota) {
    die("Nota Fiscal não encontrada.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Fiscal Avulsa Nº <?= htmlspecialchars($nota['numero_nota']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #fff !important;
            }
            .card {
                border: 1px solid #000 !important;
                box-shadow: none !important;
            }
        }
        .header-logo {
            border-bottom: 2px solid #0d6efd;
        }
    </style>
</head>
<body class="bg-light">

<div class="container my-4 no-print">
    <div class="d-flex justify-content-between align-items-center">
        <a href="emissao_nfa.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Emitir Outra NFA</a>
        <div>
            <button onclick="window.print();" class="btn btn-primary fw-bold me-2"><i class="bi bi-printer"></i> Imprimir / Salvar PDF</button>
            <a href="index.php" class="btn btn-outline-dark"><i class="bi bi-house"></i> Início</a>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    
                    <!-- Cabeçalho Oficial -->
                    <div class="row align-items-center pb-3 mb-4 header-logo">
                        <div class="col-8">
                            <h4 class="fw-bold mb-1 text-uppercase">ESTADO DO MARANHÃO</h4>
                            <h5 class="fw-bold text-primary mb-1">PREFEITURA MUNICIPAL DE CENTRO DO GUILHERME</h5>
                            <p class="text-muted mb-0 small">SETOR DE ARRECADAÇÃO E TRIBUTOS MUNICIPAIS</p>
                        </div>
                        <div class="col-4 text-end border-start ps-3">
                            <span class="badge bg-primary fs-6 mb-1">NOTA FISCAL AVULSA</span>
                            <h5 class="fw-bold text-dark mb-0">Nº <?= htmlspecialchars($nota['numero_nota']); ?></h5>
                            <small class="text-muted">Data: <?= date('d/m/Y H:i', strtotime($nota['data_emissao'])); ?></small>
                        </div>
                    </div>

                    <!-- Prestador de Serviço -->
                    <div class="card mb-3 border">
                        <div class="card-header bg-light fw-bold text-uppercase py-2">
                            <i class="bi bi-person-badge"></i> Prestador do Serviço
                        </div>
                        <div class="card-body py-2">
                            <div class="row">
                                <div class="col-8">
                                    <strong>Razão Social / Nome:</strong> <?= htmlspecialchars($nota['prestador_nome']); ?>
                                </div>
                                <div class="col-4">
                                    <strong>CPF/CNPJ:</strong> <?= htmlspecialchars($nota['prestador_cpf_cnpj']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tomador de Serviço -->
                    <div class="card mb-3 border">
                        <div class="card-header bg-light fw-bold text-uppercase py-2">
                            <i class="bi bi-person"></i> Tomador do Serviço (Cliente)
                        </div>
                        <div class="card-body py-2">
                            <div class="row">
                                <div class="col-8">
                                    <strong>Razão Social / Nome:</strong> <?= htmlspecialchars($nota['tomador_nome']); ?>
                                </div>
                                <div class="col-4">
                                    <strong>CPF/CNPJ:</strong> <?= htmlspecialchars($nota['tomador_cpf_cnpj']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes do Serviço -->
                    <div class="card mb-4 border">
                        <div class="card-header bg-light fw-bold text-uppercase py-2">
                            <i class="bi bi-file-text"></i> Descrição dos Serviços
                        </div>
                        <div class="card-body">
                            <p class="card-text text-justify mb-0" style="white-space: pre-line;">
                                <?= htmlspecialchars($nota['descricao_servico']); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Valores e Impostos -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered text-center align-middle">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Valor Total do Serviço</th>
                                    <th>Alíquota ISS</th>
                                    <th>Valor do ISS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fs-5 fw-bold text-dark">R$ <?= number_format($nota['valor_total'], 2, ',', '.'); ?></td>
                                    <td><?= number_format($nota['aliquota_iss'], 2, ',', '.'); ?>%</td>
                                    <td class="fs-5 fw-bold text-success">R$ <?= number_format($nota['valor_iss'], 2, ',', '.'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Assinaturas / Autenticação -->
                    <div class="row mt-5 pt-4 text-center">
                        <div class="col-6">
                            <div class="border-top border-dark pt-1 mx-4">
                                <small>Assinatura do Prestador</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border-top border-dark pt-1 mx-4">
                                <small>Setor de Arrecadação Municipal</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>