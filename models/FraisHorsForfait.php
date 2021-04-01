<?php

//////////////////////////////////////////
//                                      //
//       Model : FraisHorsForfait       //
//                                      //
//////////////////////////////////////////

class FraisHorsForfait{

    private $id;
    private $idUser;
    private $mois;
    private $libelle;
    private $date;
    private $montant;

    public function __construct($id, $idUser, $mois, $libelle, $date, $montant){
        $this->id = $id;
        $this->idUser = $idUser;
        $this->mois = $mois;
        $this->libelle = $libelle;
        $this->date = $date;
        $this->montant = $montant;
    }

    public function getId(){
        return $this->id;
    }

    public function getIdUser(){
        return $this->idUser;
    }

    public function getMois(){
        return $this->mois;
    }

    public function getLibelle(){
        return $this->libelle;
    }

    public function getDate(){
        return $this->date;
    }

    public function getMontant(){
        return $this->montant;
    }

}

?>