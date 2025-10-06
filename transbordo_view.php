<?php
require 'config.php';
require 'includes/auth.php';

$id = $_GET['id'] ?? 0;

// Buscar transbordo
$stmt = $pdo->prepare("SELECT t.*, 
                              ue.nome AS emissor, 
                              ur.nome AS receptor
    FROM transbordos t 
    JOIN usuarios ue ON t.id_usuario_emissor = ue.id_usuario 
    LEFT JOIN usuarios ur ON t.id_usuario_receptor = ur.id_usuario
    WHERE t.id_transbordo = ?");

$stmt->execute([$id]);
$transbordo = $stmt->fetch();

if (!$transbordo) {
    die("Transbordo não encontrado.");
}

// Buscar itens
$stmtItens = $pdo->prepare("SELECT ti.*, p.codigo, p.descricao 
    FROM transbordo_itens ti 
    JOIN produtos p ON ti.id_produto = p.id_produto 
    WHERE ti.id_transbordo = ?");
$stmtItens->execute([$id]);
$itens = $stmtItens->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Ficha de Transbordo Interno</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body class="bg-gray-100 p-6">
    <div class="max-w-5xl mx-auto bg-white p-6 shadow-lg border">
        <!-- Cabeçalho -->
        <div class="flex justify-between items-center mb-4">
            <div>
                <img src="logo.jpg" alt="Logo" class="h-32 mb-2">
            </div>
            <div class="text-center flex-1">
                <h1 class="text-lg font-bold uppercase">Ficha de Transbordo Interno</h1>
            </div>
            <div class="text-sm border p-2">
                <p><strong>Ficha Nº:</strong> <?= str_pad($transbordo['id_transbordo'], 5, "0", STR_PAD_LEFT); ?></p>
                <p><strong>Data:</strong> <?= date("d/m/Y", strtotime($transbordo['data_emissao'])); ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($transbordo['status']); ?></p>
                <p><strong>Observacao:</strong> <?= htmlspecialchars($transbordo['observacoes']); ?></p>
            </div>
        </div>

        <!-- Tabela de Itens -->
        <table class="w-full border border-black text-sm">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border px-2 py-1 w-20">Item</th>
                    <th class="border px-2 py-1">Descrição</th>
                    <th class="border px-2 py-1 w-24">Quantidade</th>
                    <th class="border px-2 py-1 w-24">Lote</th>
                    <th class="border px-2 py-1 w-24">Pallets</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                    <tr>
                        <td class="border px-2 py-1"><?= htmlspecialchars($item['codigo']); ?></td>
                        <td class="border px-2 py-1"><?= htmlspecialchars($item['descricao']); ?></td>
                        <td class="border px-2 py-1 text-center"><?= $item['quantidade']; ?></td>
                        <td class="border px-2 py-1 text-center"><?= htmlspecialchars($item['lote']); ?></td>
                        <td class="border px-2 py-1 text-center"><?= $item['qtd_pallets']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Assinaturas -->
        <div class="grid grid-cols-2 gap-8 text-center mt-12">
            <div>
                <div class="border-t border-black w-2/3 mx-auto"></div>
                <p class="mt-2">Assinatura Fábrica</p>
            </div>
            <div>
                <div class="border-t border-black w-2/3 mx-auto"></div>
                <p class="mt-2">Assinatura Expedição</p>
            </div>
        </div>
        <p class="mt-2 text-sm text-gray-700">Emitido por: <strong><?= htmlspecialchars($transbordo['emissor']); ?></strong></p>
        <p class="mt-2 text-sm text-gray-700">Recebido por: <strong><?= htmlspecialchars($transbordo['receptor']); ?></strong></p>

        <!-- Ações -->
        <div class="mt-6 flex gap-4 no-print">
            <?php if ($transbordo['status'] === 'emitido' && $transbordo['id_usuario_emissor'] != $_SESSION['user']['id']): ?>
                <!-- Botão Receber -->
                <form method="post" action="transbordo_sign.php" class="inline">
                    <input type="hidden" name="id" value="<?= $transbordo['id_transbordo'] ?>">
                    <input type="hidden" name="acao" value="receber">
                    <button type="submit" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                        Receber
                    </button>
                </form>
            <?php endif; ?>
            <?php if ($transbordo['status'] === 'emitido' && $transbordo['id_usuario_emissor'] == $_SESSION['user']['id']): ?>
                <button onclick="window.print()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Imprimir
                </button>
            <?php endif; ?>
            <?php if ($transbordo['status'] === 'recebido'): ?>
                <!-- Botão Imprimir -->
                <button onclick="window.print()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Imprimir
                </button>
            <?php endif; ?>

            <a href="index.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Voltar</a>
        </div>
    </div>
</body>

</html>