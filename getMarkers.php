<?php
require_once $_SERVER["DOCUMENT_ROOT"].'/DBconnect.php';

session_start();
$ip_hash = hash('md5', $_SERVER['REMOTE_ADDR'].'112233445566778899');
$is_admin = (array_key_exists('admin', $_SESSION) && $_SESSION['admin'] == $ip_hash);


$stmt = $dbConn->prepare('UPDATE markers SET aproved = ? WHERE id = ?');
if (array_key_exists('accept', $_GET) && is_numeric($_GET['accept'])) {
    $value = 1;
    $stmt->bind_param('ii', $value, $_GET['accept']);
    $stmt->execute();
} elseif (array_key_exists('reject', $_GET) && is_numeric($_GET['reject'])) {
    $value = 0;
    $stmt->bind_param('ii', $value, $_GET['reject']);
    $stmt->execute();
}
$stmt->close();


if (array_key_exists('id', $_GET) && is_numeric($_GET['id'])) {

    if ($is_admin) {
        $stmt = $dbConn->prepare('SELECT name, email, text, photo, aproved, nsfw FROM markers WHERE id = ? LIMIT 1');
    } else {
        $stmt = $dbConn->prepare('SELECT name, email, text, photo, aproved, nsfw FROM markers WHERE id = ? AND aproved = 1 LIMIT 1');
    }
    $stmt->bind_param('i', $_GET['id']);
    $stmt->execute();
    if ($dbConn->error != '') {
        error_log($dbConn->error);
        // echo json_encode(array('accepted'=>true, 'error'=>true, 'error_text'=>'DB error please contact the admin'));
        exit();
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $result->free();

    $row['text'] = nl2br(htmlspecialchars($row['text']));

    if ($row['aproved'] == 1 || $is_admin) {
        echo json_encode($row);
    }

} else {
    if (array_key_exists('aproved', $_GET) && $_GET['aproved'] == 0 && $is_admin) {
        $result = $dbConn->query('SELECT id, loc FROM markers WHERE aproved IS NULL');
    } else if (array_key_exists('aproved', $_GET) && $_GET['aproved'] == 1 && $is_admin) {
        $result = $dbConn->query('SELECT id, loc FROM markers WHERE aproved = 0');
    } else {
        $result = $dbConn->query('SELECT id, loc FROM markers WHERE aproved = 1');
    }

    if ($dbConn->error != '') {
        error_log($dbConn->error);
        echo json_encode(array('accepted'=>true, 'error'=>true, 'error_text'=>'DB error please contact the admin'));
        exit();
    }

    $markers = array();
    while ($row = $result->fetch_assoc()) {
        $markers[] = array('id'=>(integer)$row['id'], 'pos'=>json_decode($row['loc']));
    }
    echo json_encode($markers);
}