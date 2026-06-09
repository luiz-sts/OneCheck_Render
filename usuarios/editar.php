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

$res  = ApiClient::get('/usuarios/' . $id);
$alvo = $res['dados'] ?? null;

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

// --- SALVAR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome        = post_str('nome');
    $perfil      = $_POST['perfil'] ?? 'vistoriador';
    $ativo       = isset($_POST['ativo']);
    $mfaObr      = isset($_POST['mfa_obrigatorio']);
    $resetarMfa  = isset($_POST['resetar_mfa']);
    $senha       = $_POST['senha'] ?? '';

    if (!in_array($perfil, $perfis, true)) {
        flash_set('error', 'Perfil inválido.');
    } elseif ($nome === '') {
        flash_set('error', 'Nome é obrigatório.');
    } else {
        $payload = [
            'nome'            => $nome,
            'role'            => $perfil,
            'ativo'           => (bool)$ativo,
            'mfa_obrigatorio' => (bool)$mfaObr,
            'mfa_required'    => (bool)$mfaObr,
        ];
        if ($senha !== '') {
            $payload['senha'] = $senha;
        }
        if ($resetarMfa) {
            $payload['resetar_mfa'] = true;
            $payload['mfa_secret']  = null;
            $payload['mfa_ativo']   = false;
            $payload['mfa_enabled'] = false;
        }

        $upd = ApiClient::put('/usuarios/' . $id, $payload);

        if (!empty($upd['sucesso'])) {
            flash_set('success', 'Usuário atualizado com sucesso.');
            redirect(base_url('usuarios/index.php'));
        } else {
            flash_set('error', $upd['erro'] ?? 'Erro ao atualizar usuário.');
        }
    }
}

$nomeAlvo   = $alvo['nome']   ?? '';
$emailAlvo  = $alvo['email']  ?? '';
$perfilAlvo = $alvo['perfil'] ?? ($alvo['role'] ?? '');
$ativoAlvo  = (bool)($alvo['ativo'] ?? true);
$mfaEnabled = (bool)($alvo['mfa_ativo'] ?? ($alvo['mfa_enabled'] ?? false));
$mfaObr     = (bool)($alvo['mfa_obrigatorio'] ?? ($alvo['mfa_required'] ?? false));

$pageTitle  = 'Editar usuário';
$activeMenu = 'usuarios';
require ONECHECK_ROOT . '/includes/header.php';
flash_render();
page_header('Editar ' . $nomeAlvo, $emailAlvo);
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">

        <!-- Status MFA -->
        <p class="small mb-3">
            Status MFA:
            <?php if ($mfaEnabled): ?>
                <span class="badge text-bg-success"><i class="bi bi-shield-check me-1"></i>Ativo</span>
            <?php else: ?>
                <span class="badge text-bg-secondary">Inativo</span>
            <?php endif; ?>
        </p>

        <!-- Formulário editar -->
        <form method="post" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nome</label>
                <input name="nome" class="form-control" required value="<?= e($nomeAlvo) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">E-mail</label>
                <input class="form-control" value="<?= e($emailAlvo) ?>" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Nova senha (opcional)</label>
                <input type="password" name="senha" class="form-control" minlength="6" autocomplete="new-password" placeholder="Deixe em branco para manter">
            </div>
            <div class="col-md-4">
                <label class="form-label">Perfil</label>
                <select name="perfil" class="form-select">
                    <?php foreach ($perfis as $pr): ?>
                    <option value="<?= e($pr) ?>" <?= $perfilAlvo === $pr ? 'selected' : '' ?>><?= e(ucfirst($pr)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label d-block">&nbsp;</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="ativo" id="ativo" <?= $ativoAlvo ? 'checked' : '' ?>>
                    <label class="form-check-label" for="ativo">Usuário ativo</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="mfa_obrigatorio" id="mfa_obr" <?= $mfaObr ? 'checked' : '' ?>>
                    <label class="form-check-label" for="mfa_obr">MFA obrigatório</label>
                </div>
                <?php if ($mfaEnabled): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="resetar_mfa" id="resetar_mfa">
                    <label class="form-check-label" for="resetar_mfa" style="color:#fbbf24">Resetar MFA</label>
                </div>
                <?php endif; ?>
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

<!-- Zona de perigo — Excluir -->
<?php if (api_is_admin()): ?>
<div class="card border-0" style="border:1px solid #5a2020 !important">
    <div class="card-body">
        <h6 style="color:#f87171"><i class="bi bi-exclamation-triangle me-2"></i>Zona de perigo</h6>
        <p class="small" style="color:#6b7fa3">
            Excluir este usuário é uma ação irreversível. Todos os dados vinculados a ele podem ser afetados.
        </p>
        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalExcluir">
            <i class="bi bi-trash me-1"></i>Excluir usuário
        </button>
    </div>
</div>

<!-- Modal confirmação -->
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
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
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
