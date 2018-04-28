<?php
session_start();
$ip_hash = hash('md5', $_SERVER['REMOTE_ADDR'].'112233445566778899');
$is_admin = (array_key_exists('admin', $_SESSION) && $_SESSION['admin'] == $ip_hash);

echo (int)$is_admin;