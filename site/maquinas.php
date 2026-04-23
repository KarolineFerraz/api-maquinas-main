<?php
include '../conexao.php';

header('Content-Type: application/json');

$sql = "SELECT * FROM maquinas";
$stmt = $pdo->query($sql);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
