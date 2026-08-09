<?php
require 'auth.php';
exigir_login();
header('Location: medicoes_listar.php');
exit;
