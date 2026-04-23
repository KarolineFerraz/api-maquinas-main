<?php
include '../conexao.php';

header('Content-Type: application/json');

$stmt = $pdo->query("SELECT * FROM modelos");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

