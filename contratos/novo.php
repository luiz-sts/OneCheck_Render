<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/config/api.php';
require_once dirname(__DIR__) . '/includes/auth_api.php';
require_once dirname(__DIR__) . '/includes/rbac.php';
api_require_page('contratos');

$erro = '';

// Busca imóveis disponíveis (com endereço incluso na resposta)
$resImoveis = ApiClient::get('/imoveis', ['status' => 'disponivel', 'por_pagina' => 100]);
$imoveis    = $resImoveis['dados'] ?? [];

// Busca locatários — a API filtra por role=locatario
$resLocatarios = ApiClient::get('/usuarios', ['role' => 'locatario', 'por_pagina' => 100]);
$locatarios    = $resLocatarios['dados'] ?? [];

// Debug silencioso: se a listagem vier vazia, tenta sem filtro de role
// (algumas versões da API não suportam o filtro por query string)
if (empty($locatarios)) {
    $resTodos = ApiClient::get('/usuarios', ['por_pagina' => 200]);
    $todos = $resTodos['dados'] ?? [];
    $locatarios = array_values(array_filter($todos, function ($u) {
        $r = $u['role'] ?? ($u['perfil'] ?? '');
        return strtolower($r) === 'locatario';
    }));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imovelId    = trim($_POST['imovel_id']    ?? '');
    $locatarioId = trim($_POST['locatario_id'] ?? '');
    $dataInicio  = trim($_POST['data_inicio']  ?? '');
    $dataFim     = trim($_POST['data_fim']     ?? '');

    if (!$imovelId || !$locatarioId || !$dataInicio || !$dataFim) {
        $erro = 'Imóvel, locatário, data início e data fim são obrigatórios.';
    } else {
        $res = ApiClient::post('/contratos', [
            'imovel_id'    => $imovelId,
            'locatario_id' => $locatarioId,
            'data_inicio'  => $dataInicio,
            'data_fim'     => $dataFim,
        ]);

        if (!empty($res['sucesso'])) {
            flash_set('success', 'Contrato criado com sucesso.');
            redirect(base_url('contratos/index.php'));
        } else {
            $erro = $res['erro'] ?? ('Erro ao criar contrato (HTTP ' . ($res['_status'] ?? '?') . ').');
        }
    }
}

// Monta label legível para cada imóvel
function imovel_label(array $im): string {
    $end = $im['endereco'] ?? null;
    $tipo = $im['tipo'] ?? 'Imóvel';
    $tam  = $im['tamanho'] ? ' · ' . $im['tamanho'] : '';
    if ($end) {
        $rua  = trim(($end['rua'] ?? '') . ', ' . ($end['numero'] ?? ''));
        $comp = $end['complemento'] ? ' ' . $end['complemento'] : '';
        $cid  = $end['cidade'] ?? '';
        return $tipo . $tam . ' — ' . $rua . $comp . ($cid ? ', ' . $cid : '');
    }
    return $tipo . $tam . ' — ID: ' . substr($im['id'] ?? '', 0, 8) . '...';
}

$pageTitle  = 'Novo contrato';
$activeMenu = 'contratos';
require ONECHECK_ROOT . '/includes/header.php';
flash_render();
?>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div class="oc-page-header mb-0">
        <h2>Novo contrato</h2>
        <p>Vincule um imóvel a um locatário</p>
    </div>
    <a href="<?= e(base_url('contratos/index.php')) ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Voltar
    </a>
</div>

<?php if ($erro): ?>
<div class="alert alert-danger mb-3"><i class="bi bi-exclamation-triangle me-2"></i><?= e($erro) ?></div>
<?php endif; ?>

<?php if (empty($imoveis)): ?>
<div class="alert alert-warning mb-3">
    <i class="bi bi-info-circle me-2"></i>
    Nenhum imóvel disponível. <a href="<?= e(base_url('imoveis/novo.php')) ?>">Cadastre um imóvel</a> antes de criar um contrato.
</div>
<?php endif; ?>

<?php if (empty($locatarios)): ?>
<div class="alert alert-warning mb-3">
    <i class="bi bi-info-circle me-2"></i>
    Nenhum locatário cadastrado. <a href="<?= e(base_url('usuarios/novo.php')) ?>">Cadastre um usuário</a> com perfil Locatário.
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="post" autocomplete="off">
            <div class="row g-3">
                <!-- Imóvel -->
                <div class="col-md-6">
                    <label class="form-label">Imóvel disponível <span style="color:#f87171">*</span></label>
                    <select name="imovel_id" class="form-select" required <?= empty($imoveis) ? 'disabled' : '' ?>>
                        <option value="">Selecione um imóvel...</option>
                        <?php foreach ($imoveis as $im): ?>
                        <option value="<?= e($im['id']) ?>"
                            <?= ($_POST['imovel_id'] ?? '') === $im['id'] ? 'selected' : '' ?>>
                            <?= e(imovel_label($im)) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($imoveis)): ?>
                    <div class="form-text"><?= count($imoveis) ?> imóvel(is) disponível(is)</div>
                    <?php endif; ?>
                </div>

                <!-- Locatário -->
                <div class="col-md-6">
                    <label class="form-label">Locatário <span style="color:#f87171">*</span></label>
                    <select name="locatario_id" class="form-select" required <?= empty($locatarios) ? 'disabled' : '' ?>>
                        <option value="">Selecione um locatário...</option>
                        <?php foreach ($locatarios as $loc): ?>
                        <option value="<?= e($loc['id']) ?>"
                            <?= ($_POST['locatario_id'] ?? '') === $loc['id'] ? 'selected' : '' ?>>
                            <?= e($loc['nome'] ?? '(sem nome)') ?> — <?= e($loc['email'] ?? '') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($locatarios)): ?>
                    <div class="form-text"><?= count($locatarios) ?> locatário(s) cadastrado(s)</div>
                    <?php endif; ?>
                </div>

                <!-- Datas -->
                <div class="col-md-6">
                    <label class="form-label">Data início <span style="color:#f87171">*</span></label>
                    <input type="date" name="data_inicio" class="form-control" required
                           value="<?= e($_POST['data_inicio'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Data fim <span style="color:#f87171">*</span></label>
                    <input type="date" name="data_fim" class="form-control" required
                           value="<?= e($_POST['data_fim'] ?? '') ?>">
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary" <?= (empty($imoveis) || empty($locatarios)) ? 'disabled' : '' ?>>
                    <i class="bi bi-check-lg me-1"></i>Criar contrato
                </button>
                <a href="<?= e(base_url('contratos/index.php')) ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require ONECHECK_ROOT . '/includes/footer.php'; ?>
