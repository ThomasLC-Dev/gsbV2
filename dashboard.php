<?php

//Session
session_start();
$userId = $_SESSION['id'];

//Includes
include_once('utils/Utils.php');
include_once('utils/Database.php');

$utils = new Utils();
$database = new Database();

//Login / Disconnect
if(!isset($_SESSION['login'])){
    header('Location: index.php');
}

if(isset($_POST['disconnect-btn'])){
    $utils->disconnect();
}

//Load Data

$currentMois = date('F');
$currentFicheFrais = null;

if($database->currentFicheFraisExists($userId, $currentMois)){
    $currentFicheFrais = $database->getFicheFrais($userId, date('F'));
    $currentFicheFrais->calculMontantTotal();
    $mois = $utils->convertToFr($currentFicheFrais->getMois());
    $etp = $currentFicheFrais->getArrFraisForfait()['ETP']->getQuantite();
    $km = $currentFicheFrais->getArrFraisForfait()['KM']->getQuantite();
    $nui = $currentFicheFrais->getArrFraisForfait()['NUI']->getQuantite();
    $rep = $currentFicheFrais->getArrFraisForfait()['REP']->getQuantite();
}
else{
    $database->removeFicheFrais($userId, $currentMois);
    $database->closeFicheFrais($userId);
    $database->initialiseFicheFrais($userId);
    header('Location: dashboard.php');
}

//Register Fiche Frais

if(isset($_POST['register'])){

    $etapes = htmlspecialchars($_POST['etapes']);
    $kilometres = htmlspecialchars($_POST['kilometres']);
    $nuits = htmlspecialchars($_POST['nuits']);
    $repas = htmlspecialchars($_POST['repas']);

    if(isset($etapes) && ($etapes>=0) && isset($kilometres) && ($kilometres>=0) && isset($nuits) && ($nuits>=0) && isset($repas) && ($repas>=0)){
        $etp = $currentFicheFrais->getArrFraisForfait()['ETP']->setQuantite($etapes);
        $km = $currentFicheFrais->getArrFraisForfait()['KM']->setQuantite($kilometres);
        $nui = $currentFicheFrais->getArrFraisForfait()['NUI']->setQuantite($nuits);
        $rep = $currentFicheFrais->getArrFraisForfait()['REP']->setQuantite($repas);
        $database->updateFicheFrais($currentFicheFrais);
        header('Location: dashboard.php');
    }
    else{
        $err_msg = 'Compléter tous les champs !';
    }
}

// Min and max for date picker
$min = date("Y-m-d", strtotime(date('Y').'-'.$mois.'-01'));
$max = date("Y-m-t", strtotime(date('Y').'-'.$mois.'-01'));

//Add Frais Hors Forfait
if(isset($_POST['add-frais-hors-forfait'])){
    $libelle = htmlspecialchars($_POST['libelle']);
    $date = htmlspecialchars($_POST['date']);
    $montant = htmlspecialchars($_POST['montant']);

    if(isset($libelle) && !empty($libelle) && isset($date) && !empty($date) && isset($montant) && !empty($montant)){
        $database->insertFraisHorsForfait($userId, $currentFicheFrais->getMois(), $libelle, $date, $montant);
        header('Location: dashboard.php');
    }
    else{
        $err_msg = 'Compléter tous les champs !';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Galaxy Swiss Bourdin</title>
    <link rel="stylesheet" href="style.css">
    <script src="js/script.js" defer></script>
</head>
<body class="dashboard">
    <div class="page">
        <header>
            <img src="img/logo-gsb.png" alt="GSB Logo" class="logo">
            <div class="header-right">
                <span><?= $_SESSION['name']?></span>
                <form method="POST">
                    <button type="submit" name="disconnect-btn"><img src="img/power.svg" alt="disconnect"></button>
                </form>
            </div>
        </header>
        <main>
            <div class="register-costs">
                <div class="title">
                    <h1>Renseigner ma fiche de frais</h1>
                    <h2><?php if(isset($mois)){ echo $mois; }else{ echo ''; }?></h2>
                </div>
                <form method="POST" action="">
                    <div class="form">
                        <div class="left">
                            <input type="number" placeholder="Forfait etape" name="etapes" value="<?php if(isset($etp)){ echo $etp; }else{ echo ''; }?>"><br>
                            <input type="number" placeholder="Frais kilométriques" name="kilometres" value="<?php if(isset($km)){ echo $km; }else{ echo ''; }?>">
                        </div>
                        <div class="right">
                            <input type="number" placeholder="Nuitée hôtel" name="nuits" value="<?php if(isset($nui)){ echo $nui; }else{ echo ''; }?>"><br>
                            <input type="number" placeholder="Repas restaurant" name="repas" value="<?php if(isset($rep)){ echo $rep; }else{ echo ''; }?>">
                        </div>
                    </div>
                    <div class="list">
                        <?php
                            foreach($currentFicheFrais->getArrFraisHorsForfait() as $fraisHorsForfait){
                                echo '<div class="element">';
                                echo '<span>'.$utils->convertToFrDate($fraisHorsForfait->getDate()).'</span>';
                                echo '<span>'.$fraisHorsForfait->getLibelle().'</span>';
                                echo '<span>'.$fraisHorsForfait->getMontant().'€</span>';
                                echo '<a href="delete-frais-hors-forfait.php?id='.$fraisHorsForfait->getId().'"><img src="img/delete.svg"></a>';
                                echo '</div>';
                            }
                        ?>
                    </div>
                    <span class="total"><?= $currentFicheFrais->getMontantValide()?>€</span>
                    <div class="buttons">
                        <button type="button" onclick="openPopup()">Frais hors forfait</butto>
                        <button type="submit" name="register">Enregistrer</button>
                        </div>
                </form>
                <span class="err-message"><?= isset($err_msg) ? $err_msg : '' ?></span>
            </div>
            <div class="consult-costs">
                <div class="title">
                    <h1>Consulter mes fiches de frais</h1>
                </div>
                <div class="list">
                    <?php
                        foreach($database->getAllFicheFrais($userId) as $ficheFrais){
                            echo '<div class="element">';
                            echo '<span>'.$utils->convertToFr($ficheFrais['mois']).'</span>';
                            echo '<span>Total : '.$ficheFrais['montantValide'].'€</span>';
                            echo '<a target="_blank" href="fiche-frais-details.php?mois='.$ficheFrais['mois'].'"><img src="img/eye.svg"></a>';
                            echo '</div>';
                        }
                    ?>
                </div>
            </div>
        </main>
    </div>
    <div class="popup">
        <div class="header">
            <h1 class="popup-title">Frais Hors Forfait</h1>
            <img src="img/close.svg" onclick="closePopup()">
        </div>
        <form method="POST" action="" id="frais-hors-forfait-form">
            <input type="text" placeholder="Libellé" name="libelle" required>
            <input type="date" placeholder="Date" name="date" min="<?= $min ?>" max="<?= $max ?>" required>
            <input type="number"  placeholder="Montant" name="montant" required>
            <button type="submit" name="add-frais-hors-forfait">Ajouter</button>
        </form>
    </div>
</body>
</html>