<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, x-api-key");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

include __DIR__ . '/../conexao.php';

$apiKeyRecebida = $_SERVER['HTTP_X_API_KEY'] ?? '';
$apiKeyCorreta = getenv("API_KEY");

if (!$apiKeyCorreta || $apiKeyRecebida !== $apiKeyCorreta) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "API key inválida ou ausente"
    ]);

    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "JSON inválido ou vazio"
    ]);

    exit;
}

if (!isset($data['id_maquina'], $data['valor_db'], $data['status_ligado'])) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Campos obrigatórios: id_maquina, valor_db, status_ligado"
    ]);

    exit;
}

$id_maquina = (int) $data['id_maquina'];
$valor_db = (float) $data['valor_db'];
$status_ligado = (int) $data['status_ligado'];

$device_token = trim($data['device_token'] ?? 'TX_001');

if ($id_maquina <= 0) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "id_maquina inválido"
    ]);

    exit;
}

if ($status_ligado !== 0 && $status_ligado !== 1) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "status_ligado deve ser 0 ou 1"
    ]);

    exit;
}

if ($valor_db < 0 || $valor_db > 160) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "valor_db inválido"
    ]);

    exit;
}

try {
    $pdo->beginTransaction();

    $sqlId = "SELECT COALESCE(MAX(id), 0) + 1 AS proximo_id FROM tabela_bruta";
    $stmtId = $pdo->query($sqlId);
    $novoId = (int) $stmtId->fetch(PDO::FETCH_ASSOC)['proximo_id'];

    $sql = "INSERT INTO tabela_bruta
            (id, id_maquina, valor_db, status_ligado, data_hora)
            VALUES (?, ?, ?, ?, NOW())";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $novoId,
        $id_maquina,
        $valor_db,
        $status_ligado
    ]);

    $command = null;

    if ($device_token !== '') {
        $sqlComando = "SELECT id, device_token, tipo, novo_id_maquina
                       FROM comandos_hardware
                       WHERE device_token = ?
                         AND status = 'PENDENTE'
                       ORDER BY id ASC
                       LIMIT 1";

        $stmtComando = $pdo->prepare($sqlComando);
        $stmtComando->execute([$device_token]);

        $command = $stmtComando->fetch(PDO::FETCH_ASSOC);

        if ($command) {
            $sqlUpdate = "UPDATE comandos_hardware
                          SET status = 'ENVIADO',
                              enviado_em = NOW(),
                              mensagem = 'Comando enviado ao receptor'
                          WHERE id = ?";

            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([
                $command['id']
            ]);

            $command['id'] = (int) $command['id'];
            $command['novo_id_maquina'] = $command['novo_id_maquina'] !== null
                ? (int) $command['novo_id_maquina']
                : null;
        } else {
            $command = null;
        }
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Dados inseridos com sucesso",
        "id" => $novoId,
        "command" => $command
    ]);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Erro ao inserir dados"
    ]);
}