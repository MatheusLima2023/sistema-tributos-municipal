<?php
require_once 'auth.php';
require_once 'db.php';

$id_nfa = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_nfa <= 0) {
    die("Nota Fiscal não encontrada.");
}

// Busca os dados da NFA com as informações do Prestador e Tomador
$sql = "SELECT nfa.*, 
               p.nome_razao AS prestador_nome, p.cpf_cnpj AS prestador_documento, p.endereco AS prestador_endereco, p.bairro AS prestador_bairro, p.cidade AS prestador_cidade, p.uf AS prestador_uf,
               t.nome_razao AS tomador_nome, t.cpf_cnpj AS tomador_documento, t.endereco AS tomador_endereco, t.bairro AS tomador_bairro, t.cidade AS tomador_cidade, t.uf AS tomador_uf
        FROM notas_fiscais_avulsas nfa
        LEFT JOIN contribuintes p ON nfa.contribuinte_prestador_id = p.id
        LEFT JOIN contribuintes t ON nfa.contribuinte_tomador_id = t.id
        WHERE nfa.id = :id";
        
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_nfa]);
$nfa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nfa) {
    die("Nota Fiscal não encontrada.");
}

// Formatação das datas e valores
$data_emissao = date('d/m/Y H:i:s', strtotime($nfa['created_at'] ?? date('Y-m-d H:i:s')));
$valor_servico = number_format($nfa['valor_servico'], 2, ',', '.');
$aliquota = number_format($nfa['aliquota_iss'], 2, ',', '.');
$valor_iss = number_format($nfa['valor_iss'], 2, ',', '.');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Nota Fiscal Avulsa - <?= htmlspecialchars($nfa['numero_nfa']) ?></title>
    <style>
        * { box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
        body { background-color: #f4f4f4; margin: 0; padding: 20px; font-size: 15px; color: #000; }
        
        .nfe-container {
            width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 15px;
            border: 2px solid #000;
        }

        table { width: 100%; border-collapse: collapse; margin-bottom: -1px; }
        th, td { border: 1px solid #000; padding: 4px 6px; text-align: left; vertical-align: top; }

        .header-table td { border: 2px solid #000; }
        .logo-box { width: 90px; text-align: center; vertical-align: middle; }
        .logo-box img { max-width: 150px; max-height: 90px; }
        .title-box { text-align: center; vertical-align: middle; }
        .title-box h3 { margin: 2px 0; font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .title-box h2 { margin: 2px 0; font-size: 13px; font-weight: bold; text-transform: uppercase; }
        
        .info-box { width: 190px; font-size: 13px; }
        .info-box div { margin-bottom: 4px; }
        .info-box strong { display: block; font-size: 12px; text-transform: uppercase; }

        .section-header {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 14px;
            padding: 3px;
            border: 1px solid #000;
        }

        .disc-box { height: 220px; vertical-align: top; font-size: 13px; line-height: 1.4; white-space: pre-wrap; }
        .total-row { font-size: 15px; font-weight: bold; text-align: center; background-color: #f9f9f9; }

        .no-print { width: 800px; margin: 0 auto 15px auto; text-align: right; }
        .btn-print { background: #0d6efd; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: bold; }

        @media print {
            body { background: #fff; padding: 0; }
            .nfe-container { border: none; width: 100%; padding: 0; }
            .no-print { display: none; }
        }
        @media print {
             .page-break { 
             page-break-before: always; 
    }
}
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" class="btn-print">Imprimir / Salvar PDF</button>
</div>

<div class="nfe-container">
    
    <!-- CABEÇALHO -->
    <table class="header-table">
        <tr>
            <td class="logo-box" rowspan="2">
                <img src="img.jpeg" alt="Brasão">
            </td>
            <td class="title-box">
                <h3>PREFEITURA MUNICIPAL DE CENTRO DO GUILHERME</h3>
                <h3>SECRETARIA MUNICIPAL DE FAZENDA PÚBLICA</h3>
                <h2>NOTA FISCAL ELETRÔNICA DE SERVIÇOS - NFS-e</h2>
            </td>
            <td class="info-box">
                <strong>Número da Nota</strong>
                <span style="font-size: 13px; font-weight: bold;"><?= htmlspecialchars($nfa['numero_nfa']) ?></span>
            </td>
        </tr>
        <tr>
            <td class="title-box" style="font-size: 13px;">
                PRESTAÇÃO DE SERVIÇOS AVULSA
            </td>
            <td class="info-box">
                <strong>Data e Hora de Emissão</strong>
                <?= $data_emissao ?>
            </td>
        </tr>
    </table>

   <!-- PRESTADOR DE SERVIÇOS -->
<div class="section-header">PRESTADOR DE SERVIÇOS</div>
<table>
    <tr>
        <td>
            <strong>CPF/CNPJ:</strong> <?= htmlspecialchars($nfa['prestador_documento'] ?? '-') ?><br>
            <strong>Nome/Razão Social:</strong> <?= htmlspecialchars($nfa['prestador_nome'] ?? '-') ?><br>
            <strong>Endereço:</strong> <?= htmlspecialchars($nfa['prestador_endereco'] ?? 'Não informado') ?><br>
            <strong>Bairro:</strong> <?= htmlspecialchars($nfa['prestador_bairro'] ?? 'Centro') ?><br>
            <strong>Município:</strong> <?= htmlspecialchars($nfa['prestador_cidade'] ?? 'Centro do Guilherme-') ?><strong>-</strong> <strong>UF:</strong><strong></strong><?= htmlspecialchars($nfa['prestador_uf'] ?? 'Centro do Guilherme-') ?>
        </td>
    </tr>
</table>

<!-- TOMADOR DE SERVIÇOS -->
<div class="section-header">TOMADOR DE SERVIÇOS</div>
<table>
    <tr>
        <td>
            <strong>Nome/Razão Social:</strong> <?= htmlspecialchars($nfa['tomador_nome'] ?? 'CONSUMIDOR FINAL / NÃO CADASTRADO') ?><br>
            <strong>CPF/CNPJ:</strong> <?= htmlspecialchars($nfa['tomador_documento'] ?? '----') ?><br>
            <strong>Endereço:</strong> <?= htmlspecialchars($nfa['tomador_endereco'] ?? '----') ?><br>
            <strong>Bairro:</strong> <?= htmlspecialchars($nfa['tomador_bairro'] ?? '----') ?><br>
            <strong>Município:</strong> <?= htmlspecialchars($nfa['tomador_cidade'] ?? 'Centro do Guilherme-') ?><strong>-</strong> <strong>UF:</strong><strong></strong><?= htmlspecialchars($nfa['tomador_uf'] ?? 'Centro do Guilherme-') ?>
        </td>
    </tr>
</table>
    <!-- DISCRIMINAÇÃO DOS SERVIÇOS -->
    <div class="section-header">DISCRIMINAÇÃO DOS SERVIÇOS</div>
    <table>
        <tr>
            <td class="disc-box">
<?= htmlspecialchars($nfa['descricao_servico']) ?>
            </td>
        </tr>
    </table>

    <!-- VALOR TOTAL -->
    <table>
        <tr class="total-row">
            <td style="padding: 6px;">VALOR TOTAL DA NOTA = R$ <?= $valor_servico ?></td>
        </tr>
    </table>

    <!-- IMPOSTOS E DEDUÇÕES -->
    <table>
        <tr style="text-align: center; font-weight: bold; background: #f0f0f0; font-size: 13px;">
            <td>INSS (R$)</td>
            <td>IRRF (R$)</td>
            <td>CSLL (R$)</td>
            <td>COFINS (R$)</td>
            <td>PIS/PASEP (R$)</td>
        </tr>
        <tr style="text-align: center;">
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
        </tr>
    </table>

    <table>
        <tr style="text-align: center; font-weight: bold; background: #f0f0f0; font-size: 13px;">
            <td>Valor Total das Deduções (R$)</td>
            <td>Base de Cálculo (R$)</td>
            <td>Alíquota (%)</td>
            <td>Valor do ISS (R$)</td>
            <td>Crédito (R$)</td>
        </tr>
        <tr style="text-align: center;">
            <td>0,00</td>
            <td>R$ <?= $valor_servico ?></td>
            <td><?= $aliquota ?>%</td>
            <td>R$ <?= $valor_iss ?></td>
            <td>0,00</td>
        </tr>
    </table>

    <!-- OUTRAS INFORMAÇÕES -->
    <div class="section-header">OUTRAS INFORMAÇÕES</div>
    <table>
        <tr>
            <td style="font-size: 11px; line-height: 1.3;">
                (1) Esta NFA foi emitida com respaldo na Legislação Tributária Municipal de Centro do Guilherme - MA.<br>
                (2) Documento gerado eletronicamente pelo Sistema de Arrecadação Municipal.
            </td>
        </tr>
    </table>

</div>

<!-- Cole após a </div> que fecha a sua Nota Fiscal Avulsa atual -->

<div class="page-container page-break">
    <!-- CABEÇALHO DO DAM -->
    <table>
        <tr>
            <td class="logo-box">
                <img src="img.jpeg" alt="Brasão">
            </td>
            <td style="text-align: center; vertical-align: middle; font-weight: bold;">
                PREFEITURA MUNICIPAL DE CENTRO DO GUILHERME<br>
                SECRETARIA MUNICIPAL DE FINANÇAS E TRIBUTOS<br>
                <span style="font-size: 13px;">DOCUMENTO DE ARRECADAÇÃO MUNICIPAL - DAM</span>
            </td>
            <td style="width: 130px; text-align: center; vertical-align: middle; background: #f0f0f0;">
                <strong style="font-size: 13px; display: block;">VIA</strong>
                <strong style="font-size: 13px; display: block;">CONTRIBUINTE</strong>
            </td>
        </tr>
    </table>

    <!-- DADOS DO CONTRIBUINTE -->
    <!-- DADOS DO CONTRIBUINTE DO DAM -->
<div class="section-header">DADOS DO CONTRIBUINTE / PAGADOR</div>
<table>
    <tr>
        <td colspan="2">
            <strong>NOME / RAZÃO SOCIAL:</strong>
            <?= htmlspecialchars($nfa['prestador_nome'] ?? '-') ?>
        </td>
        <td>
            <strong>CPF / CNPJ:</strong>
            <?= htmlspecialchars($nfa['prestador_documento'] ?? '-') ?>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <strong>ENDEREÇO:</strong>
            <?= htmlspecialchars($nfa['prestador_endereco'] ?? 'Não informado') ?>
        </td>
        <td>
            <strong>BAIRRO:</strong>
            <?= htmlspecialchars($nfa['prestador_bairro'] ?? 'Centro') ?>
        </td>
    </tr>
</table>

    <!-- DETALHAMENTO DO TRIBUTO -->
    <div class="section-header">DETALHAMENTO DO TRIBUTO</div>
    <table>
        <tr>
            <td style="width: 25%;">
                <strong>NÚMERO DA NOTA:</strong><br>
                <?= htmlspecialchars($nfa['numero_nfa']) ?>
            </td>
            <td style="width: 50%;">
                <strong>RECEITA / TRIBUTO:</strong><br>
                ISSQN - NOTA FISCAL AVULSA
            </td>
            <td style="width: 25%;">
                <strong>DATA DE VENCIMENTO:</strong><br>
                <span style="color: #c00; font-weight: bold;">
                    <?= date('d/m/Y', strtotime(($nfa['created_at'] ?? date('Y-m-d')) . ' + 10 days')) ?>
                </span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>OBSERVAÇÕES / HISTÓRICO:</strong>
                <div style="font-size: 14px; margin-top: 4px;">
                    Ref. ao ISSQN devido da Nota Fiscal Avulsa Nº <?= htmlspecialchars($nfa['numero_nfa']) ?>.<br>
                    Prestação de Serviço realizada em Centro do Guilherme - MA.
                </div>
            </td>
            <td>
                <strong>VALOR DO ISS:</strong><br>
                R$ <?= number_format($nfa['valor_iss'], 2, ',', '.') ?><br><br>
                <strong>VALOR TOTAL A PAGAR:</strong><br>
                <span style="font-size: 15px; font-weight: bold;">R$ <?= number_format($nfa['valor_iss'], 2, ',', '.') ?></span>
            </td>
        </tr>
    </table>

    <!-- INSTRUÇÕES -->
    <div class="section-header">INSTRUÇÕES DE PAGAMENTO</div>
    <table>
        <tr>
            <td style="font-size: 9px; line-height: 1.4;">
                • Pagável nas agências bancárias credenciadas e correspondentes bancários do Município.<br>
                • Após o vencimento, incidirão juros e multa de mora conforme Legislação Tributária Municipal.<br>
                • Este documento não quita débitos anteriores.
            </td>
        </tr>
    </table>

    <!-- CÓDIGO DE BARRAS -->
    <div style="text-align: left; padding: 5px; border: 1px solid #000; margin-top: 10px; background: #fafafa;">
        <div style="font-family: monospace; font-size: 14px; font-weight: bold; margin-bottom: 8px;">
            <strong>DADOS BANCÁRIOS PARA PAGAMENTO:</strong>
                <p><strong>Banco:</strong> Bradesco | <strong>Agência:</strong> 1772-8 | <strong>Conta Corrente:</strong> 8413-1</p>
                <p><strong>Favorecido:</strong> P.M.C.G TRIBUTOS</p>
        </div>
    </div>
</div>

</body>
</html>