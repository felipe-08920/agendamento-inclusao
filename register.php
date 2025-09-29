<?php
require 'db/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pdo = connectDatabase();

    $name = $_POST['nome'];
    $email = $_POST['email'];
    $cpf = $_POST['cpf'];
    $password = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $telefone = $_POST['telefone'] ?? null;
    $tipo = $_POST['tipo'] ?? null;

    if (empty($telefone) || empty($tipo)) {
        echo "<script>alert('Todos os campos são obrigatórios!');</script>";
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email OR cpf = :cpf");
    $stmt->execute([':email' => $email, ':cpf' => $cpf]);

    if ($stmt->rowCount() > 0) {
        echo "<script>alert('Email ou CPF já cadastrados!');</script>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, cpf, password, telefone, tipo) 
            VALUES (:name, :email, :cpf, :password, :telefone, :tipo)");
        try {
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':cpf' => $cpf,
                ':password' => $password,
                ':telefone' => $telefone,
                ':tipo' => $tipo
            ]);
            echo "<script>
            alert('Usuário cadastrado com sucesso!');
            window.location.href = 'login.php';
        </script>";
        } catch (PDOException $e) {
            echo "<script>alert('Erro ao cadastrar: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="public/styles/header.css">
    <link rel="stylesheet" href="public/styles/footer.css">
    <link rel="stylesheet" href="public/styles/register.css">
<link rel="icon" type="image/png" href="public/images/brasao.png">
</head>

<body>
    <header class="navbar navbar-expand-sm text-light shadow ">
        <div class="container__imgs colum align-items-start">
            <a href="#" class="navbar-brand">
                <img src="public/images/logo_semed.png" alt="Logo Semed" class="img-fluid logo_semed">
            </a>
        </div>

    </header>
    <div class="banner">

    </div>
    <main>
        <div class="title">
            <h3>Cadastro</h3>
            <span>
        </div>
        <form class="row g-3" method="POST">
            <div class="col-md-6">
                <label for="nome" class="form-label">Nome:</label>
                <input type="text" class="form-control" id="nome" placeholder="Digite seu Nome" required name="nome">
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" placeholder="Digite seu Email" required
                    name="email">
            </div>
            <div class="col-md-6">
                <label for="telefone" class="form-label">Telefone:</label>
                <input type="text" class="form-control" id="telefone" placeholder="Digite seu Telefone" required
                    name="telefone">
            </div>
            <div class="col-md-6">
                <label for="cpf" class="form-label">CPF:</label>
                <input type="text" class="form-control" id="cpf" placeholder="Digite seu CPF" required name="cpf">
            </div>
            <div class="col-md-6">
                <label for="tipo" class="form-label">Tipo:</label>
                <select class="form-select" id="tipo" name="tipo" required>
                    <option value="Semed">Semed</option>
                    <option value="Núcleo">Núcleo</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="senha" class="form-label">Senha:</label>
                <input type="password" class="form-control" id="senha" placeholder="Digite sua Senha" required
                    name="senha">
            </div>


            <div class="col-12">
                <button type="submit" class="btn btn-primary">Registrar</button>
            </div>
        </form>

    </main>

    <?php include 'components/footer.php' ?>

</body>

</html>