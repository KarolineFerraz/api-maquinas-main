<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include __DIR__ . '/../conexao.php';

for ($i = 1; $i <= 400; $i++) {

    $id_maquina = rand(1, 2);

    $valor_db = rand(60, 110);

    $status_ligado = rand(0, 1);

    $dias_atras = rand(0, 30);

    $data_hora = date(
        'Y-m-d H:i:s',
        strtotime("-$dias_atras days +" . rand(0,23) . " hours")
    );

    $sql = "
    INSERT INTO tabela_bruta
    (id_maquina, valor_db, status_ligado, data_hora)
    VALUES (?, ?, ?, ?)
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
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
