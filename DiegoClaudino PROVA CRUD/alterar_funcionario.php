<?php
session_start();
require_once 'conexao.php';

if ($_SESSION['perfil'] != 1) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit();
}

// Se não veio ID, pede para buscar o funcionário
if (!isset($_GET['id']) || intval($_GET['id']) <= 0) {
    // Se enviou busca via POST
    $funcionario = null;
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['busca_funcionario'])) {
        $busca = trim($_POST['busca_funcionario']);
        if (is_numeric($busca)) {
            $sql = "SELECT * FROM funcionario WHERE id_funcionario = :busca";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
        } else {
            $sql = "SELECT * FROM funcionario WHERE nome_funcionario LIKE :busca_nome";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
        }
        $stmt->execute();
        $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($funcionario) {
            // Redireciona para a própria página com o id encontrado
            header("Location: alterar_funcionario.php?id=" . $funcionario['id_funcionario']);
            exit();
        } else {
            echo "<script>alert('Funcionário não encontrado!');</script>";
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>Alterar Funcionário</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <h2>Buscar Funcionário para Alterar</h2>
        <form action="alterar_funcionario.php" method="POST">
            <label for="busca_funcionario">Digite o ID ou nome do funcionário:</label>
            <input type="text" id="busca_funcionario" name="busca_funcionario" required>
            <button type="submit">Buscar</button>
        </form>
        <a href="principal.php">Voltar</a>
    </body>
    </html>
    <?php
    exit();
}

// Daqui pra baixo: fluxo original com ID definido!
$id = intval($_GET['id']);

$sql = "SELECT * FROM funcionario WHERE id_funcionario = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$funcionario) {
    echo "<script>alert('Funcionário não encontrado!'); window.location.href='alterar_funcionario.php';</script>";
    exit();
}

$nome = $funcionario['nome_funcionario'];
$endereco = $funcionario['endereco'];
$telefone = $funcionario['telefone'];
$email = $funcionario['email'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nome_funcionario'])) {
    $nome = trim($_POST['nome_funcionario']);
    $endereco = trim($_POST['endereco']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);

    if (empty($nome) || empty($email)) {
        echo "<script>alert('Nome e Email são obrigatórios!');</script>";
    } else {
        $sql_check = "SELECT id_funcionario FROM funcionario WHERE email = :email AND id_funcionario != :id";
        $stmt = $pdo->prepare($sql_check);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "<script>alert('Já existe funcionário com este e-mail!');</script>";
        } else {
            $sql = "UPDATE funcionario SET nome_funcionario = :nome, endereco = :endereco, telefone = :telefone, email = :email WHERE id_funcionario = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':endereco', $endereco);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                echo "<script>alert('Funcionário alterado com sucesso!');window.location='buscar_funcionario.php';</script>";
                exit();
            } else {
                echo "<script>alert('Erro ao alterar funcionário!');</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Alterar Funcionário</title>
    <link rel="stylesheet" href="styles.css">
    <script>
    // Permitir apenas letras no campo nome
    function somenteLetras(e) {
        let char = String.fromCharCode(e.which);
        if (!/[a-zA-ZÀ-ÿ\s]/.test(char) && e.which !== 8 && e.which !== 0) {
            e.preventDefault();
        }
    }
    // Máscara telefone (99) 99999-9999
    function mascaraTelefone(e) {
        let input = e.target;
        let value = input.value.replace(/\D/g, '');

        if (value.length > 11) value = value.slice(0, 11);

        if (value.length > 0) {
            value = '(' + value;
        }
        if (value.length > 3) {
            value = value.slice(0, 3) + ') ' + value.slice(3);
        }
        if (value.length > 10) {
            value = value.slice(0, 10) + '-' + value.slice(10);
        }
        input.value = value;
    }
    // Bloquear colar caracteres inválidos no nome
    function validarNomeOnPaste(e) {
        let paste = (e.clipboardData || window.clipboardData).getData('text');
        if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(paste)) {
            e.preventDefault();
        }
    }
    // Bloquear colar caracteres inválidos no telefone
    function validarTelefoneOnPaste(e) {
        let paste = (e.clipboardData || window.clipboardData).getData('text');
        if (!/\(?\d{2}\)?\s?\d{5}-?\d{4}/.test(paste)) {
            e.preventDefault();
        }
    }
    window.onload = function() {
        // Aplica a máscara imediatamente se já houver valor no input (ex: vindo do banco)
        var tel = document.getElementById('telefone');
        if (tel) {
            mascaraTelefone({target: tel});
            tel.addEventListener('input', mascaraTelefone);
        }
    }
    </script>
</head>
<body>
<header class="header-nome">
    <span>Desenvolvido por: <strong>Diego Claudino Florentino</strong></span>
</header>
    <h2>Alterar Funcionário</h2>
    <form class="alterar-funcionario-form" action="" method="POST">
        <label for="nome_funcionario">Nome:</label>
        <input type="text" id="nome_funcionario" name="nome_funcionario"
               value="<?=htmlspecialchars($nome)?>" required
               pattern="[A-Za-zÀ-ÿ\s]+"
               title="Digite apenas letras"
               onkeypress="somenteLetras(event)"
               onpaste="validarNomeOnPaste(event)">

        <label for="endereco">Endereço:</label>
        <input type="text" id="endereco" name="endereco" value="<?=htmlspecialchars($endereco)?>">

        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone"
               value="<?=htmlspecialchars($telefone)?>"
               pattern="\(\d{2}\)\s\d{5}-\d{4}"
               maxlength="15"
               title="Digite no formato (99) 99999-9999"
               oninput="mascaraTelefone(event)"
               onpaste="validarTelefoneOnPaste(event)">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?=htmlspecialchars($email)?>" required>

        <button type="submit">Alterar</button>
        <button type="reset" onclick="window.location='buscar_funcionario.php'">Cancelar</button>
    </form>
    <a href="principal.php">Voltar</a>
</body>
</html>