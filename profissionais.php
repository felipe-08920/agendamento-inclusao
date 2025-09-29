<?php
include './db/config.php';
session_start();

$conn = connectDatabase();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Inserção de novo profissional
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'], $_POST['especialidade'])) {
//     $nome = trim($_POST['nome']);
//     $especialidade = trim($_POST['especialidade']);

//     if ($nome && $especialidade) {
//         $stmt = $conn->prepare("INSERT INTO profissionais (nome, especialidade) VALUES (:nome, :especialidade)");
//         $stmt->execute([':nome' => $nome, ':especialidade' => $especialidade]);
//         header("Location: profissionais.php");
//         exit;
//     }
// }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'], $_POST['especialidade'], $_POST['horario'])) {
    $nome = trim($_POST['nome']);
    $especialidade = trim($_POST['especialidade']);
    $horario = trim($_POST['horario']);

    if ($nome && $especialidade && $horario) {
        $stmt = $conn->prepare("INSERT INTO profissionais (nome, especialidade, horario) 
                                VALUES (:nome, :especialidade, :horario)");
        $stmt->execute([
            ':nome' => $nome,
            ':especialidade' => $especialidade,
            ':horario' => $horario
        ]);

        header("Location: profissionais.php");
        exit;
    }
}

// Exclusão de profissional
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM profissionais WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header("Location: profissionais.php");
    exit;
}

$profissionais = $conn->query("SELECT * FROM profissionais ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Lista de especialidades
$especialidades = [
    'Psicólogo(a)',
    'Fonoaudiólogo(a)',
    'Psicopedagogo(a)',
    'Terapeuta Ocupacional',
    'Assistente Social',
    'Outro'
];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastro de Profissionais</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/styles/header.css">
    <link rel="stylesheet" href="public/styles/footer.css">
    <link rel="stylesheet" href="public/styles/style.css">
<link rel="icon" type="image/png" href="public/images/brasao.png">
</head>

<body>

    <?php include 'components/header.php'; ?>

    <div class="container mt-5">
        <h2 class="mb-4">Cadastrar Novo Profissional</h2>
        <form method="POST" class="row g-3">
            <div class="col-md-4">
                <label for="nome" class="form-label">Nome do Profissional</label>
                <input type="text" name="nome" id="nome" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label for="horario" class="form-label">Horários Disponíveis</label>
                <select name="horario" id="horario" class="form-select">
                    <option value="">Selecione um Horário</option>
                    <option value="08:00 às 11:30">08:00 às 11:30</option>
                    <option value="13:00 às 17:00">13:00 às 17:00</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="especialidade" class="form-label">Especialidade</label>
                <select name="especialidade" id="especialidade" class="form-select" required>
                    <option value="">Selecione uma Especialidade</option>
                    <?php foreach ($especialidades as $esp): ?>
                    <option value="<?= htmlspecialchars($esp) ?>"><?= htmlspecialchars($esp) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Cadastrar</button>
            </div>
        </form>

        <hr class="my-5">

        <h3>Profissionais Cadastrados</h3>
        <table class="table table-bordered table-striped mt-3">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Especialidade</th>
                    <th>Horários Disponíveis</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($profissionais as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['nome']) ?></td>
                    <td><?= htmlspecialchars($p['especialidade']) ?></td>
                    <td><?= htmlspecialchars($p['horario']) ?></td>
                    <td class="d-flex gap-2">
                        <a href="editar-profissional.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">✏️
                            Editar</a>
                        <a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm"
                            onclick="return confirm('Deseja excluir este profissional?');">🗑️ Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>