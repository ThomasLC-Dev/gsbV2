<?php

session_start();

include_once('utils/Utils.php');

$utils = new Utils();

//Redirect if already login
if(isset($_SESSION['login'])){
    header('Location: dashboard.php');
}
//When connect button click
if(isset($_POST['connect-btn'])){
    $utils->connect(htmlspecialchars($_POST['identifiant']), htmlspecialchars($_POST['password']));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Galaxy Swiss Bourdin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login">
    <div class="left">
        <img src="img/undraw_login.svg" alt="Dessin de 2 personnes du corps médical">
    </div>
    <div class="right">
        <img src="img/logo-gsb.png" alt="GSB Logo" class="logo">
        <div class="login-form">
            <form method="POST" action="">
                <div class="input-div">
                    <input type="text" name="identifiant" id="identifiant" required>
                    <label for="identifiant" class="label-name"><img src="img/user.svg">Identifiant</label>
                </div>
                <br>
                <div class="input-div">
                    <input type="password" name="password" id="password" required>
                    <label for="password" class="label-name"><img src="img/lock.svg">Mot de passe</label>
                </div>
                <br>
                <button type="submit" name="connect-btn">Se connecter</button>
            </form>
            <span class="err-message"><?= isset($utils->err_msg) ? $utils->err_msg : '' ?></span>
        </div>
    </div>
</body>
</html>