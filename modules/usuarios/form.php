

<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirPerfil(['admin', 'professor']);

$perfil = $_SESSION['tipo'];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Impedir ID inválido na URL
if (isset($_GET['id']) && (!$id || $id < 1)) {
    die('ID de usuário inválido.');
}

$usuario = [
    'nome' => '',
    'email' => '',
    'tipo' => 'aluno',
    'status' => 'ativo'
];

$mensagem = '';
$erro = false;

// Localizar usuário para edição
if ($id) {
    if ($perfil === 'professor') {
        $stmt = $pdo->prepare(
            "SELECT id, nome, email, tipo, status
             FROM usuarios
             WHERE id = :id AND tipo = 'aluno'"
        );
    } else {
        $stmt = $pdo->prepare(
            "SELECT id, nome, email, tipo, status
             FROM usuarios
             WHERE id = :id"
        );
    }

    $stmt->execute([':id' => $id]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resultado) {
        die('Usuário não encontrado ou sem permissão para edição.');
    }

    $usuario = $resultado;
}

// Receber dados do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $status = $_POST['status'] ?? 'ativo';

    // Professor só pode trabalhar com alunos
    if ($perfil === 'professor') {
        $tipo = 'aluno';
    } else {
        $tipo = $_POST['tipo'] ?? 'aluno';
    }

    $usuario = [
        'nome' => $nome,
        'email' => $email,
        'tipo' => $tipo,
        'status' => $status
    ];

    // Validar campos
    if ($nome === '' || $email === '') {
        $mensagem = 'Preencha todos os campos obrigatórios.';
        $erro = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = 'Informe um e-mail válido.';
        $erro = true;
    } elseif (!$id && $senha === '') {
        $mensagem = 'Informe uma senha para o novo usuário.';
        $erro = true;
    } elseif (!in_array($tipo, ['admin', 'professor', 'aluno'], true)) {
        $mensagem = 'Tipo de usuário inválido.';
        $erro = true;
    } elseif (!in_array($status, ['ativo', 'bloqueado'], true)) {
        $mensagem = 'Status inválido.';
        $erro = true;
    }

    // Verificar e-mail duplicado
    if (!$erro) {
        $sql = "SELECT id FROM usuarios WHERE email = :email";

        if ($id) {
            $sql .= " AND id != :id";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $email);

        if ($id) {
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        }

        $stmt->execute();

        if ($stmt->fetch()) {
            $mensagem = 'Este e-mail já está cadastrado.';
            $erro = true;
        }
    }

    // Atualizar usuário
    if (!$erro && $id) {
        // Proteção adicional no UPDATE para professor
        $condicao = $perfil === 'professor'
            ? "WHERE id = :id AND tipo = 'aluno'"
            : "WHERE id = :id";

        if ($senha !== '') {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "UPDATE usuarios
                 SET nome = :nome,
                     email = :email,
                     senha = :senha,
                     tipo = :tipo,
                     status = :status
                 $condicao"
            );

            $stmt->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':senha' => $senhaHash,
                ':tipo' => $tipo,
                ':status' => $status,
                ':id' => $id
            ]);
        } else {
            $stmt = $pdo->prepare(
                "UPDATE usuarios
                 SET nome = :nome,
                     email = :email,
                     tipo = :tipo,
                     status = :status
                 $condicao"
            );

            $stmt->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':tipo' => $tipo,
                ':status' => $status,
                ':id' => $id
            ]);
        }

        $mensagem = 'Usuário atualizado com sucesso!';

    // Cadastrar usuário
    } elseif (!$erro && !$id) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            "INSERT INTO usuarios (nome, email, senha, tipo, status)
             VALUES (:nome, :email, :senha, :tipo, :status)"
        );

        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':senha' => $senhaHash,
            ':tipo' => $tipo,
            ':status' => $status
        ]);

        $mensagem = 'Usuário cadastrado com sucesso!';

        $usuario = [
            'nome' => '',
            'email' => '',
            'tipo' => 'aluno',
            'status' => 'ativo'
        ];
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<main class="container">
    <section class="card">

        <h1 class="page-title">
            <?= $id ? 'Editar usuário' : 'Novo usuário' ?>
        </h1>

        <?php if ($mensagem): ?>
            <p class="alert">
                <?= htmlspecialchars($mensagem) ?>
            </p>
        <?php endif; ?>

        <form method="POST">

            <label>Nome</label>
            <input
                type="text"
                name="nome"
                value="<?= htmlspecialchars($usuario['nome']) ?>"
                required
            >

            <label>E-mail</label>
            <input
                type="email"
                name="email"
                value="<?= htmlspecialchars($usuario['email']) ?>"
                required
            >

            <label>Senha</label>
            <input
                type="password"
                name="senha"
                <?= !$id ? 'required' : '' ?>
            >

            <?php if ($id): ?>
                <small>Deixe em branco para manter a senha atual.</small>
            <?php endif; ?>

            <?php if ($perfil === 'admin'): ?>
                <label>Tipo</label>
                <select name="tipo" required>
                    <option value="aluno" <?= $usuario['tipo'] === 'aluno' ? 'selected' : '' ?>>Aluno</option>
                    <option value="professor" <?= $usuario['tipo'] === 'professor' ? 'selected' : '' ?>>Professor</option>
                    <option value="admin" <?= $usuario['tipo'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                </select>
            <?php else: ?>
                <p><strong>Tipo:</strong> Aluno</p>
            <?php endif; ?>

            <label>Status</label>
            <select name="status" required>
                <option value="ativo" <?= $usuario['status'] === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="bloqueado" <?= $usuario['status'] === 'bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
            </select>

            <button type="submit" class="button">
                Salvar
            </button>

            <a href="index.php">Voltar</a>

        </form>
    </section>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
