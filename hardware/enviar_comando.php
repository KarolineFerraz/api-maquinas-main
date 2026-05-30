<?php

// 1. CORS Headers: Crucial for allowing your GitHub Pages React app to send POST requests 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, x-api-key");
header("Content-Type: application/json");

// Handle preflight requests from the browser
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// 2. Include the database connection 
// Adjust this path if conexao.php is located somewhere else in the directory structure
include __DIR__ . '/../conexao.php';

// 3. Optional: API Key Security (Mirroring inserir_ruido.php)
$apiKeyRecebida = $_SERVER['HTTP_X_API_KEY'] ?? '';
$apiKeyCorreta = getenv("API_KEY");

// If you want to secure this endpoint, uncomment the lines below:
/*
if (!$apiKeyCorreta || $apiKeyRecebida !== $apiKeyCorreta) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "API key inválida ou ausente"]);
    exit;
}
*/

// 4. Capture and decode the JSON payload sent by Control.jsx
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        "success" => false, 
        "message" => "JSON inválido ou vazio"
    ]);
    exit;
}

// 5. Extract variables and validate required fields
$device_token = trim($data['device_token'] ?? '');
$tipo = trim($data['tipo'] ?? '');
$novo_id_maquina = isset($data['novo_id_maquina']) && $data['novo_id_maquina'] !== '' 
    ? (int) $data['novo_id_maquina'] 
    : null;

if ($device_token === '' || $tipo === '') {
    http_response_code(400);
    echo json_encode([
        "success" => false, 
        "message" => "Campos obrigatórios: device_token, tipo"
    ]);
    exit;
}

// 6. Insert the command into the database
try {
    // We insert the command with the default status of 'PENDENTE' 
    $sql = "INSERT INTO comandos_hardware 
            (device_token, tipo, novo_id_maquina, status, criado_em)
            VALUES (?, ?, ?, 'PENDENTE', NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $device_token,
        $tipo,
        $novo_id_maquina
    ]);

    $novo_comando_id = $pdo->lastInsertId();

    // Return success to the React frontend
    echo json_encode([
        "success" => true,
        "message" => "Comando adicionado à fila com sucesso",
        "comando_id" => $novo_comando_id
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Erro ao inserir comando: " . $e->getMessage()
    ]);
}