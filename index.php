<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

// Consultas de Estatísticas
$totalContribuintes = $pdo->query("SELECT COUNT(*) FROM contribuintes")->fetchColumn() ?: 0;
$totalNfas = $pdo->query("SELECT COUNT(*) FROM notas_fiscais")->fetchColumn() ?: 0;

// Exemplo de consultas para DAMs se a tabela existir
$totalDams = 0;
$damsPendentes = 0;
try {
    $totalDams = $pdo->query("SELECT COUNT(*) FROM dams")->fetchColumn() ?: 0;
    $damsPendentes = $pdo->query("SELECT COUNT(*) FROM dams WHERE status = 'pendente'")->fetchColumn() ?: 0;
} catch (Exception $e) {
    // Tabela DAMs ainda não criada
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Tributário - Prefeitura Municipal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <style>
        body {
            min-height: 100vh;
            overflow-x: hidden;
        }
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background-color: #1a1d20;
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 12px 20px;
            font-weight: 500;
            border-radius: 6px;
            margin: 2px 10px;
            transition: all 0.2s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background-color: #0d6efd;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="bg-light">

<div class="d-flex">
    <!-- Menu Lateral Esquerdo -->
    <aside class="sidebar d-flex flex-column justify-content-between p-3">
        <div>
            <div class="px-2 py-3 mb-3 border-bottom border-secondary text-center">
                <i class="bi bi-building fs-2 text-primary d-block mb-1"></i>
                <h6 class="fw-bold text-white mb-0">CENTRO DO GUILHERME</h6>
                <small class="text-muted">Arrecadação Municipal</small>
            </div>

            <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                    <a href="index.php" class="nav-link active">
                        <i class="bi bi-speedometer2"></i> Painel Geral
                    </a>
                </li>
                <li class="nav-item">
                    <a href="listar_contribuintes.php" class="nav-link">
                        <i class="bi bi-people"></i> Contribuintes
                    </a>
                </li>
                <li class="nav-item">
                    <a href="emissao_nfa.php" class="nav-link">
                        <i class="bi bi-file-earmark-plus"></i> Emitir NFA
                    </a>
                </li>
                <li class="nav-item">
                    <a href="listar_nfa.php" class="nav-link">
                        <i class="bi bi-receipt"></i> NFAs Emitidas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="emissao_dam.php" class="nav-link">
                        <i class="bi bi-card-checklist"></i> Emitir DAM
                    </a>
                </li>
                <li class="nav-item">
                    <a href="certidoes.php" class="nav-link">
                        <i class="bi bi-patch-check"></i> Certidões
                    </a>
                </li>
            </ul>
        </div>

        <div class="pt-3 border-top border-secondary d-flex justify-content-between align-items-center px-2">
            <span class="small text-muted">Tema</span>
            <button id="theme-toggle" class="btn btn-sm btn-outline-light rounded-circle" title="Alternar Tema">🌙</button>
        </div>
    </aside>

    <!-- Conteúdo Principal -->
    <main class="main-content flex-grow-1">
        <header class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
            <h4 class="fw-bold mb-0"><i class="bi bi-speedometer2 me-2"></i>Painel Principal</h4>
            <a href="emissao_nfa.php" class="btn btn-primary fw-bold">
                <i class="bi bi-plus-lg me-1"></i> Nova NFA
            </a>
        </header>

        <!-- Cards de Estatísticas -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 border-start border-4 border-primary">
                    <div class="card-body">
                        <div class="text-uppercase small fw-bold text-muted">Contribuintes Cadastrados</div>
                        <div class="fs-2 fw-bold text-dark mt-1"><?= $totalContribuintes; ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 border-start border-4 border-info">
                    <div class="card-body">
                        <div class="text-uppercase small fw-bold text-muted">NFAs Emitidas</div>
                        <div class="fs-2 fw-bold text-info mt-1"><?= $totalNfas; ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 border-start border-4 border-success">
                    <div class="card-body">
                        <div class="text-uppercase small fw-bold text-muted">Total de DAMs Gerados</div>
                        <div class="fs-2 fw-bold text-success mt-1"><?= $totalDams; ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 border-start border-4 border-warning">
                    <div class="card-body">
                        <div class="text-uppercase small fw-bold text-muted">DAMs Pendentes</div>
                        <div class="fs-2 fw-bold text-warning mt-1"><?= $damsPendentes; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-toggle.js"></script>
</body>
</html>