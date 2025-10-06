<?php
require 'config.php';
require 'includes/auth.php';

$user = $_SESSION['user'];
$nome=strtolower($user['nome']);
$isAdmin = strtolower($user['setor']) === 'admin';
$isLogistica = strtolower($user['setor']) === 'logistica';

// ----- Filtros -----
$filtros = [];
$params  = [];

if ($isAdmin || $isLogistica) {
    if (!empty($_GET['usuario'])) {
        $filtros[] = "(ue.nome LIKE ? OR ur.nome LIKE ?)";
        $params[]  = "%" . $_GET['usuario'] . "%";
        $params[]  = "%" . $_GET['usuario'] . "%";
    }

    if (!empty($_GET['produto'])) {
        $filtros[] = "EXISTS (
            SELECT 1 
            FROM transbordo_itens ti
            JOIN produtos p ON p.id_produto = ti.id_produto
            WHERE ti.id_transbordo = t.id_transbordo
              AND (p.id_produto = ? OR p.codigo = ?)
        )";
        $params[] = $_GET['produto'];
        $params[] = $_GET['produto'];
    }

    if (!empty($_GET['data'])) {
        $filtros[] = "DATE(t.data_emissao) = ?";
        $params[] = $_GET['data'];
    }

    if (!empty($_GET['setor'])) {
        $filtros[] = "(ue.setor = ? OR ur.setor = ?)";
        $params[] = $_GET['setor'];
        $params[] = $_GET['setor'];
    }
} else {
    $filtros[] = "(t.id_usuario_emissor = ? OR t.id_usuario_receptor = ?)";
    $params[]  = $user['id'];
    $params[]  = $user['id'];
}

// ----- Ordenação -----
$validColumns = ['data_emissao', 'status'];
$orderBy = in_array($_GET['order_by'] ?? '', $validColumns) ? $_GET['order_by'] : 'data_emissao';
$orderDir = ($_GET['order_dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

// Monta WHERE final
$where = $filtros ? "WHERE " . implode(" AND ", $filtros) : "";

// Query base
$sql = "SELECT t.id_transbordo, t.data_emissao, t.status, t.observacoes,
               ue.nome AS emissor, ur.nome AS receptor
        FROM transbordos t
        LEFT JOIN usuarios ue ON ue.id_usuario = t.id_usuario_emissor
        LEFT JOIN usuarios ur ON ur.id_usuario = t.id_usuario_receptor
        $where
        ORDER BY $orderBy $orderDir";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transbordos = $stmt->fetchAll();

// Função helper para montar link de ordenação
function orderLink($column, $label, $currentCol, $currentDir)
{
    $newDir = ($currentCol === $column && $currentDir === 'ASC') ? 'DESC' : 'ASC';
    $arrow = '';
    if ($currentCol === $column) {
        $arrow = $currentDir === 'ASC' ? '↑' : '↓';
    }
    $query = $_GET;
    $query['order_by'] = $column;
    $query['order_dir'] = $newDir;
    $url = '?' . http_build_query($query);
    return "<a href=\"$url\" class=\"flex items-center justify-center gap-1\">$label <span>$arrow</span></a>";
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Transbordos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-6xl mx-auto bg-white shadow-lg rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold">Lista de Transbordos de <?= htmlspecialchars($nome ) ?></h2>
            <div class="flex gap-2">
                <?php if ($isAdmin || $isLogistica): ?>
                    <a href="index.php" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                        Limpar
                    </a>
                <?php endif; ?>
                <a href="transbordo_add.php" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
                    Novo
                </a>
                <?php if ($isAdmin): ?>
                    <a href="usuarios.php" class="bg-yellow-500 text-white px-6 py-2 rounded hover:bg-yellow-600">Usuários</a>
                <?php endif; ?>
                <a href="logout.php" class="bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600">Sair</a>
            </div>
        </div>

        <?php if ($isAdmin || $isLogistica): ?>
            <form method="get" class="grid grid-cols-5 gap-4 mb-6">
                <input type="text" name="usuario" placeholder="Nome do Usuário"
                    class="border rounded p-2"
                    value="<?= htmlspecialchars($_GET['usuario'] ?? '') ?>">
                <input type="text" name="produto" placeholder="Código Produto"
                    class="border rounded p-2"
                    value="<?= htmlspecialchars($_GET['produto'] ?? '') ?>">
                <input type="date" name="data"
                    class="border rounded p-2"
                    value="<?= htmlspecialchars($_GET['data'] ?? '') ?>">
                <input type="text" name="setor" placeholder="Setor"
                    class="border rounded p-2"
                    value="<?= htmlspecialchars($_GET['setor'] ?? '') ?>">
                <button type="submit"
                    class="col-span-1 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Filtrar
                </button>
            </form>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="border p-2">ID</th>
                        <th class="border p-2">
                            <?= orderLink('data_emissao', 'Data', $orderBy, $orderDir) ?>
                        </th>
                        <th class="border p-2">Emissor</th>
                        <th class="border p-2">Receptor</th>
                        <th class="border p-2">
                            <?= orderLink('status', 'Status', $orderBy, $orderDir) ?>
                        </th>
                        <th class="border p-2">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($transbordos): ?>
                        <?php foreach ($transbordos as $t): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="border p-2 text-center"><?= $t['id_transbordo'] ?></td>
                                <td class="border p-2 text-center"><?= date("d/m/Y H:i", strtotime($t['data_emissao'])) ?></td>
                                <td class="border p-2"><?= htmlspecialchars($t['emissor']) ?></td>
                                <td class="border p-2"><?= htmlspecialchars($t['receptor']) ?></td>
                                <td class="border p-2 text-center"><?= ucfirst($t['status']) ?></td>
                                <td class="border p-2 text-center">
                                    <a href="transbordo_view.php?id=<?= $t['id_transbordo'] ?>"
                                        class="text-blue-600 hover:underline">Ver</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="border p-4 text-center text-gray-500">
                                Nenhum transbordo encontrado
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>