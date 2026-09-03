<?php
require_once 'db.php';

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_pessoa         = $_POST['tipo_pessoa'] ?? 'PF';
    $nome_razao          = trim($_POST['nome_razao'] ?? '');
    $cpf_cnpj            = trim($_POST['cpf_cnpj'] ?? '');
    $inscricao_municipal = trim($_POST['inscricao_municipal'] ?? '');
    $endereco            = trim($_POST['endereco'] ?? '');
    $bairro              = trim($_POST['bairro'] ?? '');
    $cidade              = trim($_POST['cidade'] ?? 'Centro do Guilherme');
    $uf                  = trim($_POST['uf'] ?? 'MA');
    $telefone            = trim($_POST['telefone'] ?? '');
    $email               = trim($_POST['email'] ?? '');

    if (!empty($nome_razao) && !empty($cpf_cnpj) && !empty($endereco)) {
        try {
            $sql = "INSERT INTO contribuintes 
                    (tipo_pessoa, nome_razao, cpf_cnpj, inscricao_municipal, endereco, bairro, cidade, uf, telefone, email) 
                    VALUES (:tipo_pessoa, :nome_razao, :cpf_cnpj, :inscricao_municipal, :endereco, :bairro, :cidade, :uf, :telefone, :email)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':tipo_pessoa'         => $tipo_pessoa,
                ':nome_razao'          => $nome_razao,
                ':cpf_cnpj'            => $cpf_cnpj,
                ':inscricao_municipal' => $inscricao_municipal,
                ':endereco'            => $endereco,
                ':bairro'              => $bairro,
                ':cidade'              => $cidade,
                ':uf'                  => $uf,
                ':telefone'            => $telefone,
                ':email'               => $email
            ]);

            header("Location: index.php?status=sucesso");
            exit;
        } catch (PDOException $e) {
            $mensagem = "Erro ao cadastrar: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    } else {
        $mensagem = "Preencha todos os campos obrigatórios!";
        $tipo_mensagem = "warning";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Contribuinte - Setor Tributário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">🏛️ Tributos Municipais - Centro do Guilherme</a>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h4 class="mb-0">Cadastrar Novo Contribuinte</h4>
            </div>
            <div class="card-body">
                <?php if ($mensagem): ?>
                    <div class="alert alert-<?= $tipo_mensagem ?>"><?= htmlspecialchars($mensagem) ?></div>
                <?php endif; ?>

                <form method="POST" action="cadastrar_contribuinte.php">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tipo de Pessoa *</label>
                            <select name="tipo_pessoa" class="form-select" required>
                                <option value="PF">Pessoa Física (PF)</option>
                                <option value="PJ">Pessoa Jurídica (PJ)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nome / Razão Social *</label>
                            <input type="text" name="nome_razao" class="form-content form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">CPF / CNPJ *</label>
                            <input type="text" name="cpf_cnpj" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Inscrição Municipal</label>
                            <input type="text" name="inscricao_municipal" class="form-control">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Endereço *</label>
                            <input type="text" name="endereco" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bairro *</label>
                            <input type="text" name="bairro" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="cidade" class="form-control" value="Centro do Guilherme">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">UF</label>
                            <input type="text" name="uf" class="form-control" value="MA">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Salvar Contribuinte</button>
                        <a href="index.php" class="btn btn-secondary">Voltar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
