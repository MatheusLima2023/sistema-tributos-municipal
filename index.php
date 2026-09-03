<?php
require_once 'db.php';

// Busca simples de contribuintes para validar a conexão e exibir na tela
$sql = "SELECT * FROM contribuintes ORDER BY id DESC LIMIT 10";
$stmt = $pdo->query($sql);
$contribuintes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Tributário Municipal - Centro do Guilherme</title>
    <!-- Estilização simples e moderna via Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                🏛️ Tributos Municipais - Centro do Guilherme
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Contribuintes Cadastrados</h2>
            <a href="cadastrar_contribuinte.php" class="btn btn-success">+ Novo Contribuinte</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Nome / Razão Social</th>
                            <th>CPF / CNPJ</th>
                            <th>Cidade/UF</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($contribuintes) > 0): ?>
                            <?php foreach ($contribuintes as $c): ?>
                                <tr>
                                    <td><?= htmlspecialchars($c['id']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($c['tipo_pessoa']) ?></span></td>
                                    <td><?= htmlspecialchars($c['nome_razao']) ?></td>
                                    <td><?= htmlspecialchars($c['cpf_cnpj']) ?></td>
                                    <td><?= htmlspecialchars($c['cidade']) ?>/<?= htmlspecialchars($c['uf']) ?></td>
                                    <td>
                                        <a href="gerar_dam.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">Gerar DAM</a>
                                        <a href="emitir_certidao.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-success">Emitir Certidão</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Nenhum contribuinte cadastrado até o momento.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>