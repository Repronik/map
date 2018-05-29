<?php

session_start();
$ip_hash = hash('md5', $_SERVER['REMOTE_ADDR'].'112233445566778899');

$servername = 'localhost';
$username = 'cuteboy';
$password = 'uBIng01S7FuDvid6';
$dbname = 'cuteboyworld';

$dbConn = new mysqli($servername, $username, $password, $dbname, 3306);
if ($dbConn->connect_error) {
    die('Connection DB failed');
}
// var_dump(password_hash('1234', PASSWORD_BCRYPT));

if (array_key_exists('username', $_POST) && array_key_exists('password', $_POST)) {
    $stmt = $dbConn->prepare('SELECT name, password, login FROM admins WHERE name = ?');
    $stmt->bind_param('s', $_POST['username']);
    $stmt->execute();
    if ($dbConn->error != '') {
        error_log($dbConn->error);
        echo json_encode(array('error_text'=>'DB error please contact the admin'));
        exit();
    }
    $stmt->bind_result($name, $password, $lastLogin);
    $stmt->fetch();
    $stmt->close();
    if (password_verify($_POST['password'], $password)) {
        $_SESSION['admin'] = $ip_hash;
        setcookie("isAdmin", true, 0, '/');
    }
}

if (array_key_exists('logout', $_GET)) {
    unset($_SESSION['admin']);
    setcookie("isAdmin", false, time() - 3600, '/');
    header('Location: https://'.$_SERVER['SERVER_NAME']);
    exit();
}


if (array_key_exists('admin', $_SESSION) && $_SESSION['admin'] == $ip_hash) {
    echo '<a href="?logout">logout</a></br>';

    foreach (scandir($_SERVER["DOCUMENT_ROOT"].'/images') as $img) {
        if ($img != '.' && $img != '..') {
            echo '<img src="/images/'.$img.'"></br>'.$img.'<br><br>';
        }
    }

    // $stmt = $dbConn->prepare('SELECT COUNT(*) FROM markers WHERE ip = ?');
    // $stmt->bind_param('s', $ip_hash);
    // $stmt->execute();
    // if ($dbConn->error != '') {
    //     error_log($dbConn->error);
    //     echo json_encode(array('accepted'=>true, 'error'=>true, 'error_text'=>'DB error please contact the admin'));
    //     exit();
    // }
    // $stmt->bind_result($alreadyExists);
    // $stmt->fetch();
    // $stmt->close();
} else {

    ?>
    <h1>admin login</h1>
    <form method="post" >
        <table>
            <tr>
                <td>Username:</td>
                <td><input type="text" name="username"></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input type="password" name="password"></td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" value="submit"></td>
            </tr>
        </table>
    </form>
    <?php

}