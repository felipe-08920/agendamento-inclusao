<?php
include './db/config.php';
session_start();

$conn = connectDatabase();

if (!isset($_GET['id'])) {
  echo "ID não especificado.";
  exit;
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM agendamento WHERE id = :id");
$stmt->execute([':id' => $id]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
  echo "Aluno não encontrado.";
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $update = $conn->prepare("
    UPDATE agendamento SET
      cid = :cid,
      profissional = :profissional,
      nome_aluno = :nome_aluno,
      escola = :escola,
      turma = :turma,
      turno = :turno,
      data_nascimento = :data_nascimento,
      idade = :idade,
      nome_responsavel = :nome_responsavel,
      endereco = :endereco,
      contato = :contato,
      responsavel_atendimento = :responsavel_atendimento,
      status = :status
    WHERE id = :id
  ");

  $update->execute([
    ':cid' => $_POST['cid'],
    ':profissional' => $_POST['profissional'],
    ':nome_aluno' => $_POST['nome_aluno'],
    ':escola' => $_POST['escola'],
    ':turma' => $_POST['turma'],
    ':turno' => $_POST['turno'],
    ':data_nascimento' => $_POST['data_nascimento'],
    ':idade' => $_POST['idade'],
    ':nome_responsavel' => $_POST['nome_responsavel'],
    ':endereco' => $_POST['endereco'],
    ':contato' => $_POST['contato'],
    ':responsavel_atendimento' => $_POST['responsavel_atendimento'],
    ':status' => $_POST['status'],
    ':id' => $id
  ]);

  echo "<script>alert('Aluno atualizado com sucesso');window.location.href='consulta.php';</script>";
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Editar Aluno</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="icon" type="image/png" href="public/images/brasao.png">
</head>
<body class="container mt-4">
  <h3>Editar Dados do Aluno</h3>
  <form method="POST">
    <?php foreach ($aluno as $campo => $valor): ?>
      <?php if ($campo != 'id' && $campo != 'lista_espera' && $campo != 'data_solicitacao'): ?>
        <div class="mb-3">
          <label class="form-label"><?= ucfirst(str_replace("_", " ", $campo)) ?></label>
          <input 
            type="<?= ($campo == 'data_nascimento') ? 'date' : 'text' ?>" 
            name="<?= $campo ?>" 
            class="form-control" 
            value="<?= htmlspecialchars($valor) ?>" 
            <?= ($campo == 'responsavel_atendimento') ? 'readonly' : '' ?>
            required>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-success">Salvar</button>
    <a href="consulta.php" class="btn btn-secondary">Cancelar</a>
  </form>
</body>
</html>
