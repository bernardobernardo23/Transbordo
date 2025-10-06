<?php
require 'config.php';

header('Content-Type: application/json');

$codigo = $_GET['codigo'] ?? '';

if ($codigo !== '') {
    $stmt = $pdo->prepare("SELECT descricao FROM produtos WHERE codigo = ?");
    $stmt->execute([$codigo]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($prod) {
        echo json_encode(['descricao' => $prod['descricao']]);
    } else {
        echo json_encode(['descricao' => null]);
    }
} else {
    echo json_encode(['descricao' => null]);
}
