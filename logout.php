<?php
session_start();
require 'db/config.php'; // Certifique-se de ajustar o caminho para o arquivo correto

// Conecta ao banco de dados
$conn = connectDatabase();

// Registra o log de logout
if (isset($_SESSION['user_id'])) {
    logAction($conn, $_SESSION['user_id'], 'Logout realizado', 'Usuário saiu do sistema.');
}

// Destroi a sessão e redireciona
session_destroy();
header('Location: login.php');
exit;
