<?php
require 'config.php';
require 'includes/auth.php';

// apenas admins
$isAdmin = in_array(strtolower($_SESSION['user']['setor']), ['admin']);
if (!$isAdmin) die("Acesso negado.");


$erroAdd = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'add') {
    $nome = $_POST['nome'] ?? '';
    $login = $_POST['login'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $setor = $_POST['setor'] ?? '';

    if ($nome && $login && $senha && $setor) {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, login, senha_hash, setor) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $login, $senha_hash, $setor]);
        header("Location: usuarios.php");
        exit;
    } else {
        $erroAdd = "Todos os campos são obrigatórios.";
    }
}

$stmt = $pdo->query("SELECT id_usuario, nome, login, setor FROM usuarios ORDER BY nome");
$usuarios = $stmt->fetchAll();

// Para edição (AJAX/modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'edit') {
    $id = $_POST['id_usuario'];
    $nome = $_POST['nome'];
    $login = $_POST['login'];
    $setor = $_POST['setor'];
    $senha = $_POST['senha'] ?? '';

    if ($nome && $login && $setor) {
        if ($senha) {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET nome=?, login=?, setor=?, senha_hash=? WHERE id_usuario=?");
            $stmt->execute([$nome, $login, $setor, $senha_hash, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET nome=?, login=?, setor=? WHERE id_usuario=?");
            $stmt->execute([$nome, $login, $setor, $id]);
        }
        header("Location: usuarios.php");
        exit;
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario=?");
    $stmt->execute([$id]);
    header("Location: usuarios.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>CRUD Usuários</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">
    <a href="index.php" class="bg-gray-500 ml-4 text-white px-6 py-2 rounded hover:bg-blue-600">
        Voltar
    </a>
    <div class="max-w-7xl mx-auto grid grid-cols-2 gap-6 mt-10">

        <div class="bg-white p-6 shadow-lg rounded">
            <h2 class="text-xl font-bold mb-4">Criar Usuário</h2>

            <?php if ($erroAdd): ?>
                <div class="bg-red-100 text-red-700 p-2 mb-4 rounded"><?= $erroAdd ?></div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <input type="hidden" name="acao" value="add">
                <div>
                    <label class="block mb-1">Nome</label>
                    <input type="text" name="nome" class="w-full border rounded p-2" required>
                </div>
                <div>
                    <label class="block mb-1">Login</label>
                    <input type="text" name="login" class="w-full border rounded p-2" required>
                </div>
                <div>
                    <label class="block mb-1">Senha</label>
                    <input type="password" name="senha" class="w-full border rounded p-2" required>
                </div>
                <div>
                    <label class="block mb-1">Setor</label>
                    <select name="setor" class="w-full border rounded p-2">
                        <option value="logistica">logistica</option>
                        <option value="producao">producao</option>
                        <option value="admin">admin</option>
                    </select>
                </div>
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Salvar</button>
            </form>
        </div>

        <div class="bg-white p-6 shadow-lg rounded overflow-x-auto">
            <h2 class="text-xl font-bold mb-4">Lista de Usuários</h2>

            <table class="w-full border border-gray-300 text-sm">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="border p-2">ID</th>
                        <th class="border p-2">Nome</th>
                        <th class="border p-2">Login</th>
                        <th class="border p-2">Setor</th>
                        <th class="border p-2">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="border p-2 text-center"><?= $u['id_usuario'] ?></td>
                            <td class="border p-2"><?= htmlspecialchars($u['nome']) ?></td>
                            <td class="border p-2"><?= htmlspecialchars($u['login']) ?></td>
                            <td class="border p-2"><?= htmlspecialchars($u['setor']) ?></td>
                            <td class="border p-2 text-center space-x-2">
                                <button onclick="openEditModal(<?= $u['id_usuario'] ?>, '<?= addslashes($u['nome']) ?>', '<?= addslashes($u['login']) ?>', '<?= $u['setor'] ?>')" class="text-blue-600 hover:underline">Editar</button>
                                <a href="?delete=<?= $u['id_usuario'] ?>" onclick="return confirm('Deseja excluir?');" class="text-red-600 hover:underline">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white p-6 rounded shadow-lg w-96">
            <h2 class="text-xl font-bold mb-4">Editar Usuário</h2>
            <form method="post" class="space-y-4">
                <input type="hidden" name="acao" value="edit">
                <input type="hidden" name="id_usuario" id="editId">
                <div>
                    <label class="block mb-1">Nome</label>
                    <input type="text" name="nome" id="editNome" class="w-full border rounded p-2" required>
                </div>
                <div>
                    <label class="block mb-1">Login</label>
                    <input type="text" name="login" id="editLogin" class="w-full border rounded p-2" required>
                </div>
                <div>
                    <label class="block mb-1">Senha (deixe em branco para não alterar)</label>
                    <input type="password" name="senha" class="w-full border rounded p-2">
                </div>
                <div>
                    <label class="block mb-1">Setor</label>
                    <input type="text" name="setor" id="editSetor" class="w-full border rounded p-2" required>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancelar</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, nome, login, setor) {
            document.getElementById('editId').value = id;
            document.getElementById('editNome').value = nome;
            document.getElementById('editLogin').value = login;
            document.getElementById('editSetor').value = setor;
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editModal').classList.add('flex');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('editModal').classList.remove('flex');
        }
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });
    </script>

</body>

</html>