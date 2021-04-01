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

//Get the expense sheet with month in $_GET
$ficheFrais = $database->getFicheFrais($userId, $_GET['mois']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fiche Frais : <?= $utils->convertToFr($ficheFrais->getMois())?></title>
    <link rel="stylesheet" href="style.css">
    <script src="js/script.js" defer></script>
</head>
<body class="fiche-frais-details">
    <header>
        <img src="img/logo-gsb.png" alt="GSB Logo" class="logo">
    </header>
    <div class="content">
        <div class="fiche-details">
            <?php
                echo '<span class="etat '.$ficheFrais->getEtat().'">'.$database->getEtatLibelle($ficheFrais->getEtat()).'</span><br />';
                echo '<span class="mois">'.$utils->convertToFr($ficheFrais->getMois()).'</span>';
            ?>
        </div>
        <div class="frais-forfait">
            <?php
                $etp = $ficheFrais->getArrFraisForfait()['ETP']->getQuantite();
                $km = $ficheFrais->getArrFraisForfait()['KM']->getQuantite();
                $nui = $ficheFrais->getArrFraisForfait()['NUI']->getQuantite();
                $rep = $ficheFrais->getArrFraisForfait()['REP']->getQuantite();

                echo '<div>';
                echo '<span class="etapes"><img src="img/steps.svg">Etapes : '.$etp.'</span><br />';
                echo '<span class="kilometres"><img src="img/drive.svg">Kilomètres : '.$km.'</span><br />';
                echo '</div><div>';
                echo '<span class="nuits"><img src="img/bed.svg">Nuitée : '.$nui.'</span><br />';
                echo '<span class="repas"><img src="img/dinner.svg">Repas : '.$rep.'</span>';
                echo '</div>'
            ?>
        </div>
        <div class="frais-hors-forfait">
            <?php
                foreach($ficheFrais->getArrFraisHorsForfait() as $fraisHorsForfait){
                    echo '<div class="element">';
                    echo '<span>'.$utils->convertToFrDate($fraisHorsForfait->getDate()).'</span>';
                    echo '<span>'.$fraisHorsForfait->getLibelle().'</span>';
                    echo '<span>'.$fraisHorsForfait->getMontant().'€</span>';
                    echo '</div>';
                }
            ?>
        </div>
        <div class="fiche-frais-montant">
            <?php
                echo '<span class="montantValide">Total : '.$ficheFrais->getMontantValide().'€</span><br />';
            ?>
        </div>
    </div>
    <div class="fab" onclick="window.print();return false;">
        <img src="img/printer.svg">
    </div>
</body>