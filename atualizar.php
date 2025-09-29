<?php
include './db/config.php';
session_start();

$conn = connectDatabase();


if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
if ($_SESSION['user_tipo'] === 'Núcleo') {
  header('Location: acesso-negado.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $cid = $_POST['cid'] ?? null;
  $profissional = $_POST['profissional'] ?? null;
  $nomeAluno = $_POST['nomeAluno'] ?? null;
  $escola = $_POST['escola'] ?? null;
  $turma = $_POST['turma'] ?? null;
  $turno = $_POST['turno'] ?? null;
  $dataNascimento = $_POST['dataNascimento'] ?? null;
  $idade = $_POST['idade'] ?? null;
  $nomeResponsavel = $_POST['nomeResponsavel'] ?? null;
  $endereco = $_POST['endereco'] ?? null;
  $contato = $_POST['contato'] ?? null;
  $responsavelAtendimento = $_POST['responsavelAtendimento'] ?? null;
  $status = $_POST['status'] ?? null;
  $dataSolicitacao = $_POST['dataSolicitacao'] ?? null;

  try {
    // Inserindo o novo agendamento
    $stmt = $conn->prepare("
      INSERT INTO agendamento (cid, profissional, nome_aluno, escola, turma, turno, data_nascimento, idade, 
      nome_responsavel, endereco, contato, responsavel_atendimento, status, data_solicitacao, lista_espera) 
      VALUES (:cid, :profissional, :nomeAluno, :escola, :turma, :turno, :dataNascimento, :idade, 
      :nomeResponsavel, :endereco, :contato, :responsavelAtendimento, :status, :dataSolicitacao, 0)
    ");
    $stmt->execute([
      ':cid' => $cid,
      ':profissional' => $profissional,
      ':nomeAluno' => $nomeAluno,
      ':escola' => $escola,
      ':turma' => $turma,
      ':turno' => $turno,
      ':dataNascimento' => $dataNascimento,
      ':idade' => $idade,
      ':nomeResponsavel' => $nomeResponsavel,
      ':endereco' => $endereco,
      ':contato' => $contato,
      ':responsavelAtendimento' => $responsavelAtendimento,
      ':status' => $status,
      ':dataSolicitacao' => $dataSolicitacao,
    ]);

    // Atualizando a posição na lista de espera com base na data_solicitacao
    $stmtUpdateQueue = $conn->prepare("
UPDATE agendamento a
JOIN (
  SELECT id, ROW_NUMBER() OVER (PARTITION BY profissional ORDER BY data_solicitacao ASC) AS nova_posicao
  FROM agendamento
) b ON a.id = b.id
SET a.lista_espera = b.nova_posicao
WHERE a.profissional = :profissional;
");
    $stmtUpdateQueue->execute([':profissional' => $profissional]);


    logAction($conn, $_SESSION['user_id'], 'Novo agendamento criado', "Aluno: $nomeAluno, Profissional: $profissional");

    // Redirecionar para gerar o PDF com os dados atualizados
    $stmtGetPosition = $conn->prepare("
  SELECT lista_espera 
  FROM agendamento 
  WHERE profissional = :profissional AND nome_aluno = :nomeAluno 
  ORDER BY data_solicitacao ASC 
  LIMIT 1;
");
    $stmtGetPosition->execute([
      ':profissional' => $profissional,
      ':nomeAluno' => $nomeAluno,
    ]);
    $posicao = $stmtGetPosition->fetchColumn();

    // Inclua a posição na fila nos parâmetros
    $queryParams = http_build_query([
      'nomeAluno' => $nomeAluno,
      'profissional' => $profissional,
      'dataSolicitacao' => $dataSolicitacao,
      'posicao' => $posicao,
    ]);

    header("Location: gerar_pdf.php?$queryParams");
    exit;
  } catch (PDOException $e) {
    echo "<script>alert('Erro ao agendar: " . addslashes($e->getMessage()) . "');</script>";
  }
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulário de Consulta</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="public/styles/header.css">
  <link rel="stylesheet" href="public/styles/footer.css">
  <link rel="stylesheet" href="public/styles/style.css">
<link rel="icon" type="image/png" href="public/images/brasao.png">
  <script src="https://kit.fontawesome.com/8a5e7a4ec6.js" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"
    integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
</head>

<body>
  <?php include 'components/header.php'; ?>

  <main>
    <form class="row g-3" method="POST">
      <div class="title">
        <h3>Solicitação / Encaminhamento</h3>
      </div>
      <div class="col-md-6">
        <label for="cid" class="form-label">CID</label>
        <input type="text" class="form-control" id="cid" name="cid" placeholder="Insira o CID do Aluno"
          required>
      </div>
      <div class="col-md-6">
        <label for="profissional" class="form-label">Profissional</label>
        <select id="profissional" name="profissional" class="form-select" required>
          <option selected>Selecione</option>
          <option>Fonoaudióloga</option>
          <option>Psicopedagoga</option>
          <option>Psicóloga</option>
          <option>Avaliação Fonoaudióloga</option>
          <option>Avaliação Psicopedagoga</option>
          <option>Avaliação Psicóloga</option>
        </select>
      </div>
      <div class="title">
        <h3>Identificação do Aluno</h3>
      </div>
      <div class="col-md-6">
        <label for="nomeAluno" class="form-label">Nome do Aluno</label>
        <input type="text" class="form-control" id="nomeAluno" name="nomeAluno"
          placeholder="Insira o Nome do Aluno" required>
      </div>
      <div class="col-md-6">
        <label for="escola" class="form-label">Escola</label>
        <select id="escola" name="escola" class="form-select" required>
          <option selected>Selecione a Escola</option>
          <option>EMEI Irmã Dulce</option>
          <option>EMEI Creche Mariana Martins Moura Souza</option>
          <option>EMEI Creche Ana Cristina Aragão Neves</option>
          <option>EMEI Creche Aparecido dos Santos</option>
          <option>EMEI Creche Dom Hélder Pessoa Câmara</option>
          <option>EMEI Creche Madre Maria dos Anjos Amorim</option>
          <option>EMEI Creche Michele de Jesus Santos</option>
          <option>EMEI Creche Vovô Jason de Gois da Silva</option>
          <option>EMEI Jardim de Infância Pequena Fada</option>
          <option>E.M Abelardo Pereira de Melo</option>
          <option>E.M Anália Vieira de Figueiredo</option>
          <option>E.M Apulcro Mota</option>
          <option>E.M Barquinho Amarelo</option>
          <option>E.M Coronel Gentil Daltro</option>
          <option>E.M Diva Maria Corrêa</option>
          <option>E.M Eduardo Viana dos Santos</option>
          <option>E.M Izídio Marques de Melo</option>
          <option>E.M João Paulo II</option>
          <option>E.M João Vasconcelos Prado</option>
          <option>E.M José do Prado Franco</option>
          <option>E.M José Ferreira Neto</option>
          <option>E.M José Teixeira da Cruz</option>
          <option>E.M Josefa de Santana</option>
          <option>E.M Leonel Brizola</option>
          <option>E.M Luana Rolemberg Santos de Menezes</option>
          <option>E.M Major João Teles</option>
          <option>E.M Manoel Cunha</option>
          <option>E.M Manoel de Jesus Silva</option>
          <option>E.M Mariana Prado Vasconcelos</option>
          <option>E.M Nair Menezes dos Santos</option>
          <option>E.M Nossa Senhora de Lourdes</option>
          <option>E.M Nossa Senhora do Socorro</option>
          <option>E.M Padre Pedro</option>
          <option>E.M Pedro Moreira Filho</option>
          <option>E.M Professor Acrísio Cruz</option>
          <option>E.M Professora Maria Cristina dos Santos Amaro</option>
          <option>E.M Professor Donald</option>
          <option>E.M Professora Elisa Teles</option>
          <option>E.M Professora Honorina Costa</option>
          <option>E.M Professora Maria da Conceição Cruz Vasconcelos</option>
          <option>E.M Professora Maria Rizonete Silva</option>
          <option>E.M Professora Maria São Pedro Vasconcelos</option>
          <option>E.M Professora Maria Vitória Costa Santos</option>
          <option>E.M Professora Neuzice Barreto de Lima</option>
          <option>E.M Rosalvo de Queiros</option>
          <option>E.M Santa Terezinha</option>
        </select>
      </div>
      <div class="col-md-6">
        <label for="turma" class="form-label">Turma</label>
        <select id="turma" name="turma" class="form-select" required>
          <option selected>Selecione a Turma</option>
          <option>Berçário</option>
          <option>Infantil I</option>
          <option>Infantil II</option>
          <option>Infantil III</option>
          <option>Pré I</option>
          <option>Pré II</option>
          <option>1º Ano</option>
          <option>2º Ano</option>
          <option>3º Ano</option>
          <option>4º Ano</option>
          <option>5º Ano</option>
          <option>6º Ano</option>
          <option>7º Ano</option>
          <option>8º Ano</option>
          <option>9º Ano</option>
        </select>
      </div>
      <div class="col-md-6">
        <label for="turno" class="form-label">Turno</label>
        <select id="turno" name="turno" class="form-select" required>
          <option selected>Selecione o Turno</option>
          <option>Matutino</option>
          <option>Vespertino</option>
        </select>
      </div>
      <div class="col-md-6">
        <label for="dataNascimento" class="form-label">Data de Nascimento</label>
        <input type="date" class="form-control" id="dataNascimento" name="dataNascimento" required>
      </div>
      <div class="col-md-6">
        <label for="idade" class="form-label">Idade do Aluno</label>
        <input type="text" class="form-control" id="idade" name="idade" placeholder="Insira a Idade do Aluno"
          required>
      </div>
      <div class="title">
        <h3>Responsável Pela Solicitação</h3>
      </div>
      <div class="col-md-6">
        <label for="nomeResponsavel" class="form-label">Nome do Responsável</label>
        <input type="text" class="form-control" id="nomeResponsavel" name="nomeResponsavel"
          placeholder="Insira o Nome do Responsável" required>
      </div>
      <div class="col-md-6">
        <label for="endereco" class="form-label">Endereço</label>
        <input type="text" class="form-control" id="endereco" name="endereco"
          placeholder="Insira o Endereço do Responsável" required>
      </div>
      <div class="col-md-6">
        <label for="contato" class="form-label">Contato</label>
        <input type="tel" class="form-control" id="contato" name="contato"
          placeholder="Insira o Telefone do Responsável" required>
      </div>
      <div class="col-md-6">
        <label for="responsavelAtendimento" class="form-label">Responsável Pelo Atendimento</label>
        <input type="text" class="form-control" id="responsavelAtendimento" name="responsavelAtendimento"
          value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
          placeholder="Insira o Responsável pelo Atendimento" required readonly>
      </div>
      <div class="col-md-6">
        <label for="status" class="form-label">Status</label>
        <select id="status" name="status" class="form-select" required>
          <option selected>Selecione o Status</option>
          <option>Em espera</option>
          <option>Em atendimento</option>
          <option>Alta</option>
        </select>
      </div>
      <div class="col-md-6">
        <label for="dataSolicitacao" class="form-label">Data da Solicitação</label>
        <input type="date" class="form-control" id="dataSolicitacao" name="dataSolicitacao" required>
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary">Enviar</button>
      </div>
    </form>
  </main>
  <br>

  <?php include 'components/footer.php'; ?>
</body>

</html>