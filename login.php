<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha_hash'])) {
        $_SESSION['user'] = [
            'id' => $user['id_usuario'],
            'nome' => $user['nome'],
            'setor' => $user['setor']
        ];
        header("Location: index.php");
        exit;
    } else {
        $erro = "Login ou senha inválidos";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Login - Transbordo</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-sm bg-white rounded-xl shadow p-6">
        <h2 class="text-2xl font-bold text-center mb-4">Login - Transbordo</h2>

        <?php if (isset($erro)): ?>
            <div class="bg-red-100 text-red-700 text-sm p-2 rounded mb-3">
                <?= $erro ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-3">
            <div>
                <label class="block text-sm font-medium">Usuário</label>
                <input type="text" name="login" required
                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium">Senha</label>
                <input type="password" name="senha" required
                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                Entrar
            </button>
        </form>
    </div>
</body>
</html>
