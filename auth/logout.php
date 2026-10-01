<?php
session_start();
session_destroy();
header('Location: /gestion-scolaire/auth/login.php');
exit;