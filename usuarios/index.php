<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/config/api.php';
require_once dirname(__DIR__) . '/includes/auth_api.php';
require_once dirname(__DIR__) . '/includes/rbac.php';
api_require_page('usuarios');

$roleF  = $_GET['role'] ?? '';
$pagina = max(1, (int)($_GET['pagina'] ?? 1));

$params = ['pagina' => $pagina, 'por_pagina' => 20];
if ($roleF !== '') $params['role'] = $roleF;

$res      = ApiClient::get('/usuarios', $params);
$usuarios = $res['dados'] ?? [];
$total    = $res['paginacao']['total'] ?? 0;
$totalPag = (int) ceil($total / 20);

$pageTitle  = 'Usuários';
$activeMenu = 'usuarios';
require ONECHECK_ROOT . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div class="oc-page-header mb-0">
        <h2>Usuários</h2>
        <p><?= $total ?> usuário(s) cadastrado(s)</p>
    </div>
    <a href="<?= e(base_url('usuarios/novo.php')) ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Novo usuário
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form class="row g-2 align-items-end" method="get">
            <div class="col-md-4">
                <label class="form-label">Perfil</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="admin"       <?= $roleF==='admin'       ? 'selected':'' ?>>Admin</option>
                    <option value="vistoriador" <?= $roleF==='vistoriador' ? 'selected':'' ?>>Vistoriador</option>
                    <option value="locatario"   <?= $roleF==='locatario'   ? 'selected':'' ?>>Locatário</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm">Filtrar</button>
                <a href="?" class="btn btn-outline-secondary btn-sm ms-1">Limpar</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (!$usuarios): ?>
        <div class="p-4" style="color:#6b7fa3;font-size:13px">
            <i class="bi bi-people me-2"></i>Nenhum usuário encontrado.
        </div>
        <?php else: ?>
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
                    <th>MFA</th>
                    <th>Cadastrado em</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <?php $uid = $u['id'] ?? ''; $urole = $u['role'] ?? ($u['perfil'] ?? ''); ?>
                <tr>
                    <td><?= e($u['nome'] ?? '—') ?></td>
                    <td style="font-size:12px"><?= e($u['email'] ?? '—') ?></td>
                    <td>
                        <?php
                        echo match($urole) {
                            'admin'       => '<span class="badge bg-danger">Admin</span>',
                            'vistoriador' => '<span class="badge bg-primary">Vistoriador</span>',
                            'locatario'   => '<span class="badge bg-success">Locatário</span>',
                            default       => '<span class="badge bg-secondary">' . e(ucfirst($urole)) . '</span>',
                        };
                        ?>
                    </td>
                    <td>
                        <?php
                        $mfaAtivo = (bool)($u['mfa_ativo'] ?? ($u['mfa_enabled'] ?? false));
                        $mfaObr   = (bool)($u['mfa_obrigatorio'] ?? ($u['mfa_required'] ?? false));
                        if ($mfaAtivo): ?>
                            <span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>Ativo</span>
                        <?php elseif ($mfaObr): ?>
                            <span class="badge bg-warning text-dark"><i class="bi bi-shield-exclamation me-1"></i>Pendente</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><i class="bi bi-shield me-1"></i>Inativo</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:#6b7fa3"><?= e(substr($u['created_at'] ?? '', 0, 10)) ?></td>
                    <td>
                        <?php if ($uid): ?>
                        <a href="<?= e(base_url('usuarios/editar.php?id=' . urlencode($uid))) ?>"
                           class="btn btn-outline-secondary btn-sm py-0 px-2">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php if ($totalPag > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-end">
        <?php for ($p = 1; $p <= $totalPag; $p++): ?>
        <li class="page-item <?= $p === $pagina ? 'active' : '' ?>">
            <a class="page-link" href="?pagina=<?= $p ?>&role=<?= e($roleF) ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php require ONECHECK_ROOT . '/includes/footer.php'; ?>
