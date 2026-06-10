<nav class="navbar app-navbar">
    <div class="container-fluid">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-light btn-sm d-lg-none" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand" href="<?= e(base_url('dashboard/index.php')) ?>"
               style="font-weight:700;letter-spacing:-.3px;font-size:16px">
                <span class="brand-one">One</span><span class="brand-check">Check</span>
            </a>
        </div>

        <?php $u = api_user(); if ($u): ?>
        <div class="d-flex align-items-center gap-2">
            <div class="dropdown">
                <button class="btn d-flex align-items-center gap-2 p-0 border-0 bg-transparent"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                        style="cursor:pointer">
                    <span class="d-none d-md-inline" style="font-size:12px;color:#6b7fa3">
                        <?= e($u['nome'] ?? '') ?>
                    </span>
                    <div class="oc-avatar"><?= e(api_initials()) ?></div>
                    <i class="bi bi-chevron-down d-none d-md-inline" style="font-size:10px;color:#6b7fa3"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="min-width:210px">
                    <!-- Info do usuário -->
                    <li>
                        <div class="px-3 py-2" style="border-bottom:0.5px solid #2a3347">
                            <div style="font-size:13px;color:#e2e8f0;font-weight:500"><?= e($u['nome'] ?? '') ?></div>
                            <div style="font-size:11px;color:#6b7fa3"><?= e($u['email'] ?? '') ?></div>
                            <div class="mt-1 d-flex gap-1 flex-wrap">
                                <?php
                                $role = $u['role'] ?? '';
                                $roleBadge = match($role) {
                                    'admin'       => '<span class="badge bg-danger" style="font-size:10px">Admin</span>',
                                    'vistoriador' => '<span class="badge bg-primary" style="font-size:10px">Vistoriador</span>',
                                    'locatario'   => '<span class="badge bg-success" style="font-size:10px">Locatário</span>',
                                    default       => '',
                                };
                                echo $roleBadge;
                                if ($u['mfa_ativo'] ?? false):
                                ?>
                                <span class="badge bg-success" style="font-size:10px">
                                    <i class="bi bi-shield-check"></i> MFA
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                    <!-- Meu perfil -->
                    <li>
                        <a class="dropdown-item" href="<?= e(base_url('usuarios/perfil.php')) ?>">
                            <i class="bi bi-person me-2"></i>Meu perfil
                        </a>
                    </li>
                    <!-- Alterar senha -->
                    <li>
                        <a class="dropdown-item" href="<?= e(base_url('usuarios/minha-senha.php')) ?>">
                            <i class="bi bi-key me-2"></i>Alterar senha
                        </a>
                    </li>
                    <!-- Configurar MFA (só se não tiver) -->
                    <?php if (!($u['mfa_ativo'] ?? false)): ?>
                    <li>
                        <a class="dropdown-item" href="<?= e(base_url('public/mfa-setup.php')) ?>">
                            <i class="bi bi-shield-lock me-2" style="color:#4f8ef7"></i>Configurar MFA
                        </a>
                    </li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider" style="border-color:#2a3347"></li>
                    <!-- Sair -->
                    <li>
                        <a class="dropdown-item" href="<?= e(base_url('public/logout.php')) ?>"
                           style="color:#f87171">
                            <i class="bi bi-box-arrow-right me-2"></i>Sair
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
</nav>
