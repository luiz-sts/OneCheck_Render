<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/auth_api.php';
require_once dirname(__DIR__) . '/includes/rbac.php';

if (!empty($_SESSION['api_token'])) {
    redirect(api_home_url());
}

redirect(base_url('public/login.php'));
