
<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
require_once 'auth.php';
require_once 'db.php';

$id = $_GET['id'] ?? null;
$contribuinte = [
    'tipo_pessoa'         => 'PF',
    'nome_razao'          => '',
    'cpf_cnpj'            => '',
    'rg'                  => '',
    'inscricao_municipal' => '',
    'ramo_atividade'      => '',
    'endereco'            => '',
    'numero'              => '',
    'bairro'              => '',
    'cidade'              => 'Centro do Guilherme',
    'uf'                  => 'MA'
];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM contribuintes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $contribuinte = $stmt->fetch() ?: $contribuinte;
}

$ramos     = $pdo->query("SELECT DISTINCT ramo_atividade FROM contribuintes WHERE ramo_atividade IS NOT NULL AND ramo_atividade != ''")->fetchAll(PDO::FETCH_COLUMN);
$enderecos = $pdo->query("SELECT DISTINCT endereco FROM contribuintes WHERE endereco IS NOT NULL AND endereco != ''")->fetchAll(PDO::FETCH_COLUMN);
$bairros   = $pdo->query("SELECT DISTINCT bairro FROM contribuintes WHERE bairro IS NOT NULL AND bairro != ''")->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        ':tipo_pessoa'         => $_POST['tipo_pessoa'] ?? 'PF',
        ':nome_razao'          => trim($_POST['nome_razao']),
        ':cpf_cnpj'            => trim($_POST['cpf_cnpj']),
        ':rg'                  => trim($_POST['rg']),
        ':inscricao_municipal' => trim($_POST['inscricao_municipal']),
        ':ramo_atividade'      => trim($_POST['ramo_atividade']),
        ':endereco'            => trim($_POST['endereco']),
        ':numero'              => trim($_POST['numero']),
        ':bairro'              => trim($_POST['bairro']),
        ':cidade'              => trim($_POST['cidade']),
        ':uf'                  => trim($_POST['uf'])
    ];

    if ($id) {
        $dados[':id'] = $id;
        $sql = "UPDATE contribuintes SET 
                    tipo_pessoa=:tipo_pessoa, nome_razao=:nome_razao, cpf_cnpj=:cpf_cnpj, rg=:rg, 
                    inscricao_municipal=:inscricao_municipal, ramo_atividade=:ramo_atividade, 
                    endereco=:endereco, numero=:numero, bairro=:bairro, cidade=:cidade, uf=:uf 
                WHERE id=:id";
    } else {
        $sql = "INSERT INTO contribuintes (tipo_pessoa, nome_razao, cpf_cnpj, rg, inscricao_municipal, ramo_atividade, endereco, numero, bairro, cidade, uf) 
                VALUES (:tipo_pessoa, :nome_razao, :cpf_cnpj, :rg, :inscricao_municipal, :ramo_atividade, :endereco, :numero, :bairro, :cidade, :uf)";
    }

    $pdo->prepare($sql)->execute($dados);
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? 'Editar' : 'Novo' ?> Contribuinte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/dark-mode.css">
</head>
<body class="bg-light">

<div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
    <button id="theme-toggle" class="theme-toggle-btn btn btn-sm btn-outline-secondary rounded-circle" title="Alternar Tema">🌙</button>
</div>

<div class="container my-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><?= $id ? 'Editar' : 'Cadastrar' ?> Contribuinte</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Tipo de Pessoa *</label>
                        <select name="tipo_pessoa" class="form-select">
                            <option value="PF" <?= $contribuinte['tipo_pessoa'] == 'PF' ? 'selected' : '' ?>>Pessoa Física (PF)</option>
                            <option value="PJ" <?= $contribuinte['tipo_pessoa'] == 'PJ' ? 'selected' : '' ?>>Pessoa Jurídica (PJ)</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Nome / Razão Social *</label>
                        <input type="text" name="nome_razao" class="form-control" value="<?= htmlspecialchars($contribuinte['nome_razao']) ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">CPF / CNPJ *</label>
                        <input type="text" id="cpf_cnpj" name="cpf_cnpj" class="form-control" value="<?= htmlspecialchars($contribuinte['cpf_cnpj']) ?>" placeholder="000.000.000-00" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">Inscrição Estadual / RG</label>
                        <input type="text" id="inscricao_estadual" name="rg" class="form-control" value="<?= htmlspecialchars($contribuinte['rg']) ?>" placeholder="00.000.000-0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Inscrição Municipal</label>
                        <input type="text" name="inscricao_municipal" class="form-control" value="<?= htmlspecialchars($contribuinte['inscricao_municipal']) ?>">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-bold">Ramo de Atividade</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="ramo_atividade" class="form-control" list="listaRamos" value="<?= htmlspecialchars($contribuinte['ramo_atividade']) ?>" placeholder="Busque ou digite a atividade">
                        </div>
                        <datalist id="listaRamos">
                            <?php foreach ($ramos as $ramo): ?>
                                <option value="<?= htmlspecialchars($ramo) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="col-md-7">
                        <label class="form-label fw-bold">Endereço (Logradouro)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="endereco" class="form-control" list="listaEnderecos" value="<?= htmlspecialchars($contribuinte['endereco']) ?>" placeholder="Busque ou digite o endereço">
                        </div>
                        <datalist id="listaEnderecos">
                            <?php foreach ($enderecos as $end): ?>
                                <option value="<?= htmlspecialchars($end) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold">Número</label>
                        <input type="text" name="numero" class="form-control" value="<?= htmlspecialchars($contribuinte['numero']) ?>" placeholder="Nº">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Bairro</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="bairro" class="form-control" list="listaBairros" value="<?= htmlspecialchars($contribuinte['bairro']) ?>" placeholder="Busque ou digite o bairro">
                        </div>
                        <datalist id="listaBairros">
                            <?php foreach ($bairros as $b): ?>
                                <option value="<?= htmlspecialchars($b) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Cidade / UF</label>
                        <div class="input-group">
                            <input type="text" name="cidade" class="form-control" value="<?= htmlspecialchars($contribuinte['cidade']) ?>" readonly>
                            <input type="text" name="uf" class="form-control" style="max-width: 70px;" value="<?= htmlspecialchars($contribuinte['uf']) ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-success px-4 me-2">Salvar</button>
                    <a href="index.php" class="btn btn-secondary px-4">Voltar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/theme-toggle.js"></script>
</body>
</html>