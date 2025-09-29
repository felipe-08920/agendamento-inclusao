<?php
include './db/config.php';
session_start();

$conn = connectDatabase();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// FILTROS
$profissional_filter = $_GET['profissional'] ?? '';
$aluno_filter = $_GET['aluno'] ?? '';
$profissionais = $conn->query("SELECT DISTINCT profissional FROM agendamento")->fetchAll(PDO::FETCH_COLUMN);

$sql = "SELECT * FROM agendamento WHERE status = 'Em espera'";
$conditions = [];

if ($profissional_filter && $profissional_filter !== 'todos') {
    $conditions[] = "profissional = :profissional";
}

if ($aluno_filter) {
    $conditions[] = "nome_aluno LIKE :aluno";
}

if ($conditions) {
    $sql .= " AND " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY profissional, lista_espera";
$stmt = $conn->prepare($sql);

if ($profissional_filter && $profissional_filter !== 'todos') {
    $stmt->bindParam(':profissional', $profissional_filter, PDO::PARAM_STR);
}

if ($aluno_filter) {
    $aluno_filter_param = "%" . $aluno_filter . "%";
    $stmt->bindParam(':aluno', $aluno_filter_param, PDO::PARAM_STR);
}

$stmt->execute();
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ATUALIZA STATUS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $agendamento_id = $_POST['agendamento_id'];
    $novo_status = $_POST['status'];

    try {
        $stmt = $conn->prepare("UPDATE agendamento SET status = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $novo_status,
            ':id' => $agendamento_id
        ]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('Erro ao atualizar status: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// CONTAGEM
$totalAlunos = $conn->query("SELECT COUNT(*) FROM agendamento WHERE status = 'Em espera'")->fetchColumn();
$contagens = $conn->query("SELECT profissional, COUNT(*) as total FROM agendamento WHERE status = 'Em espera' GROUP BY profissional")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Consultar Agendamentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/styles/header.css">
    <link rel="stylesheet" href="public/styles/footer.css">
    <link rel="stylesheet" href="public/styles/consulta.css">
<link rel="icon" type="image/png" href="public/images/brasao.png">
</head>

<body>
    <?php include 'components/header.php'; ?>
    <div class="banner"></div>
    <br>

    <!-- FILTROS -->
    <form method="GET" class="container mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="profissional">Filtrar por Profissional:</label>
                <select name="profissional" id="profissional" class="form-select">
                    <option value="todos" <?= $profissional_filter === 'todos' ? 'selected' : '' ?>>Todos</option>
                    <?php foreach ($profissionais as $profissional): ?>
                        <option value="<?= htmlspecialchars($profissional) ?>"
                            <?= $profissional_filter === $profissional ? 'selected' : '' ?>>
                            <?= htmlspecialchars($profissional) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="aluno">Filtrar por Nome do Aluno:</label>
                <input type="text" name="aluno" id="aluno" value="<?= htmlspecialchars($aluno_filter) ?>"
                    class="form-control">
            </div>

            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-primary w-100">Filtrar</button>
            </div>
        </div>
    </form>

    <!-- CONTAGEM -->
    <div class="container mb-3">
        <div class="alert alert-info">
            <strong>Total de alunos cadastrados:</strong> <?= $totalAlunos ?>
        </div>
        <div>
            <strong>Contagem por profissional:</strong>
            <ul>
                <?php foreach ($contagens as $p): ?>
                    <li><?= htmlspecialchars($p['profissional']) ?>: <?= $p['total'] ?> aluno(s)</li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- TABELA -->
  <div class="table-responsive table-wrapper container mt-3">
        <table class="table table-bordered table-striped table-hover">
            <thead class="" style="background-color: #18264F;">
                <tr>
                    
                    <th>CID</th>
                    <th>Profissional</th>
                    <th>Aluno</th>
                    <th>Escola</th>
                    <th>Turma</th>
                    <th>Turno</th>
                    <th>Data Nasc.</th>
                    <th>Idade</th>
                    <th>Responsável</th>
                    <th>Endereço</th>
                    <th>Contato</th>
                    <th>Atendimento</th>
                    <th>Solicitação</th>
                    <th>Lista Espera</th>
                    <th>Status</th>
                    <th>Atualizar Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($agendamentos): ?>
                    <?php foreach ($agendamentos as $row): ?>
                        <tr>
                           
                            <td><?= htmlspecialchars($row['cid']) ?></td>
                            <td><?= htmlspecialchars($row['profissional']) ?></td>
                            <td><?= htmlspecialchars($row['nome_aluno']) ?></td>
                            <td><?= htmlspecialchars($row['escola']) ?></td>
                            <td><?= htmlspecialchars($row['turma']) ?></td>
                            <td><?= htmlspecialchars($row['turno']) ?></td>
                            <td><?= htmlspecialchars($row['data_nascimento']) ?></td>
                            <td><?= htmlspecialchars($row['idade']) ?></td>
                            <td><?= htmlspecialchars($row['nome_responsavel']) ?></td>
                            <td><?= htmlspecialchars($row['endereco']) ?></td>
                            <td><?= htmlspecialchars($row['contato']) ?></td>
                            <td><?= htmlspecialchars($row['responsavel_atendimento']) ?></td>
                            <td><?= htmlspecialchars($row['data_solicitacao']) ?></td>
                            <td><?= htmlspecialchars($row['lista_espera']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="agendamento_id" value="<?= $row['id'] ?>">
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="Pendente" <?= $row['status'] === 'Pendente' ? 'selected' : '' ?>>Pendente
                                        </option>
                                        <option value="Em Atendimento"
                                            <?= $row['status'] === 'Em Atendimento' ? 'selected' : '' ?>>Em Atendimento</option>
                                        <option value="Alta" <?= $row['status'] === 'Alta' ? 'selected' : '' ?>>Alta</option>
                                        <option value="Desistente" <?= $row['status'] === 'Desistente' ? 'selected' : '' ?>>
                                            Desistente</option>
                                        <option value="Não localizado"
                                            <?= $row['status'] === 'Não localizado' ? 'selected' : '' ?>>Não localizado</option>
                                    </select>
                                    <button type="submit" name="update_status"
                                        class="btn btn-sm btn-outline-success mt-1">Salvar</button>
                                </form>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <a href="editar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">✏️ Editar</a>
                                    <a href="ficha.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white">📄 Ficha</a>
                                 <a href="gerar_pdf.php?nomeAluno=<?= urlencode($row['nome_aluno']) ?>
&profissional=<?= urlencode($row['profissional']) ?>
&posicao=<?= urlencode($row['lista_espera']) ?>
&dataSolicitacao=<?= urlencode($row['data_solicitacao']) ?>"
class="btn btn-sm btn-dark">🖨️ PDF</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="18">Nenhum agendamento encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php include 'components/footer.php'; ?>
</body>

</html>