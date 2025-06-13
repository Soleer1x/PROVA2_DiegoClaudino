<?php
session_start();
require_once 'conexao.php';

if ($_SESSION['perfil'] != 1){
    echo "<script>alert('Acesso negado!');window.location.href='principal.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"]=="POST"){
    $nome = trim($_POST['nome_funcionario']);
    $endereco = trim($_POST['endereco']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);

    if (empty($nome) || empty($email)) {
        echo "<script>alert('Nome e Email são obrigatórios!');</script>";
    } else {
        $sql = "SELECT id_funcionario FROM funcionario WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "<script>alert('Já existe funcionário com este e-mail!');</script>";
        } else {
            $sql = "INSERT INTO funcionario (nome_funcionario, endereco, telefone, email) VALUES (:nome, :endereco, :telefone, :email)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':endereco', $endereco);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':email', $email);

            if ($stmt->execute()){
                echo "<script>alert('Funcionário cadastrado com sucesso!');window.location.href='cadastro_funcionario.php';</script>";
            } else {
                echo "<script>alert('Erro ao cadastrar funcionário!');</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Funcionário</title>
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
    </script>
</head>
<body>
<header class="header-nome">
    <span>Desenvolvido por: <strong>Diego Claudino Florentino</strong></span>
</header>
    <h2>Cadastrar Funcionário</h2>
    <form class="cadastro-funcionario-form" action="cadastro_funcionario.php" method="POST" autocomplete="off">
        <label for="nome_funcionario">Nome:</label>
        <input type="text" id="nome_funcionario" name="nome_funcionario" required
               pattern="[A-Za-zÀ-ÿ\s]+"
               title="Digite apenas letras"
               onkeypress="somenteLetras(event)"
               onpaste="validarNomeOnPaste(event)">

        <label for="endereco">Endereço:</label>
        <input type="text" id="endereco" name="endereco">

        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone"
               pattern="\(\d{2}\)\s\d{5}-\d{4}"
               maxlength="15"
               title="Digite no formato (99) 99999-9999"
               oninput="mascaraTelefone(event)"
               onpaste="validarTelefoneOnPaste(event)">

        <label for="email">Email:</label> 
        <input type="email" id="email" name="email" required>

        <button type="submit">Salvar</button>
        <button type="reset">Cancelar</button>
    </form>
    <a href="principal.php">Voltar</a>
</body>
</html>