<?php
include '../conexao.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$sqlId = "SELECT COALESCE(MAX(id), 0) + 1 AS proximo_id FROM tabela_bruta";
$stmtId = $pdo->query($sqlId);
$novoId = $stmtId->fetch(PDO::FETCH_ASSOC)['proximo_id'];

$sql = "INSERT INTO tabela_bruta (id, id_maquina, valor_db, status_ligado)
        VALUES (?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $novoId,
    $data['id_maquina'],
    $data['valor_db'],
    $data['status_ligado']
]);

echo json_encode(["status" => "ok", "id" => $novoId]);