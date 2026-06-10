<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/config/api.php';
require_once dirname(__DIR__) . '/includes/auth_api.php';
require_once dirname(__DIR__) . '/includes/rbac.php';

api_require_login();

$erro    = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senhaAtual = $_POST['senha_atual'] ?? '';
    $novaSenha  = $_POST['nova_senha']  ?? '';
    $confirma   = $_POST['confirma']    ?? '';

    if (!$senhaAtual || !$novaSenha || !$confirma) {
        $erro = 'Preencha todos os campos.';
    } elseif (strlen($novaSenha) < 8) {
        $erro = 'A nova senha deve ter no mínimo 8 caracteres.';
    } elseif ($novaSenha !== $confirma) {
        $erro = 'A confirmação da nova senha não confere.';
    } else {
        $res = ApiClient::put('/usuarios/me/senha', [
            'senha_atual' => $senhaAtual,
            'nova_senha'  => $novaSenha,
        ]);

        if (!empty($res['sucesso'])) {
            $sucesso = 'Senha alterada com sucesso!';
        } else {
            $erro = $res['erro'] ?? ($res['message'] ?? 'Erro ao alterar senha.');
        }
    }
}

$user       = api_user();
$role       = $user['role'] ?? 'locatario';
$pageTitle  = 'Alterar senha';
$activeMenu = '';

// Usar o header correto dependendo do perfil
if ($role === 'locatario') {
    require ONECHECK_ROOT . '/locatario/_header.php';
} else {
    require ONECHECK_ROOT . '/includes/header.php';
}
?>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div class="oc-page-header mb-0">
        <h2>Alterar senha</h2>
        <p>Atualize a senha da sua conta</p>
    </div>
    <?php
    $back = $role === 'locatario'
        ? base_url('locatario/index.php')
        : base_url('usuarios/perfil.php');
    ?>
    <a href="<?= e($back) ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<?php if ($erro): ?>
<div class="alert alert-danger mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= e($erro) ?></div>
<?php endif; ?>
<?php if ($sucesso): ?>
<div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i><?= e($sucesso) ?></div>
<?php endif; ?>

<div class="card" style="max-width:480px">
    <div class="card-body">
        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">Senha atual</label>
                <input type="password" name="senha_atual" class="form-control"
                       required autocomplete="current-password" placeholder="••••••••">
            </div>
            <div class="mb-3">
                <label class="form-label">Nova senha</label>
                <input type="password" name="nova_senha" class="form-control"
                       required minlength="8" autocomplete="new-password"
                       placeholder="Mínimo 8 caracteres">
            </div>
            <div class="mb-4">
                <label class="form-label">Confirmar nova senha</label>
                <input type="password" name="confirma" class="form-control"
                       required minlength="8" autocomplete="new-password"
                       placeholder="Repita a nova senha">
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-check-lg me-1"></i>Salvar nova senha
            </button>
        </form>
    </div>
</div>

<?php
if ($role === 'locatario') {
    require ONECHECK_ROOT . '/locatario/_footer.php';
} else {
    require ONECHECK_ROOT . '/includes/footer.php';
}
?>
