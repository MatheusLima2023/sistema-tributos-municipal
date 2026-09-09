<?php
// topo dos arquivos emissao_nfa.php, salvar_nfa.php e imprimir_nfa.php
require_once __DIR__ . '/db.php';
$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM notas_fiscais_avulsas WHERE id = ?");
$stmt->execute([$id]);
$nfa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nfa) {
    die("Nota Fiscal Avulsa não encontrada.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Nota Fiscal Avulsa Nº <?= sprintf('%05d', $nfa['numero_nfa']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; }
        .page { width: 210mm; min-height: 297mm; padding: 10mm; margin: auto; background: white; border: 1px solid #ccc; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        th, td { border: 1px solid #000; padding: 4px; vertical-align: top; }
        .header-title { text-align: center; font-weight: bold; font-size: 13px; }
        .bg-gray { background-color: #e6e6e6; font-weight: bold; text-align: center; }
        .number-box { color: red; font-size: 16px; font-weight: bold; }
        @media print {
            .page { border: none; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 15px; text-align: center;">
    <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">🖨️ Imprimir Nota Fiscal</button>
</div>

<div class="page">
    <table>
        <tr>
            <td width="20%" style="text-align: center;"><strong style="font-size:18px;">PREFEITURA</strong><br>CENTRO DO GUILHERME</td>
            <td width="60%" class="header-title">
                PREFEITURA MUNICIPAL DE CENTRO DO GUILHERME<br>
                <small>CNPJ: 01.612.328/0001-21</small><br>
                <small>RUA DO COMERCIO, S/Nº, CENTRO, CEP 65288-000 - CENTRO DO GUILHERME-MA</small><br><br>
                <strong>NOTA FISCAL DE SERVIÇOS AVULSO</strong><br>
                <small>Impostos sobre Serviço de Qualquer Natureza</small>
            </td>
            <td width="20%" style="text-align: center;">
                Nº <span class="number-box"><?= sprintf('%05d', $nfa['numero_nfa']); ?></span><br><br>
                Data de Emissão:<br>
                <strong><?= date('d/m/Y', strtotime($nfa['data_emissao'])); ?></strong>
            </td>
        </tr>
    </table>

    <!-- PRESTADOR -->
    <table>
        <tr class="bg-gray"><td colspan="2">PRESTADOR DE SERVIÇOS</td></tr>
        <tr>
            <td>NOME: <strong><?= htmlspecialchars($nfa['prestador_nome']); ?></strong></td>
            <td>BAIRRO: <strong><?= htmlspecialchars($nfa['prestador_bairro']); ?></strong></td>
        </tr>
        <tr>
            <td>CIDADE: <strong><?= htmlspecialchars($nfa['prestador_cidade_uf']); ?></strong></td>
            <td>COMPLEMENTO: <strong>DF</strong></td>
        </tr>
        <tr>
            <td>ENDEREÇO: <strong><?= htmlspecialchars($nfa['prestador_endereco']); ?></strong></td>
            <td>Nº CEP: <strong><?= htmlspecialchars($nfa['prestador_cep']); ?></strong></td>
        </tr>
        <tr>
            <td>CPF/CNPJ: <strong><?= htmlspecialchars($nfa['prestador_cpf_cnpj']); ?></strong></td>
            <td>Inscrição: <strong><?= htmlspecialchars($nfa['prestador_inscricao']); ?></strong></td>
        </tr>
    </table>

    <!-- TOMADOR -->
    <table>
        <tr class="bg-gray"><td colspan="2">TOMADOR DE SERVIÇOS</td></tr>
        <tr>
            <td>NOME: <strong><?= htmlspecialchars($nfa['tomador_nome']); ?></strong></td>
            <td>BAIRRO: <strong><?= htmlspecialchars($nfa['tomador_bairro']); ?></strong></td>
        </tr>
        <tr>
            <td>CIDADE: <strong><?= htmlspecialchars($nfa['tomador_cidade_uf']); ?></strong></td>
            <td>COMPLEMENTO: <strong><?= htmlspecialchars($nfa['tomador_complemento']); ?></strong></td>
        </tr>
        <tr>
            <td>ENDEREÇO: <strong><?= htmlspecialchars($nfa['tomador_endereco']); ?></strong></td>
            <td>Nº CEP: <strong><?= htmlspecialchars($nfa['tomador_cep']); ?></strong></td>
        </tr>
        <tr>
            <td>CPF/CNPJ: <strong><?= htmlspecialchars($nfa['tomador_cpf_cnpj']); ?></strong></td>
            <td>Inscrição: </td>
        </tr>
    </table>

    <!-- DISCRIMINAÇÃO E EXTRATO -->
    <table>
        <tr class="bg-gray">
            <td width="60%">DISCRIMINAÇÃO DOS SERVIÇOS</td>
            <td width="40%">EXTRATO VALORES</td>
        </tr>
        <tr>
            <td rowspan="4"><strong><?= htmlspecialchars($nfa['discriminacao_servico']); ?></strong></td>
            <td>Valor Serviços: R$ <?= number_format($nfa['valor_servicos'], 2, ',', '.'); ?></td>
        </tr>
        <tr><td>VI. ISS: <?= number_format($nfa['aliquota_iss'], 1); ?>%: R$ <?= number_format($nfa['valor_iss'], 2, ',', '.'); ?></td></tr>
        <tr><td>VI. IRRF: <?= number_format($nfa['aliquota_irrf'], 1); ?>%: R$ <?= number_format($nfa['valor_irrf'], 2, ',', '.'); ?></td></tr>
        <tr><td>VI. Líquido: R$ <?= number_format($nfa['valor_liquido'], 2, ',', '.'); ?></td></tr>
    </table>

    <!-- DESCRIÇÃO -->
    <table>
        <tr class="bg-gray">
            <td width="80%">DESCRIÇÃO DOS SERVIÇOS</td>
            <td width="20%">VL. TOTAL</td>
        </tr>
        <tr style="height: 60px;">
            <td><?= htmlspecialchars($nfa['descricao_servico']); ?></td>
            <td style="text-align: right; vertical-align: bottom;"><strong>R$ <?= number_format($nfa['valor_servicos'], 2, ',', '.'); ?></strong></td>
        </tr>
    </table>

    <!-- RESUMO IMPOSTOS -->
    <table>
        <tr class="bg-gray"><td colspan="2">TOTAL DOS SERVIÇOS E IMPOSTOS</td></tr>
        <tr><td>Valor Líquido:</td><td>R$ <?= number_format($nfa['valor_liquido'], 2, ',', '.'); ?></td></tr>
        <tr><td>ISS: 5%</td><td>R$ <?= number_format($nfa['valor_iss'], 2, ',', '.'); ?></td></tr>
        <tr><td>IRRF: <?= number_format($nfa['aliquota_irrf'], 2); ?>%</td><td>R$ <?= number_format($nfa['valor_irrf'], 2, ',', '.'); ?></td></tr>
        <tr><td><strong>Total da Nota:</strong></td><td><strong>R$ <?= number_format($nfa['valor_servicos'], 2, ',', '.'); ?></strong></td></tr>
    </table>

    <!-- OUTRAS INFORMAÇÕES -->
    <table>
        <tr class="bg-gray"><td>OUTRAS INFORMAÇÕES</td></tr>
        <tr>
            <td>
                Mês de Competência: <strong><?= htmlspecialchars($nfa['mes_competencia']); ?></strong><br>
                Local de Prestação de Serviços: Centro do Guilherme - MA<br>
                Tributos: TRIBUTAVEIS<br>
                Recolhimento: A RECOLHER PELO PRESTADOR<br>
                Data do Vencimento do ISSQN desta NFS: <strong><?= date('d/m/Y', strtotime($nfa['data_vencimento_iss'])); ?></strong><br>
                Serviços Ret. IRRF: Administração de bens ou negócios em geral<br>
                Descrição de atividades: CEXSD-100-Valores para cobrança de Expedientes e Serviços Diversos
            </td>
        </tr>
    </table>
</div>

</body>
</html>