<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$mensagem = '';
$tipoMensagem = '';

$contribuinte = [
    'id' => '',
    'nome_razao' => '',
    'cpf_cnpj' => '',
    'inscricao_municipal' => '',
    'email' => '',
    'telefone' => '',
    'endereco' => '',
    'bairro' => 'Centro',
    'cidade' => 'Centro do Guilherme',
    'uf' => 'MA'
];

$isEdicao = false;

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM contribuintes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dados) {
        $contribuinte = array_merge($contribuinte, $dados);
        $isEdicao = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $nomeRazao = trim($_POST['nome_razao'] ?? '');
    $cpfCnpj = trim($_POST['cpf_cnpj'] ?? '');
    $inscricaoMunicipal = trim($_POST['inscricao_municipal'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $cidade = trim($_POST['cidade'] ?? 'Centro do Guilherme');
    $uf = trim($_POST['uf'] ?? 'MA');

    try {
        if ($id > 0) {
            $sql = "UPDATE contribuintes SET 
                        nome_razao = :nome_razao,
                        cpf_cnpj = :cpf_cnpj,
                        inscricao_municipal = :inscricao_municipal,
                        email = :email,
                        telefone = :telefone,
                        endereco = :endereco,
                        bairro = :bairro,
                        cidade = :cidade,
                        uf = :uf
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome_razao' => $nomeRazao,
                ':cpf_cnpj' => $cpfCnpj,
                ':inscricao_municipal' => $inscricaoMunicipal,
                ':email' => $email,
                ':telefone' => $telefone,
                ':endereco' => $endereco,
                ':bairro' => $bairro,
                ':cidade' => $cidade,
                ':uf' => $uf,
                ':id' => $id
            ]);

            header("Location: listar_contribuintes.php?msg=atualizado");
            exit;
        } else {
            $sql = "INSERT INTO contribuintes (
                        nome_razao, cpf_cnpj, inscricao_municipal, email, telefone, 
                        endereco, bairro, cidade, uf
                    ) VALUES (
                        :nome_razao, :cpf_cnpj, :inscricao_municipal, :email, :telefone, 
                        :endereco, :bairro, :cidade, :uf
                    )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome_razao' => $nomeRazao,
                ':cpf_cnpj' => $cpfCnpj,
                ':inscricao_municipal' => $inscricaoMunicipal,
                ':email' => $email,
                ':telefone' => $telefone,
                ':endereco' => $endereco,
                ':bairro' => $bairro,
                ':cidade' => $cidade,
                ':uf' => $uf
            ]);

            header("Location: listar_contribuintes.php?msg=cadastrado");
            exit;
        }
    } catch (PDOException $e) {
        $mensagem = "Erro ao salvar contribuinte: " . $e->getMessage();
        $tipoMensagem = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdicao ? 'Editar' : 'Novo'; ?> Contribuinte - Prefeitura Municipal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/dark-mode.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-building"></i> CENTRO DO GUILHERME - Arrecadação
        </a>
        <div class="d-flex align-items-center">
            <a href="listar_contribuintes.php" class="btn btn-outline-light btn-sm me-2"><i class="bi bi-arrow-left"></i> Voltar à Lista</a>
            <button id="theme-toggle" class="theme-toggle-btn btn btn-sm btn-outline-light rounded-circle" title="Alternar Tema">🌙</button>
        </div>
    </div>
</nav>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            <?php if (!empty($mensagem)): ?>
                <div class="alert alert-<?= $tipoMensagem; ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($mensagem); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0 fw-bold">
                        <i class="bi bi-person-gear me-2"></i><?= $isEdicao ? 'Editar Contribuinte #' . sprintf('%04d', $contribuinte['id']) : 'Cadastrar Novo Contribuinte'; ?>
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="editar_contribuinte.php">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($contribuinte['id']); ?>">

                        <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-card-heading me-1"></i> Identificação</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-7">
                                <label class="form-label fw-bold">Nome Completo / Razão Social</label>
                                <input type="text" name="nome_razao" class="form-control" value="<?= htmlspecialchars($contribuinte['nome_razao']); ?>" required placeholder="Ex: João da Silva ME">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold">CPF ou CNPJ</label>
                                <input type="text" name="cpf_cnpj" id="cpf_cnpj" class="form-control" value="<?= htmlspecialchars($contribuinte['cpf_cnpj']); ?>" required placeholder="000.000.000-00 ou 00.000.000/0001-00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Inscrição Municipal</label>
                                <input type="text" name="inscricao_municipal" class="form-control" value="<?= htmlspecialchars($contribuinte['inscricao_municipal']); ?>" placeholder="Ex: 12345">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Telefone / WhatsApp</label>
                                <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($contribuinte['telefone']); ?>" placeholder="(99) 99999-9999">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">E-mail</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($contribuinte['email']); ?>" placeholder="contribuinte@email.com">
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-geo-alt me-1"></i> Endereço</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Endereço Completo (Rua, Número, Complemento)</label>
                                <input type="text" name="endereco" class="form-control" value="<?= htmlspecialchars($contribuinte['endereco']); ?>" required placeholder="Ex: Av. Castelo Branco, Nº 100">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold">Bairro</label>
                                <input type="text" name="bairro" class="form-control" value="<?= htmlspecialchars($contribuinte['bairro']); ?>" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold">Cidade</label>
                                <input type="text" name="cidade" class="form-control" value="<?= htmlspecialchars($contribuinte['cidade']); ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">UF</label>
                                <input type="text" name="uf" class="form-control text-center" value="<?= htmlspecialchars($contribuinte['uf']); ?>" maxlength="2" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3">
                            <a href="listar_contribuintes.php" class="btn btn-secondary btn-lg">Cancelar</a>
                            <button type="submit" class="btn btn-success btn-lg fw-bold">
                                <i class="bi bi-check-circle me-1"></i> Salvar Registro
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/theme-toggle.js"></script>
<script>
document.getElementById('cpf_cnpj').addEventListener('input', function (e) {
    let x = e.target.value.replace(/\D/g, '');
    if (x.length <= 11) {
        x = x.replace(/(\d{3})(\d)/, '$1.$2')
             .replace(/(\d{3})(\d)/, '$1.$2')
             .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else {
        x = x.replace(/^(\d{2})(\d)/, '$1.$2')
             .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
             .replace(/\.(\d{3})(\d)/, '.$1/$2')
             .replace(/(\d{4})(\d)/, '$1-$2');
    }
    e.target.value = x;
});
</script>
</body>
</html>