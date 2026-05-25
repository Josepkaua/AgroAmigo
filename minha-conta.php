<?php
declare(strict_types=1);
require_once 'includes/auth.php';
session_init();
header('Location: index.php');
exit;
