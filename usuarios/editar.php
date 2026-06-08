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

// Busca usuário via API
$res  = ApiClient::get('/usuarios/' . $id);
$alvo = $res['dados'] ?? null;

if (!$alvo) {
    flash_set('error', 'Usuário não encontrado.');
    redirect(base_url('usuarios/index.php'));
}

$perfis = ['admin', 'gestor', 'vistoriador', 'visualizador', 'locatario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome   = post_str('nome');
    $perfil = $_POST['perfil'] ?? 'vistoriador';
    $ativo  = isset($_POST['ativo']) ? true : false;
    $mfaObr = isset($_POST['mfa_obrigatorio']) ? true : false;
    $senha  = $_POST['senha'] ?? '';

    if (!in_array($perfil, $perfis, true)) {
        flash_set('error', 'Perfil inválido.');
    } elseif ($nome === '') {
        flash_set('error', 'Nome é obrigatório.');
    } else {
        $payload = [
            'nome'            => $nome,
            'role'            => $perfil,
            'ativo'           => $ativo,
            'mfa_obrigatorio' => $mfaObr,
        ];
        if ($senha !== '') {
            $payload['senha'] = $senha;
        }

        $upd = ApiClient::put('/usuarios/' . $id, $payload);

        if (!empty($upd['sucesso'])) {
            flash_set('success', 'Usuário atualizado.');
            redirect(base_url('usuarios/index.php'));
        } else {
            flash_set('error', $upd['erro'] ?? 'Erro ao atualizar usuário.');
        }
    }
}

// Normaliza campos que podem vir com nomes diferentes entre versões da API
$nomeAlvo   = $alvo['nome']              ?? '';
$emailAlvo  = $alvo['email']             ?? '';
$perfilAlvo = $alvo['perfil']            ?? ($alvo['role'] ?? '');
$ativoAlvo  = (bool)($alvo['ativo']      ?? true);
$mfaEnabled = (bool)($alvo['mfa_ativo']  ?? ($alvo['mfa_enabled'] ?? false));
$mfaObr     = (bool)($alvo['mfa_obrigatorio'] ?? false);

$pageTitle  = 'Editar usuário';
$activeMenu = 'usuarios';
require ONECHECK_ROOT . '/includes/header.php';
flash_render();
page_header('Editar ' . $nomeAlvo, $emailAlvo);
?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <p class="small text-muted mb-3">
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
            <div class="col-md-4">
                <label class="form-label">Nova senha (opcional)</label>
                <input type="password" name="senha" class="form-control" minlength="6" autocomplete="new-password">
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
                    <label class="form-check-label" for="ativo">Ativo</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="mfa_obrigatorio" id="mfa_obr" <?= $mfaObr ? 'checked' : '' ?>>
                    <label class="form-check-label" for="mfa_obr">MFA obrigatório</label>
                </div>
                <?php if ($mfaEnabled): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="resetar_mfa" id="resetar_mfa">
                    <label class="form-check-label text-warning" for="resetar_mfa">Resetar MFA</label>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-12">
                <button class="btn btn-primary">Salvar</button>
                <a href="<?= e(base_url('usuarios/index.php')) ?>" class="btn btn-outline-secondary ms-2">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require ONECHECK_ROOT . '/includes/footer.php'; ?>
