<?php
require_once $_SERVER["DOCUMENT_ROOT"].'/DBconnect.php';

session_start();
$ip_hash = hash('md5', $_SERVER['REMOTE_ADDR'].'112233445566778899');
$is_admin = (array_key_exists('admin', $_SESSION) && $_SESSION['admin'] == $ip_hash);
$photo = (!empty($_FILES['file']['tmp_name']));

$errors = array();


if (!array_key_exists('name', $_POST) || strlen($_POST['name']) == 0 ) {
    $errors['name'] = 'Cant be empty';
} elseif (strlen($_POST['name']) > 64) {
    $errors['name'] = 'Thats Too long';
}


if (!array_key_exists('email', $_POST) || strlen($_POST['email']) == 0 ) {
    $errors['email'] = 'Cant be empty';
} elseif (strlen($_POST['email']) > 64) {
    $errors['email'] = 'Thats Too long';
}


if (array_key_exists('text', $_POST) && strlen($_POST['text']) > 0 ) {
    if (strlen($_POST['text']) > 280) {
        $errors['text'] = 'Thats Too long<br>Nobody is going to read that';
    }
} else {
    $errors['text'] = 'Cant be empty';
}

if (!$is_admin) {
    if (array_key_exists('captcha', $_POST)) {
        if ($_POST['captcha'] != $_SESSION['captcha']) {
            $errors['captcha'] = 'wrong';
        }
    } else {
        $errors['captcha'] = 'Cant be empty';
    }

    $_SESSION['captcha'] = substr(md5(microtime()),mt_rand(0,26),4);

    if ($photo) {
        if ($_FILES['file']['size'] > 2097152) {
            $errors['file'] = 'File is too big Max 2Mib';
        }
        if (!in_array($_FILES['file']['type'], array('image/jpeg', 'image/png'))) {
            $errors['file'] = (empty($errors['file']) ? '' : $errors['file'].'<br>').'Only images allowd (jpeg or png)';
        }
        if (!isset($_FILES['file']['error']) || is_array($_FILES['file']['error'])) {
            $errors['file'] = (empty($errors['file']) ? '' : $errors['file'].'<br>').json_encode($_FILES['file']['error']);
        }
    }
}


if (array_key_exists('edit', $_POST) && is_numeric($_POST['edit'])) {
    if ($is_admin) {
        if (array_key_exists('pos', $_POST)) {
            $pos = json_decode($_POST['pos']);
            if (!is_array($pos) || sizeof($pos) != 2 || $pos[0] < -20037508 || $pos[0] > 20037508 || $pos[1] < -20037508 || $pos[1] > 20037508 ) {
                $errors['pos'] = 'invalid';
            }
        } else {
            $errors['pos'] = 'Cant be empty';
        }
    } else if (array_key_exists('password', $_POST) && strlen($_POST['password']) <= 64 && strlen($_POST['password']) >= 8) {

        $stmt = $dbConn->prepare('SELECT pass FROM markers WHERE id = ?');
        $stmt->bind_param('i', $_POST['edit']);
        $stmt->execute();
        if ($dbConn->error != '') {
            error_log($dbConn->error);
            echo json_encode(array('accepted'=>true, 'error'=>true, 'error_text'=>'DB error please contact the admin'));
            exit();
        }
        $stmt->bind_result($password);
        $stmt->fetch();
        $stmt->close();
        if ($password != $_POST['password']) {
            $errors['password'] = 'Wrong password';
        }
    } else {
        $errors['password'] = 'Wrong password';
    }
}
$nsfw = array_key_exists('nsfw', $_POST);


if (!empty($errors)) {
    echo json_encode(array('accepted'=>false, 'error'=>true, 'errors'=>$errors));
    exit();
}

if (array_key_exists('edit', $_POST) && is_numeric($_POST['edit'])) {
    if ($is_admin) {
        $stmt = $dbConn->prepare('UPDATE markers SET name = ?, email = ?, text = ?, nsfw = ? WHERE id = ?');
        $stmt->bind_param('sssii', $_POST['name'], $_POST['email'], $_POST['text'], $nsfw, $_POST['edit']);
        $stmt->execute();
        $stmt->close();
        if ($dbConn->error == '') {
            echo json_encode(array('accepted'=>true, 'error'=>false));
            exit();
        } else {
            error_log($dbConn->error);
            echo json_encode(array('accepted'=>true, 'error'=>true, 'error_text'=>'DB error please contact the admin'));
            exit();
        }
    } else {
        $stmt = $dbConn->prepare('INSERT INTO edits (parent_marker, name, email, text, photo, nsfw, loc) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('issssis', $_POST['edit'], $_POST['name'], $_POST['email'], $_POST['text'], $fileName, $nsfw, $_POST['pos']);
        $stmt->execute();
        $stmt->close();

        $lastinsert = $dbConn->insert_id;

        $stmt = $dbConn->prepare('DELETE FROM edits WHERE parent_marker = ? AND id <> ?');
        $stmt->bind_param('ii', $_POST['edit'], $lastinsert);
        $stmt->execute();
        $stmt->close();

        if ($dbConn->error == '') {
            echo json_encode(array('accepted'=>true, 'error'=>false));
            exit();
        } else {
            error_log($dbConn->error);
            echo json_encode(array('accepted'=>true, 'error'=>true, 'error_text'=>'DB error please contact the admin'));
            exit();
        }
    }
} else {
    $stmt = $dbConn->prepare('SELECT COUNT(*) FROM markers WHERE ip = ?');
    $stmt->bind_param('s', $ip_hash);
    $stmt->execute();
    if ($dbConn->error != '') {
        error_log($dbConn->error);
        echo json_encode(array('accepted'=>true, 'error'=>true, 'error_text'=>'DB error please contact the admin'));
        exit();
    }
    $stmt->bind_result($alreadyExists);
    $stmt->fetch();
    $stmt->close();
    if ($alreadyExists) {
        echo json_encode(array('accepted'=>true, 'error'=>true, 'error_text'=>'there already is a pin by this ip'));
        exit();
    }

    // $img = new Imagick();
    $fileName = null;
    if ($photo) {
        $temp = tempnam($_SERVER['DOCUMENT_ROOT'].'/images/', '');
        rename($_FILES['file']['tmp_name'], $temp);
        $fileName = basename($temp);
    }

    $stmt = $dbConn->prepare('INSERT INTO markers (name, email, text, photo, nsfw, loc, ip, pass) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssisss', $_POST['name'], $_POST['email'], $_POST['text'], $fileName, $nsfw, $_POST['pos'], $ip_hash, $_POST['password']);
    $stmt->execute();
    $stmt->close();
    if ($dbConn->error == '') {
        echo json_encode(array('accepted'=>true, 'error'=>false, 'id'=>$dbConn->insert_id));
        exit();
    } else {
        error_log($dbConn->error);
        echo json_encode(array('accepted'=>true, 'error'=>true, 'error_text'=>'DB error please contact the admin'));
        exit();
    }
}