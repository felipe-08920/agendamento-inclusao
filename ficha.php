<?php
include './db/config.php';
session_start();

$conn = connectDatabase();

if (!isset($_GET['id'])) {
    echo "ID do aluno não informado.";
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
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Ficha do Aluno</title>
<link rel="icon" type="image/png" href="public/images/brasao.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .ficha {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            border: 1px solid #ccc;
            background-color: #fdfdfd;
        }
        .titulo {
            text-align: center;
            margin-bottom: 25px;
        }
        .campo {
            margin-bottom: 10px;
        }
        .campo strong {
            display: inline-block;
            width: 200px;
        }
        @media print {
            .btn-imprimir {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="ficha">
        <div class="titulo">
            <h3>Ficha de Atendimento do Aluno</h3>
        </div>

        <?php foreach ($aluno as $campo => $valor): ?>
            <?php if ($campo != 'id'): ?>
                <div class="campo">
                    <strong><?= ucwords(str_replace('_', ' ', $campo)) ?>:</strong>
                    <?= htmlspecialchars($valor) ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <br>
        <div class="text-center">
            <button class="btn btn-primary btn-imprimir" onclick="window.print()">Imprimir</button>
            <a href="consulta.php" class="btn btn-secondary btn-imprimir">Voltar</a>
        </div>
    </div>
</body>
</html>
