<?php
require 'config.php';
require 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = uniqid("TRB-");
    $obs    = $_POST['observacoes'] ?? '';

    // inserir transbordo
    $stmt = $pdo->prepare("INSERT INTO transbordos 
    (observacoes, status, id_usuario_emissor) 
    VALUES (?, ?, ?)");
    $stmt->execute([$obs, 'emitido', $_SESSION['user']['id']]);

    $id_transbordo = $pdo->lastInsertId();


    // inserir itens
    if (!empty($_POST['produtos']) && is_array($_POST['produtos'])) {
        foreach ($_POST['produtos'] as $p) {
            $codigo     = trim($p['codigo'] ?? '');
            $lote       = trim($p['lote'] ?? '');
            $quantidade = floatval($p['quantidade'] ?? 0);
            $pallets    = intval($p['qtd_pallets'] ?? 0);

            if ($codigo !== '') {
                $stmtP = $pdo->prepare("SELECT id_produto FROM produtos WHERE codigo = ?");
                $stmtP->execute([$codigo]);
                $prod = $stmtP->fetch();
                if ($prod) {
                    $id_produto = $prod['id_produto'];
                    $stmtI = $pdo->prepare("INSERT INTO transbordo_itens 
                        (id_transbordo, id_produto, lote, quantidade, qtd_pallets) 
                        VALUES (?,?,?,?,?)");
                    $stmtI->execute([$id_transbordo, $id_produto, $lote, $quantidade, $pallets]);
                }
            }
        }
    }

    header("Location: transbordo_view.php?id=" . $id_transbordo);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Novo Transbordo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        let count = 0;

        function addProduto() {
            let div = document.getElementById('produtos');
            let index = count++;
            let html = `
<div class="mb-4 border p-3 rounded bg-gray-50 relative produto-item">
    <button type="button" onclick="removerProduto(this)" 
        class="absolute top-2 right-2 text-red-500 hover:text-red-700 font-bold">
        X
    </button>
    <div class="grid grid-cols-2 gap-2 mb-2">
        <div>
            <label class="block font-medium text-sm">Código</label>
            <input name="produtos[${index}][codigo]" 
                class="border rounded p-2 w-full codigo" 
                placeholder="Código" 
                onblur="buscarDescricao(this, ${index})" required>
        </div>
        <div>
            <label class="block font-medium text-sm">Descrição</label>
            <input type="text" id="desc_${index}" 
                class="border rounded p-2 bg-gray-100 w-full" 
                placeholder="Descrição" readonly>
        </div>
    </div>
    <div class="grid grid-cols-3 gap-2">
        <div>
            <label class="block font-medium text-sm">Lote</label>
            <input name="produtos[${index}][lote]" 
                class="border rounded p-2 w-full" placeholder="Lote">
        </div>
        <div>
            <label class="block font-medium text-sm">Quantidade</label>
            <input name="produtos[${index}][quantidade]" 
                type="number" step="0.01" 
                class="border rounded p-2 w-full" placeholder="Qtd">
        </div>
        <div>
            <label class="block font-medium text-sm">Pallets</label>
            <input name="produtos[${index}][qtd_pallets]" 
                type="number" 
                class="border rounded p-2 w-full" placeholder="Pallets">
        </div>
    </div>
</div>`;
            div.insertAdjacentHTML('beforeend', html);
        }

        function buscarDescricao(input, index) {
            let codigo = input.value.trim();
            if (codigo === '') return;

            fetch("produto_busca.php?codigo=" + encodeURIComponent(codigo))
                .then(res => res.json())
                .then(data => {
                    document.getElementById("desc_" + index).value = data.descricao || "Não encontrado";
                })
                .catch(() => {
                    document.getElementById("desc_" + index).value = "Erro ao buscar";
                });
        }

        function removerProduto(botao) {
            botao.parentElement.remove();
        }
    </script>

</head>

<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Novo Transbordo</h2>
        <form method="post" class="space-y-4">
            <div>
                <label class="block font-medium">Observações</label>
                <textarea name="observacoes" class="w-full border rounded p-2"></textarea>
            </div>

            <h4 class="text-lg font-semibold">Produtos</h4>
            <div id="produtos"></div>
            <button type="button" onclick="addProduto()"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Adicionar Produto
            </button>

            <div class="flex gap-3 mt-4">
                <button type="submit"
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Salvar
                </button>
                <a href="index.php"
                    class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</body>

</html>