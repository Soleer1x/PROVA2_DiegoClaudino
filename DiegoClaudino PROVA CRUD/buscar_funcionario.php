<?php
session_start();
require_once 'conexao.php';

if ($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 2){
    echo "<script>alert('Acesso negado!');window.location.href='principal.php';</script>";
    exit();
}

$funcionarios = [];
if ($_SERVER["REQUEST_METHOD"]=="POST" && !empty($_POST['busca'])){
    $busca = trim($_POST['busca']);
    if (is_numeric($busca)){
        $sql = "SELECT * FROM funcionario WHERE id_funcionario = :busca ORDER BY nome_funcionario ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM funcionario WHERE nome_funcionario LIKE :busca_nome OR email LIKE :busca_nome ORDER BY nome_funcionario ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
    }
    $stmt->execute();
    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sql = "SELECT * FROM funcionario ORDER BY id_funcionario ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Buscar Funcionário</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header class="header-nome">
    <span>Desenvolvido por: <strong>Diego Claudino Florentino</strong></span>
</header>
    <h2>Lista de Funcionários</h2>
    <form action="buscar_funcionario.php" method="POST">
        <label for="busca">Digite o ID, nome ou email (opcional):</label>
        <input type="text" id="busca" name="busca">
        <button type="submit">Pesquisar</button>
    </form>
    <?php if(!empty($funcionarios)): ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Endereço</th>
                <th>Telefone</th>
                <th>Email</th>
                <th>Ações</th>
            </tr>
            <?php foreach ($funcionarios as $f): ?>
                <tr>
                    <td><?=htmlspecialchars($f['id_funcionario'])?></td>
                    <td><?=htmlspecialchars($f['nome_funcionario'])?></td>
                    <td><?=htmlspecialchars($f['endereco'])?></td>
                    <td><?=htmlspecialchars($f['telefone'])?></td>
                    <td><?=htmlspecialchars($f['email'])?></td>
                    <td>
                        <a href="alterar_funcionario.php?id=<?=htmlspecialchars($f['id_funcionario'])?>">Alterar</a>
                        <a href="excluir_funcionario.php?id=<?=htmlspecialchars($f['id_funcionario'])?>">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Nenhum funcionário encontrado.</p>
    <?php endif; ?>
    <a href="principal.php">Voltar</a>
</body>
</html>