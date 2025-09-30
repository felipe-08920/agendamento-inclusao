<?php
function connectDatabase()
{
    $server = 'localhost';
    $dbname = 'agendamento-inclusao';
    $user = 'root';
    $password = '';

    try {
        return new PDO(
            "mysql:host=$server;dbname=$dbname;charset=utf8mb4",
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $e) {
        die("Erro ao conectar: " . $e->getMessage());
    }
}
function logAction($conn, $userId, $action, $details = null)
{
    if (empty($userId) || empty($action)) {
        error_log("Parâmetros inválidos ao registrar log: userId ou action vazio.");
        return;
    }

    try {
        $stmt = $conn->prepare("
            INSERT INTO logs (user_id, action, details)
            VALUES (:user_id, :action, :details)
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':action' => $action,
            ':details' => $details
        ]);
    } catch (PDOException $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }
}
?>
