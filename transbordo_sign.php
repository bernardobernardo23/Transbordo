<?php
require 'config.php';
require 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id   = $_POST['id'] ?? 0;
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'receber') {
        // só recebe se status = emitido
        $stmt = $pdo->prepare("SELECT * FROM transbordos WHERE id_transbordo = ?");
        $stmt->execute([$id]);
        $t = $stmt->fetch();

        if ($t && $t['status'] === 'emitido' && $t['id_usuario_emissor'] != $_SESSION['user']['id']) {
            $stmt = $pdo->prepare("UPDATE transbordos 
                SET status = 'recebido', id_usuario_receptor = ? 
                WHERE id_transbordo = ?");
            $stmt->execute([$_SESSION['user']['id'], $id]);
        }
    }

    header("Location: transbordo_view.php?id=" . $id);
    exit;
}
