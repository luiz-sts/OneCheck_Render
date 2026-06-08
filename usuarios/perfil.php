<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/config/api.php';
require_once dirname(__DIR__) . '/includes/auth_api.php';
require_once dirname(__DIR__) . '/includes/rbac.php';
api_require_login();

$user = api_user();

// Busca dados atualizados do usuário via API
$res   = ApiClient::get('/usuarios/' . $user['id']);
$dados = $res['dados'] ?? $user;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = post_str('nome');
    $senhaNova = $_POST['senha_nova']  ?? '';
    $senhaAtual = $_POST['senha_atual'] ?? '';

    if ($nome === '') {
        flash_set('error', 'Nome é obrigatório.');
    } else {
        $payload = ['nome' => $nome];
        if ($senhaNova !== '') {
            $payload['senha_atual'] = $senhaAtual;
            $payload['senha']       = $senhaNova;
        }

        $upd = ApiClient::put('/usuarios/' . $user['id'], $payload);

        if (!empty($upd['sucesso'])) {
            $_SESSION['user']['nome'] = $nome;
            flash_set('success', 'Perfil atualizado.');
            redirect(base_url('usuarios/perfil.php'));
        } else {
            flash_set('error', $upd['erro'] ?? 'Erro ao atualizar perfil.');
        }
    }
}

$mfaEnabled = (bool)($dados['mfa_ativo'] ?? ($dados['mfa_enabled'] ?? false));
$perfilNome = $dados['perfil'] ?? ($dados['role'] ?? '');

$pageTitle  = 'Meu perfil';
$activeMenu = 'usuarios';
require ONECHECK_ROOT . '/includes/header.php';
flash_render();
page_header('Meu perfil', $dados['email'] ?? $user['email'] ?? '');
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="mb-3">
                    <?= badge_status('perfil', $perfilNome) ?>
                    <?php if ($mfaEnabled): ?>
                        <span class="badge text-bg-success"><i class="bi bi-shield-check me-1"></i>MFA ativo</span>
                    <?php else: ?>
                        <span class="badge text-bg-warning text-dark">MFA não configurado</span>
                    <?php endif; ?>
                </p>

                <?php if (!$mfaEnabled): ?>
                <p>
                    <a href="<?= e(base_url('public/mfa-setup.php')) ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-shield-lock me-1"></i>Configurar autenticação em 2 fatores
                    </a>
                </p>
                <?php endif; ?>

                <form method="post" class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nome</label>
                        <input name="nome" class="form-control" required value="<?= e($dados['nome'] ?? $user['nome'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">E-mail</label>
                        <input class="form-control" value="<?= e($dados['email'] ?? $user['email'] ?? '') ?>" disabled>
                    </div>
                    <div class="col-12"><hr><p class="small text-muted mb-0">Alterar senha (opcional)</p></div>
                    <div class="col-md-6">
                        <label class="form-label">Senha atual</label>
                        <input type="password" name="senha_atual" class="form-control" autocomplete="current-password">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nova senha</label>
                        <input type="password" name="senha_nova" class="form-control" minlength="6" autocomplete="new-password">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary">Salvar</button>
                        <a href="<?= e(api_home_url()) ?>" class="btn btn-link">Voltar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require ONECHECK_ROOT . '/includes/footer.php'; ?>
