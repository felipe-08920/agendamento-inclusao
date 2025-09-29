<?php
include './db/config.php';
session_start();

$conn = connectDatabase();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Lista de especialidades
$especialidades = [
    'Psicólogo(a)',
    'Fonoaudiólogo(a)',
    'Psicopedagogo(a)',
    'Terapeuta Ocupacional',
    'Assistente Social',
    'Outro'
];

// Lista de horários
$horarios = [
    '08:00 às 11:30',
    '13:00 às 17:00'
];

// Verifica se foi passado um ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='container mt-5 alert alert-danger'>ID inválido.</div>";
    exit;
}

$id = (int) $_GET['id'];

// Busca os dados do profissional
$stmt = $conn->prepare("SELECT * FROM profissionais WHERE id = :id");
$stmt->execute([':id' => $id]);
$profissional = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profissional) {
    echo "<div class='container mt-5 alert alert-warning'>Profissional não encontrado.</div>";
    exit;
}

// Atualização dos dados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'], $_POST['especialidade'], $_POST['horario'])) {
    $nome = trim($_POST['nome']);
    $especialidade = trim($_POST['especialidade']);
    $horario = trim($_POST['horario']);

    if ($nome && $especialidade && $horario) {
        $stmt = $conn->prepare("UPDATE profissionais 
                                SET nome = :nome, especialidade = :especialidade, horario = :horario 
                                WHERE id = :id");
        $stmt->execute([
            ':nome' => $nome,
            ':especialidade' => $especialidade,
            ':horario' => $horario,
            ':id' => $id
        ]);
        header("Location: profissionais.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Profissional</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/styles/header.css">
    <link rel="stylesheet" href="public/styles/footer.css">
    <link rel="icon" type="image/png" href="public/images/brasao.png">
</head>
<body>
<?php include 'components/header.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4">Editar Profissional</h2>
    <form method="POST" class="row g-3">
        <div class="col-md-4">
            <label for="nome" class="form-label">Nome do Profissional</label>
            <input type="text" name="nome" id="nome" class="form-control" 
                   value="<?= htmlspecialchars($profissional['nome']) ?>" required>
        </div>
        <div class="col-md-4">
            <label for="especialidade" class="form-label">Especialidade</label>
            <select name="especialidade" id="especialidade" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($especialidades as $esp): ?>
                    <option value="<?= htmlspecialchars($esp) ?>" 
                        <?= $esp === $profissional['especialidade'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($esp) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="horario" class="form-label">Horário Disponível</label>
            <select name="horario" id="horario" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($horarios as $h): ?>
                    <option value="<?= htmlspecialchars($h) ?>" 
                        <?= $h === $profissional['horario'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($h) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-success">Salvar Alterações</button>
            <a href="profissionais.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php include 'components/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
