<?php
require_once 'auth.php';
require_once 'db.php';

// Filtro Contribuintes
$busca_contribuinte = trim($_GET['busca_contribuinte'] ?? '');
$sql_c = "SELECT * FROM contribuintes";
$params_c = [];
if (!empty($busca_contribuinte)) {
    $sql_c .= " WHERE nome_razao LIKE :busca OR cpf_cnpj LIKE :busca OR inscricao_municipal LIKE :busca";
    $params_c[':busca'] = "%{$busca_contribuinte}%";
}
$sql_c .= " ORDER BY id DESC";
$stmtC = $pdo->prepare($sql_c);
$stmtC->execute($params_c);
$contribuintes = $stmtC->fetchAll();

// Filtro DAMs
$busca_dam = trim($_GET['busca_dam'] ?? '');
$sql_d = "SELECT d.*, c.nome_razao FROM documentos_dam d JOIN contribuintes c ON d.contribuinte_id = c.id";
$params_d = [];
if (!empty($busca_dam)) {
    $sql_d .= " WHERE d.numero_dam LIKE :busca OR c.nome_razao LIKE :busca OR d.receita_tributo LIKE :busca";
    $params_d[':busca'] = "%{$busca_dam}%";
}
$sql_d .= " ORDER BY d.id DESC";
$stmtD = $pdo->prepare($sql_d);
$stmtD->execute($params_d);
$dams = $stmtD->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Tributário Municipal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    
</head>
<body class="bg-light">

    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
        <button id="theme-toggle" class="theme-toggle-btn btn btn-sm btn-outline-secondary rounded-circle" title="Alternar Tema">🌙</button>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- SIDEBAR BARRA LATERAL FIXA -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse shadow-sm min-vh-100 p-3">
                <div class="text-center mb-4 border-bottom pb-3">
                    <img src="img.jpeg" alt="Logo Prefeitura" style="max-height: 50px;" class="mb-2">
                    <h6 class="fw-bold mb-0">Centro do Guilherme</h6>
                    <small class="text-muted">Setor Tributário</small>
                </div>

                <ul class="nav flex-column gap-1">
                    <li class="nav-item fw-bold text-muted small mt-2">CONTRIBUINTES</li>
                    <li class="nav-item">
                        <a class="nav-link text-dark py-1" href="cadastrar_contribuinte.php"><i class="bi bi-person-plus me-2"></i>Novo Contribuinte</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark py-1" href="gerar_dam.php"><i class="bi bi-file-earmark-text me-2"></i>Gerar DAM</a>
                    </li>

                    <li class="nav-item fw-bold text-muted small border-top pt-2">SISTEMA</li>
                    <li class="nav-item">
                        <a class="nav-link text-dark py-1" href="tributos.php"><i class="bi bi-coin me-2"></i>Tributos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark py-1" href="usuarios.php"><i class="bi bi-people me-2"></i>Usuários</a>
                    </li>
                    <li class="nav-item mt-3 border-top pt-2">
                        <a class="nav-link text-danger fw-bold py-1" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sair</a>
                    </li>
                </ul>
            </nav>

            <!-- PAINEL PRINCIPAL -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h4 class="fw-bold text-center mb-3 text-primary">Contribuintes Cadastrados</h4>
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mx-auto">
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                    <input type="text" id="filtroContribuinte" class="form-control" placeholder="Pesquisar por Nome, CPF/CNPJ ou Inscrição...">
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="tabelaContribuintes">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome / Razão Social</th>
                                        <th>CPF / CNPJ</th>
                                        <th>Inscrição Municipal</th>
                                        <th class="text-center">Ações Rápidas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($contribuintes as $c): ?>
                                    <tr>
                                        <td><?= $c['id'] ?></td>
                                        <td><?= htmlspecialchars($c['nome_razao'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($c['cpf_cnpj'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($c['inscricao_municipal'] ?? '') ?></td>
                                        <td class="text-center">
                                            <a href="gerar_dam.php?contribuinte_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary" title="Gerar DAM"><i class="bi bi-file-earmark-text"></i></a>
                                            <a href="emitir_certidao.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-success" title="Emitir Certidão"><i class="bi bi-award"></i></a>
                                            <a href="cadastrar_contribuinte.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="bi bi-pencil"></i></a>   
                                            <a href="emitir_nfa.php?id=<?= $c['id'] ?>" class="btn btn-outline-success" title="Emitir Nota Avulsa"><i class="bi bi-file-earmark-text"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

<script src="assets/js/theme-toggle.js"></script>
<script>
document.getElementById('filtroContribuinte')?.addEventListener('keyup', function() {
    let termo = this.value.toLowerCase();
    let linhas = document.querySelectorAll('#tabelaContribuintes tbody tr');
    
    linhas.forEach(linha => {
        let texto = linha.textContent.toLowerCase();
        linha.style.display = texto.includes(termo) ? '' : 'none';
    });
});
</script>
</body>
</html>