<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/config/api.php';
require_once dirname(__DIR__) . '/includes/auth_api.php';
require_once dirname(__DIR__) . '/includes/rbac.php';
api_require_login();

$user = api_user();

// Busca dados atualizados via GET /usuarios/me (endpoint próprio da API)
$res   = ApiClient::get('/usuarios/me');
$dados = $res['dados'] ?? $user;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? 'perfil';

    // --- Alteração de senha (endpoint dedicado: PUT /usuarios/me/senha) ---
    if ($action === 'senha') {
        $senhaAtual = $_POST['senha_atual'] ?? '';
        $senhaNova  = $_POST['nova_senha']  ?? '';
        $confirma   = $_POST['confirma']    ?? '';

        if ($senhaAtual === '' || $senhaNova === '') {
            flash_set('error', 'Informe a senha atual e a nova senha.');
        } elseif (strlen($senhaNova) < 8) {
            flash_set('error', 'A nova senha deve ter pelo menos 8 caracteres.');
        } elseif ($senhaNova !== $confirma) {
            flash_set('error', 'A nova senha e a confirmação não conferem.');
        } else {
            // API usa PUT /usuarios/me/senha com campos senha_atual e nova_senha
            $upd = ApiClient::put('/usuarios/me/senha', [
                'senha_atual' => $senhaAtual,
                'nova_senha'  => $senhaNova,
            ]);

            if (!empty($upd['sucesso'])) {
                flash_set('success', 'Senha alterada com sucesso.');
            } else {
                flash_set('error', $upd['erro'] ?? 'Não foi possível alterar a senha. Verifique a senha atual.');
            }
        }
        redirect(base_url('usuarios/perfil.php'));
    }

    // --- Atualização de nome (PUT /usuarios/{id}) ---
    $nome = post_str('nome');
    if ($nome === '') {
        flash_set('error', 'Nome é obrigatório.');
    } else {
        $upd = ApiClient::put('/usuarios/' . $user['id'], ['nome' => $nome]);
        if (!empty($upd['sucesso'])) {
            $_SESSION['user']['nome'] = $nome;
            flash_set('success', 'Perfil atualizado.');
        } else {
            flash_set('error', $upd['erro'] ?? 'Erro ao atualizar perfil.');
        }
    }
    redirect(base_url('usuarios/perfil.php'));
}

$mfaEnabled = (bool)($dados['mfa_ativo'] ?? ($dados['mfa_enabled'] ?? false));
$perfilNome = $dados['role'] ?? ($dados['perfil'] ?? '');

$pageTitle  = 'Meu perfil';
$activeMenu = 'usuarios';
$role = ($dados['role'] ?? ($dados['perfil'] ?? ''));
if ($role === 'locatario') {
    require ONECHECK_ROOT . '/locatario/_header.php';
} else {
    require ONECHECK_ROOT . '/includes/header.php';
}
flash_render();
page_header('Meu perfil', $dados['email'] ?? $user['email'] ?? '');
?>

<div class="row g-4">
    <!-- Dados pessoais -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-person me-2"></i>Dados pessoais</h6>
                <p class="mb-3">
                    <?= badge_status('perfil', $perfilNome) ?>
                    <?php if ($mfaEnabled): ?>
                        <span class="badge text-bg-success ms-1"><i class="bi bi-shield-check me-1"></i>MFA ativo</span>
                    <?php else: ?>
                        <span class="badge text-bg-warning text-dark ms-1">MFA não configurado</span>
                    <?php endif; ?>
                </p>

                <?php if (!$mfaEnabled): ?>
                <p>
                    <a href="<?= e(base_url('public/mfa-setup.php')) ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-shield-lock me-1"></i>Configurar MFA
                    </a>
                </p>
                <?php endif; ?>

                <form method="post" autocomplete="off">
                    <input type="hidden" name="_action" value="perfil">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input name="nome" class="form-control" required
                               value="<?= e($dados['nome'] ?? $user['nome'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input class="form-control" value="<?= e($dados['email'] ?? $user['email'] ?? '') ?>" disabled>
                        <div class="form-text">O e-mail não pode ser alterado.</div>
                    </div>
                    <button class="btn btn-primary btn-sm">
                        <i class="bi bi-check-lg me-1"></i>Salvar nome
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Alteração de senha -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-lock me-2"></i>Alterar senha</h6>
                <p class="small text-muted mb-3">
                    A nova senha precisa ter pelo menos 8 caracteres.
                </p>
                <form method="post" autocomplete="off">
                    <input type="hidden" name="_action" value="senha">
                    <div class="mb-3">
                        <label class="form-label">Senha atual <span style="color:#f87171">*</span></label>
                        <input type="password" name="senha_atual" class="form-control"
                               required autocomplete="current-password" placeholder="••••••••">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nova senha <span style="color:#f87171">*</span></label>
                        <input type="password" name="nova_senha" class="form-control"
                               required minlength="8" autocomplete="new-password" placeholder="Mínimo 8 caracteres">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar nova senha <span style="color:#f87171">*</span></label>
                        <input type="password" name="confirma" class="form-control"
                               required minlength="8" autocomplete="new-password" placeholder="Repita a nova senha">
                    </div>
                    <button class="btn btn-primary btn-sm">
                        <i class="bi bi-lock me-1"></i>Alterar senha
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="<?= e(api_home_url()) ?>" class="btn btn-link btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Voltar ao início
    </a>
</div>

<?php if ($role === 'locatario') {
    require ONECHECK_ROOT . '/locatario/_footer.php';
} else {
    require ONECHECK_ROOT . '/includes/footer.php';
} ?>
