<?php

// 1. CORS HEADERS - Crucial for the React frontend to read the data!
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight requests from the browser
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 2. DATABASE CONNECTION
include __DIR__ . '/../conexao.php';

try {
    // Definindo o ID da máquina que será exibida no dashboard principal
    $id_maquina = 1; 

    // ---------------------------------------------------------
    // QUERY 1: ESTADO DA MÁQUINA (Visão Geral + Limites)
    // Fazemos um JOIN entre a view e a tabela de máquinas
    // para pegar tanto as estatísticas quanto os limites definidos.
    // ---------------------------------------------------------
    $sql_maquina = "
        SELECT 
            v.id, 
            v.nome, 
            v.media_db, 
            v.vezes_ligada, 
            v.status_alerta, 
            m.limite_manutencao AS limite_db_manutencao, 
            m.limite_critico AS limite_db_critico
        FROM vw_dashboard v
        JOIN maquinas m ON v.id = m.id
        WHERE v.id = :id
        LIMIT 1
    ";
    
    $stmt_maquina = $pdo->prepare($sql_maquina);
    $stmt_maquina->bindParam(':id', $id_maquina, PDO::PARAM_INT);
    $stmt_maquina->execute();
    $estado_maquina = $stmt_maquina->fetch(PDO::FETCH_ASSOC);

    // Se não encontrar a máquina, retorna erro
    if (!$estado_maquina) {
        http_response_code(404);
        echo json_encode(["error" => "Máquina não encontrada"]);
        exit;
    }

    // ---------------------------------------------------------
    // QUERY 2: TABELA BRUTA (Últimas leituras para o Gráfico)
    // Pega os 15 registros mais recentes de ruído.
    // ---------------------------------------------------------
    $sql_logs = "
        SELECT 
            id, 
            data_hora, 
            valor_db, 
            status_ligado 
        FROM tabela_bruta 
        WHERE id_maquina = :id 
        ORDER BY data_hora DESC 
        LIMIT 15
    ";
    
    $stmt_logs = $pdo->prepare($sql_logs);
    $stmt_logs->bindParam(':id', $id_maquina, PDO::PARAM_INT);
    $stmt_logs->execute();
    $tabela_bruta = $stmt_logs->fetchAll(PDO::FETCH_ASSOC);

    // ---------------------------------------------------------
    // MONTAGEM DO JSON CONTRACT
    // ---------------------------------------------------------
    $response = [
        "estado_maquina" => $estado_maquina,
        "tabela_bruta" => $tabela_bruta
    ];

    // Dispara o JSON para o React
    echo json_encode($response);

} catch (PDOException $e) {
    // Retorna um erro JSON legível em caso de falha no banco
    http_response_code(500);
    echo json_encode(["error" => "Erro no banco de dados: " . $e->getMessage()]);
}
?>