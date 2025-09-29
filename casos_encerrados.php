<?php
include './db/config.php';
session_start();

$conn = connectDatabase();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

// FILTROS
$status_filter = $_GET['status'] ?? 'todos';
$valid_statuses = ['Alta', 'Em Atendimento', 'Desistente'];

if (!in_array($status_filter, $valid_statuses)) {
  $status_filter = 'todos';
}

$sql = "SELECT * FROM agendamento WHERE status IN ('Alta', 'Em Atendimento', 'Desistente')";
if ($status_filter !== 'todos') {
  $sql .= " AND status = :status";
}
$sql .= " ORDER BY profissional, lista_espera";

$stmt = $conn->prepare($sql);
if ($status_filter !== 'todos') {
  $stmt->bindParam(':status', $status_filter);
}
$stmt->execute();
$casos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <title>Casos Encerrados</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="public/styles/header.css">
  <link rel="stylesheet" href="public/styles/footer.css">
<link rel="icon" type="image/png" href="public/images/brasao.png">
</head>

<body>
  <?php include 'components/header.php'; ?>

  <div class="container mt-5">
    <h2 class="mb-4">Alunos em Atendimento / Alta / Desistentes</h2>

    <!-- Abas de filtro -->
    <ul class="nav nav-tabs mb-4">
      <li class="nav-item">
        <a class="nav-link <?= $status_filter === 'todos' ? 'active' : '' ?>"
          href="casos_encerrados.php">Todos</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $status_filter === 'Em Atendimento' ? 'active' : '' ?>"
          href="casos_encerrados.php?status=Em Atendimento">Em Atendimento</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $status_filter === 'Alta' ? 'active' : '' ?>"
          href="casos_encerrados.php?status=Alta">Alta</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $status_filter === 'Desistente' ? 'active' : '' ?>"
          href="casos_encerrados.php?status=Desistente">Desistentes</a>
      </li>
    </ul>

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
        <?php if (count($casos) > 0): ?>
          <?php foreach ($casos as $row): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['nome_aluno']) ?></td>
              <td><?= htmlspecialchars($row['profissional']) ?></td>
              <td><?= htmlspecialchars($row['status']) ?></td>
              <td>
                <a href="editar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">✏️ Editar</a>
                <a href="ficha.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white">📄 Ficha</a>

              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5">Nenhum aluno encontrado.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php include 'components/footer.php'; ?>
</body>

</html>