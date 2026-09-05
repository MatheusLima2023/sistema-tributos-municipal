<?php
require_once 'db.php';

$id = $_GET['id'] ?? null;
$contribuinte = [
    'tipo_pessoa' => 'PF', 'nome_razao' => '', 'cpf_cnpj' => '', 'rg' => '', 
    'inscricao_municipal' => '', 'ramo_atividade' => '', 'endereco' => '', 
    'bairro' => '', 'cidade' => 'Centro do Guilherme', 'uf' => 'MA', 'telefone' => '', 'email' => ''
];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM contribuintes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $contribuinte = $stmt->fetch() ?: $contribuinte;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        ':tipo_pessoa'         => $_POST['tipo_pessoa'],
        ':nome_razao'          => trim($_POST['nome_razao']),
        ':cpf_cnpj'            => trim($_POST['cpf_cnpj']),
        ':rg'                  => trim($_POST['rg']),
        ':inscricao_municipal' => trim($_POST['inscricao_municipal']),
        ':ramo_atividade'      => trim($_POST['ramo_atividade']),
        ':endereco'            => trim($_POST['endereco']),
        ':bairro'              => trim($_POST['bairro']),
        ':cidade'              => trim($_POST['cidade']),
        ':uf'                  => trim($_POST['uf']),
        ':telefone'            => trim($_POST['telefone']),
        ':email'               => trim($_POST['email']),
    ];

    if ($id) {
        $dados[':id'] = $id;
        $sql = "UPDATE contribuintes SET tipo_pessoa=:tipo_pessoa, nome_razao=:nome_razao, cpf_cnpj=:cpf_cnpj, rg=:rg, 
                inscricao_municipal=:inscricao_municipal, ramo_atividade=:ramo_atividade, endereco=:endereco, 
                bairro=:bairro, cidade=:cidade, uf=:uf, telefone=:telefone, email=:email WHERE id=:id";
    } else {
        $sql = "INSERT INTO contribuintes (tipo_pessoa, nome_razao, cpf_cnpj, rg, inscricao_municipal, ramo_atividade, endereco, bairro, cidade, uf, telefone, email) 
                VALUES (:tipo_pessoa, :nome_razao, :cpf_cnpj, :rg, :inscricao_municipal, :ramo_atividade, :endereco, :bairro, :cidade, :uf, :telefone, :email)";
    }

    $pdo->prepare($sql)->execute($dados);
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Editar' : 'Novo' ?> Contribuinte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container my-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><?= $id ? 'Editar' : 'Cadastrar' ?> Contribuinte</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tipo de Pessoa *</label>
                            <select name="tipo_pessoa" class="form-select" required>
                                <option value="PF" <?= $contribuinte['tipo_pessoa'] == 'PF' ? 'selected' : '' ?>>Pessoa Física (PF)</option>
                                <option value="PJ" <?= $contribuinte['tipo_pessoa'] == 'PJ' ? 'selected' : '' ?>>Pessoa Jurídica (PJ)</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Nome / Razão Social *</label>
                            <input type="text" name="nome_razao" class="form-control" value="<?= htmlspecialchars($contribuinte['nome_razao']) ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">CPF / CNPJ *</label>
                            <input type="text" name="cpf_cnpj" class="form-control" value="<?= htmlspecialchars($contribuinte['cpf_cnpj']) ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">RG (Para PF)</label>
                            <input type="text" name="rg" class="form-control" value="<?= htmlspecialchars($contribuinte['rg']) ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Inscrição Municipal</label>
                            <input type="text" name="inscricao_municipal" class="form-control" value="<?= htmlspecialchars($contribuinte['inscricao_municipal']) ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Ramo de Atividade</label>
                            <input type="text" name="ramo_atividade" class="form-control" value="<?= htmlspecialchars($contribuinte['ramo_atividade']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Endereço *</label>
                            <input type="text" name="endereco" class="form-control" value="<?= htmlspecialchars($contribuinte['endereco']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bairro *</label>
                            <input type="text" name="bairro" class="form-control" value="<?= htmlspecialchars($contribuinte['bairro']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cidade / UF</label>
                            <div class="input-group">
                                <input type="text" name="cidade" class="form-control" value="<?= htmlspecialchars($contribuinte['cidade']) ?>">
                                <input type="text" name="uf" class="form-control" style="max-width: 60px;" value="<?= htmlspecialchars($contribuinte['uf']) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($contribuinte['telefone']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($contribuinte['email']) ?>">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-success">Salvar</button>
                        <a href="index.php" class="btn btn-secondary">Voltar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>