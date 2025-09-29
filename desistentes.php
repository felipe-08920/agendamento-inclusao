<?php
include './db/config.php';
session_start();

$conn = connectDatabase();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM agendamento WHERE status = 'Desistente' ORDER BY profissional, lista_espera");
$stmt->execute();
$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alunos Desistentes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/styles/header.css">
    <link rel="stylesheet" href="public/styles/footer.css">
    <link rel="stylesheet" href="public/styles/style.css">
<link rel="icon" type="image/png" href="public/images/brasao.png">
</head>
<body>
<?php include 'components/header.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4">Alunos Desistentes</h2>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Profissional</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($alunos as $row): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nome_aluno']) ?></td>
                <td><?= htmlspecialchars($row['profissional']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td>
                    <a href="editar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">✏️ Editar</a>
                    <a href="ficha.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white">📄 Ficha</a>
                    <a href="gerar_pdf.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-dark">🖨️ PDF</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'components/footer.php'; ?>
</body>
</html>
