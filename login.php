<?php
include 'db/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['senha'] ?? '');

    if ($email && $password) {
        try {
            $conn = connectDatabase();

            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_tipo'] = $user['tipo'];

                logAction($conn, $user['id'], 'Login realizado', 'Usuário logado com sucesso.');

                echo "<script>window.location.href = 'home.php';</script>";
                exit;
            } else {
                echo "<script>alert('Email ou senha inválidos!');</script>";
            }
        } catch (PDOException $e) {
            error_log("Erro no banco: " . $e->getMessage());
            echo "<script>alert('Erro ao processar login. Tente mais tarde.');</script>";
        }
    } else {
        echo "<script>alert('Preencha todos os campos!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/styles/login.css">
    <link rel="icon" type="image/png" href="public/images/brasao.png">
</head>
<body>
<main>
  <div class="banner">
    <div class="imagens_banner">
        <img src="public/images/educacao_branca.png" class="image_login">
        <img src="public/images/prefeitura.png" class="image_login">
    </div>
</div>


    <div class="content_login">
        <form method="POST" action="login.php">
            <div class="text_banner">
                <h1>Agendamento Inclusão</h1>
                <h3>Secretaria Municipal de Educação</h3>
                <hr>
                <h3>Login</h3>
            </div>

            <div class="input">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Digite seu Email" required>

                <label for="senha" class="form-label">Senha:</label>
                <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite sua Senha" required>
            </div>

            <div class="button">
                <button class="botao" type="submit">Entrar</button>
            </div>
        </form>
    </div>
</main>

<?php include 'components/footer.php'; ?>
</body>
</html>
