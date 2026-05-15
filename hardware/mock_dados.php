<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include __DIR__ . '/../conexao.php';

for ($i = 1; $i <= 400; $i++) {

    $id_maquina = rand(1, 2);
    $valor_db = rand(60, 110);
    $status_ligado = rand(0, 1);

    $dias_atras = rand(0, 30);
    $horas = rand(0, 23);
    $minutos = rand(0, 59);

    $data_hora = date(
        'Y-m-d H:i:s',
        strtotime("-$dias_atras days +$horas hours +$minutos minutes")
    );

    $sqlId = "SELECT COALESCE(MAX(id), 0) + 1 AS proximo_id FROM tabela_bruta";
    $stmtId = $pdo->query($sqlId);
    $novoId = $stmtId->fetch(PDO::FETCH_ASSOC)['proximo_id'];

    $sql = "INSERT INTO tabela_bruta (id, id_maquina, valor_db, status_ligado, data_hora)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $novoId,
        $id_maquina,
        $valor_db,
        $status_ligado,
        $data_hora
    ]);
}

echo json_encode([
    "success" => true,
    "message" => "400 registros mockados inseridos"
]);
