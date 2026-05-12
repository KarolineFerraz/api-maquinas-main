<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include __DIR__ . '/../conexao.php';
$stmt = $pdo->query("SELECT * FROM modelos");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

