<?php
include '../conexao.php';

header('Content-Type: application/json');

$sql = "
SELECT 
    m.id,
    m.nome,
    AVG(t.valor_db) AS media_db,
    SUM(CASE WHEN t.status_ligado = 1 THEN 1 ELSE 0 END) AS vezes_ligada,
    
    CASE
        WHEN AVG(t.valor_db) >= m.limite_db_critico THEN 'CRITICO'
        WHEN AVG(t.valor_db) >= m.limite_db_manutencao THEN 'MANUTENCAO'
        ELSE 'NORMAL'
    END AS status_alerta

FROM maquinas m
JOIN tabela_bruta t ON m.id = t.id_maquina
GROUP BY m.id, m.nome, m.limite_db_manutencao, m.limite_db_critico
";

$stmt = $pdo->query($sql);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
