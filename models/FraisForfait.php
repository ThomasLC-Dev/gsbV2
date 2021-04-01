<?php

//////////////////////////////////////////
//                                      //
//          Model : FraisForfait        //
//                                      //
//////////////////////////////////////////

class FraisForfait{

    private $idUser;
    private $mois;
    private $idFraisForfait;
    private $quantite;

    public function __construct($idUser, $mois, $idFraisForfait, $quantite){
        $this->idUser = $idUser;
        $this->mois = $mois;
        $this->idFraisForfait = $idFraisForfait;
        $this->quantite = $quantite;
    }

    public function getIdUser(){
        return $this->idUser;
    }

    public function getMois(){
        return $this->mois;
    }

    public function getIdFraisForfait(){
        return $this->idFraisForfait;
    }

    public function getQuantite(){
        return $this->quantite;
    }

    public function setQuantite($quantite){
        $this->quantite = $quantite;
    }

}

?>