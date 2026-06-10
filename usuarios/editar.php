<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/config/api.php';
require_once dirname(__DIR__) . '/includes/auth_api.php';
require_once dirname(__DIR__) . '/includes/rbac.php';
api_require_page('usuarios');

$id = get_uuid('id');
if ($id === '') {
    flash_set('error', 'ID inválido.');
    redirect(base_url('usuarios/index.php'));
}

// Busca usuário — tenta endpoint direto, fallback na lista
function buscar_usuario(string $id): ?array {
    $res = ApiClient::get('/usuarios/' . $id);
    if (!empty($res['sucesso']) && !empty($res['dados'])) {
        return $res['dados'];
    }
    $lista = ApiClient::get('/usuarios', ['por_pagina' => 200]);
    foreach (($lista['dados'] ?? []) as $u) {
        if ((string)($u['id'] ?? '') === $id) return $u;
    }
    return null;
}

$alvo = buscar_usuario($id);
if (!$alvo) {
    flash_set('error', 'Usuário não encontrado.');
    redirect(base_url('usuarios/index.php'));
}

$perfis = ['admin', 'vistoriador', 'locatario'];

// --- EXCLUIR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_excluir'])) {
    $del = ApiClient::delete('/usuarios/' . $id);
    if (!empty($del['sucesso'])) {
        flash_set('success', 'Usuário excluído com sucesso.');
    } else {
        flash_set('error', $del['erro'] ?? 'Erro ao excluir usuário.');
    }
    redirect(base_url('usuarios/index.php'));
}

// --- DESATIVAR MFA (endpoint dedicado) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_disable_mfa'])) {
    $res = ApiClient::post('/auth/mfa/disable', ['usuario_id' => $id]);
    if (!empty($res['sucesso'])) {
        flash_set('success', 'MFA desativado com sucesso.');
    } else {
        flash_set('error', $res['erro'] ?? 'Erro ao desativar MFA.');
    }
    redirect(base_url('usuarios/editar.php?id=' . urlencode($id)));
}

// --- SALVAR (PUT /usuarios/{id}) ---
// A API aceita: nome, email, role — NÃO aceita senha neste endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome   = post_str('nome');
    $perfil = $_POST['perfil'] ?? 'vistoriador';

    if (!in_array($perfil, $perfis, true)) {
        flash_set('error', 'Perfil inválido.');
    } elseif ($nome === '') {
        flash_set('error', 'Nome é obrigatório.');
    } else {
        $payload = [
            'nome'  => $nome,
            'role'  => $perfil,
        ];

        $upd = ApiClient::put('/usuarios/' . $id, $payload);

        if (!empty($upd['sucesso'])) {
            flash_set('success', 'Usuário "' . $nome . '" atualizado com sucesso.');
            redirect(base_url('usuarios/index.php'));
        } else {
            flash_set('error', $upd['erro'] ?? ('Erro ao salvar (HTTP ' . ($upd['_status'] ?? '?') . ').'));
        }
    }
}

$nomeAlvo   = $alvo['nome']   ?? '';
$emailAlvo  = $alvo['email']  ?? '';
$perfilAlvo = $alvo['role']   ?? ($alvo['perfil'] ?? '');
$mfaEnabled = (bool)($alvo['mfa_ativo'] ?? ($alvo['mfa_enabled'] ?? false));
$isMe       = (string)($alvo['id'] ?? '') === (string)(api_user()['id'] ?? '');

$pageTitle  = 'Editar usuário';
$activeMenu = 'usuarios';
require ONECHECK_ROOT . '/includes/header.php';
flash_render();
page_header('Editar ' . $nomeAlvo, $emailAlvo);
?>

<div class="row g-4">
    <!-- Dados do usuário -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="small mb-3">
                    MFA:
                    <?php if ($mfaEnabled): ?>
                        <span class="badge text-bg-success"><i class="bi bi-shield-check me-1"></i>Ativo</span>
                    <?php else: ?>
                        <span class="badge text-bg-secondary">Inativo</span>
                    <?php endif; ?>
                </p>

                <form method="post" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome</label>
                        <input name="nome" class="form-control" required value="<?= e($nomeAlvo) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">E-mail</label>
                        <input class="form-control" value="<?= e($emailAlvo) ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Perfil</label>
                        <select name="perfil" class="form-select">
                            <?php foreach ($perfis as $pr): ?>
                            <option value="<?= e($pr) ?>" <?= $perfilAlvo === $pr ? 'selected' : '' ?>>
                                <?= e(ucfirst($pr)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Salvar alterações
                        </button>
                        <a href="<?= e(base_url('usuarios/index.php')) ?>" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Ações administrativas -->
    <div class="col-lg-5">
        <!-- MFA -->
        <?php if ($mfaEnabled): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="mb-2"><i class="bi bi-shield-lock me-2"></i>Autenticação MFA</h6>
                <p class="small text-muted mb-3">
                    O MFA está ativo para este usuário. Você pode desativá-lo caso o usuário perca o acesso ao app autenticador.
                </p>
                <button type="button" class="btn btn-warning btn-sm"
                        data-bs-toggle="modal" data-bs-target="#modalDisableMfa">
                    <i class="bi bi-shield-x me-1"></i>Desativar MFA
                </button>
            </div>
        </div>
        <?php else: ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="mb-2"><i class="bi bi-shield me-2"></i>Autenticação MFA</h6>
                <p class="small text-muted mb-0">
                    MFA inativo. O usuário pode configurar pelo próprio perfil após o login.
                </p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Alterar senha — só para o próprio usuário, redireciona para perfil -->
        <?php if ($isMe): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="mb-2"><i class="bi bi-lock me-2"></i>Senha</h6>
                <p class="small text-muted mb-3">Para alterar sua própria senha, use a página de perfil.</p>
                <a href="<?= e(base_url('usuarios/perfil.php')) ?>" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-person me-1"></i>Ir para meu perfil
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Zona de perigo -->
        <?php if (api_is_admin() && !$isMe): ?>
        <div class="card border-0" style="border:1px solid #5a2020 !important">
            <div class="card-body">
                <h6 style="color:#f87171"><i class="bi bi-exclamation-triangle me-2"></i>Zona de perigo</h6>
                <p class="small" style="color:#6b7fa3">
                    Excluir este usuário é irreversível e pode afetar contratos e vistorias vinculados.
                </p>
                <button type="button" class="btn btn-outline-danger btn-sm"
                        data-bs-toggle="modal" data-bs-target="#modalExcluir">
                    <i class="bi bi-trash me-1"></i>Excluir usuário
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: desativar MFA -->
<?php if ($mfaEnabled): ?>
<div class="modal fade" id="modalDisableMfa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Desativar MFA</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Desativar o MFA de <strong><?= e($nomeAlvo) ?></strong>?</p>
                <p class="small text-muted">O usuário poderá entrar sem o código de autenticação até configurar novamente.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="post">
                    <input type="hidden" name="_disable_mfa" value="1">
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-shield-x me-1"></i>Desativar MFA
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal: excluir usuário -->
<?php if (api_is_admin() && !$isMe): ?>
<div class="modal fade" id="modalExcluir" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o usuário <strong><?= e($nomeAlvo) ?></strong>?</p>
                <p class="small" style="color:#f87171">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="post">
                    <input type="hidden" name="_excluir" value="1">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Excluir definitivamente
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require ONECHECK_ROOT . '/includes/footer.php'; ?>
