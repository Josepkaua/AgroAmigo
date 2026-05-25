<?php
declare(strict_types=1);
require_once 'includes/auth.php';

session_init();

if (usuario_logado()) {
    logout_usuario();
}

flash('success', 'Você saiu da sua conta.');
header('Location: login.php');
exit;
