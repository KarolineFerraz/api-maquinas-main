<?php

$host = getenv("DB_HOST");
$port = getenv("DB_PORT");
$db   = getenv("DB_NAME");
$user = getenv("DB_USER");
$pass = getenv("DB_PASS");

if (!$host || !$port || !$db || !$user || !$pass) {
    echo json_encode([
        "success" => false,
        "message" => "Variáveis de ambiente do banco não configuradas."
    ]);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erro na conexão com o banco."
    ]);
    exit;
}
