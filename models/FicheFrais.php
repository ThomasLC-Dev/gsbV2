<?php

include_once('utils/Database.php');

//////////////////////////////////////////
//                                      //
//          Model : FicheFrais          //
//                                      //
//////////////////////////////////////////
class FicheFrais{
    
    private $userId;
    private $mois;
    private $nbJustificatifs;
    private $montantValide;
    private $etat;
    private $arrFraisForfait;
    private $arrFraisHorsForfait;

    public function __construct($userId, $mois, $nbJustificatifs, $montantValide, $etat, $arrFraisForfait, $arrFraisHorsForfait){
        $this->userId = $userId;
        $this->mois = $mois;
        $this->nbJustificatifs =$nbJustificatifs;
        $this->montantValide = $montantValide;
        $this->etat = $etat;
        $this->arrFraisForfait =$arrFraisForfait;
        $this->arrFraisHorsForfait = $arrFraisHorsForfait;
    }

    public function getUserId(){
        return $this->userId;
    }

    public function getMois(){
        return $this->mois;
    }

    public function setMois($mois){
        $this->mois = $mois;
    }

    public function getNbJustificatifs(){
        return $this->nbJustificatifs;
    }

    public function setNbJustificatifs($nbJustificatifs){
        $this->nbJustificatifs = $nbJustificatifs;
    }

    public function getMontantValide(){
        return $this->montantValide;
    }

    public function setMontantValide($montantValide){
        $this->montantValide = $montantValide;
    }

    public function getEtat(){
        return $this->etat;
    }

    public function setEtat($etat){
        $this->etat = $etat;
    }

    public function getArrFraisForfait(){
        return $this->arrFraisForfait;
    }

    public function setArrFraisForfait($arrFraisForfait){
        $this->arrFraisForfait = $arrFraisForfait;
    }

    public function getArrFraisHorsForfait(){
        return $this->arrFraisHorsForfait;
    }

    public function setArrFraisHorsForfait($arrFraisHorsForfait){
        $this->arrFraisHorsForfait = $arrFraisHorsForfait;
    }

    //Calculation of all costs

    public function calculMontantTotal(){
        $database = new Database();
        $totalFraisForfait = 0;
        foreach($this->arrFraisForfait as $fraisForfait){
            $totalFraisForfait += ($fraisForfait->getQuantite()*$database->getDetailsForFraisForfait($fraisForfait->getIdFraisForfait())['montant']);
        }

        $totalFraisHorsForfait = 0;
        foreach($this->arrFraisHorsForfait as $fraisHorsForfait){
            $totalFraisHorsForfait += $fraisHorsForfait->getMontant();
        }

        $this->montantValide = $totalFraisForfait + $totalFraisHorsForfait;
    }
}

?>