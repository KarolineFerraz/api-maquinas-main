<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, x-api-key");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include __DIR__ . '/../conexao.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        "success" => false,
        "message" => "JSON inválido ou vazio"
    ]);
    exit;
}

if (!isset($data['id_maquina'], $data['valor_db'], $data['status_ligado'])) {
    echo json_encode([
        "success" => false,
        "message" => "Campos obrigatórios: id_maquina, valor_db, status_ligado"
    ]);
    exit;
}

$id_maquina = (int) $data['id_maquina'];
$valor_db = (float) $data['valor_db'];
$status_ligado = (int) $data['status_ligado'];

$sqlId = "SELECT COALESCE(MAX(id), 0) + 1 AS proximo_id FROM tabela_bruta";
$stmtId = $pdo->query($sqlId);
$novoId = $stmtId->fetch(PDO::FETCH_ASSOC)['proximo_id'];

$sql = "INSERT INTO tabela_bruta (id, id_maquina, valor_db, status_ligado, data_hora)
        VALUES (?, ?, ?, ?, NOW())";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $novoId,
    $id_maquina,
    $valor_db,
    $status_ligado
]);

echo json_encode([
    "success" => true,
    "message" => "Dados inseridos com sucesso",
    "id" => $novoId
]);
