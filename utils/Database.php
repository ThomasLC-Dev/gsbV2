<?php

include_once('models/FicheFrais.php');
include_once('models/FraisForfait.php');
include_once('models/FraisHorsForfait.php');


//////////////////////////////////////////
//                                      //
//          Class : Database            //
//                                      //
//////////////////////////////////////////

class Database{

    //Var to store the db
    private $bdd;

    public function __construct()
    {
        $this->bdd = new PDO('mysql:host=localhost;dbname=gsbv2;charset=utf8', 'visiteur1', 'password');
    }
    
    //Initiates a new expense sheet
    function initialiseFicheFrais($id){
        $mois = date('F');

        $req = $this->bdd->prepare('INSERT INTO fichefrais VALUES(:id_user,:mois,0,0,now(),"CR")');
        $req->execute(array(
            'id_user' => $id,
            'mois' => $mois
        ));

        $this->initialiseFraisForfait($id, $mois);
    }

    //Initialise the 4 flat fee lines
    function initialiseFraisForfait($id, $mois){
        $req = $this->bdd->prepare('INSERT INTO lignefraisforfait VALUES(:id_user,:mois, "ETP", 0), (:id_user,:mois, "KM", 0), (:id_user,:mois, "NUI", 0), (:id_user,:mois, "REP", 0)');
        $req->execute(array(
            'id_user' => $id,
            'mois' => $mois
        ));
    }

    //Closing a expense sheet
    function closeFicheFrais($id){
        $currentMois = date('F');
        $lastMois = Date('F', strtotime($currentMois . ' last month'));
        $req = $this->bdd->prepare('SELECT mois FROM fichefrais WHERE idVisiteur = :id_user AND mois = :mois');
        $req->execute(array(
            'id_user' => $id,
            'mois' => $lastMois
        ));
        $result = $req->fetch();

        if($result){
            $req = $this->bdd->prepare('UPDATE fichefrais SET idEtat = "CL" WHERE idVisiteur=:id_user AND mois = :mois');
            $req->execute(array(
                'id_user' => $id,
                'mois' => $lastMois
            ));
        }
    }
    
    //Update an expense sheet
    function updateFicheFrais($ficheFrais){
        $ficheFrais->calculMontantTotal();
        $req = $this->bdd->prepare('UPDATE fichefrais SET montantValide = :montantValide, dateModif = now() WHERE idVisiteur=:id_user AND idEtat="CR"');
        $req->execute(array(
            'montantValide' => $ficheFrais->getMontantValide(),
            'id_user' => $ficheFrais->getUserId()
        ));
        $this->updateLigneFraisForfait($ficheFrais);
    }
    
    //Update flat fee line
    function updateLigneFraisForfait($ficheFrais){
        foreach($ficheFrais->getArrFraisForfait() as $line){
            $req = $this->bdd->prepare('UPDATE lignefraisforfait SET quantite = :quantite WHERE idVisiteur=:id_user AND mois=:mois AND idFraisForfait=:id_frais_forfait');
            $req->execute(array(
                'quantite' => $line->getQuantite(),
                'id_user' => $ficheFrais->getUserId(),
                'mois' => $ficheFrais->getMois(),
                'id_frais_forfait' => $line->getIdFraisForfait()
            ));
        }
    }

    //Get all expense sheets
    function getAllFicheFrais($id){
        $req = $this->bdd->prepare('SELECT mois, montantValide FROM fichefrais WHERE idVisiteur = :id_user AND idEtat != "CR" ORDER BY dateModif DESC');
        $req->execute(array(
            'id_user' => $id
        ));
        $result = $req->fetchAll();

        $arrFicheFrais = array();

        foreach($result as $line){
            $arrFicheFrais[$line['mois']] = array(
                'id' => $id,
                'mois' => $line['mois'],
                'montantValide' => $line['montantValide'],
            );
        }

        return $arrFicheFrais;   
    }
    
    //Checks if there is a current expense sheet
    function currentFicheFraisExists($id, $mois){
        $req = $this->bdd->prepare('SELECT mois, montantValide FROM fichefrais WHERE idVisiteur = :id_user AND idEtat = "CR" AND mois = :mois');
        $req->execute(array(
            'id_user' => $id,
            'mois' => $mois
        ));
        $result = $req->fetch();

        return (!$result) ? false : true;
    }
    
    //Remove an old expense sheet
    function removeFicheFrais($id, $mois){
        $req = $this->bdd->prepare('SELECT mois FROM fichefrais WHERE idVisiteur = :id_user AND mois = :mois');
        $req->execute(array(
            'id_user' => $id,
            'mois' => $mois
        ));
        $result = $req->fetch();

        if($result){
            $this->removeAllFraisForfait($id, $mois);
            $this->removeAllFraisHorsForfait($id, $mois);

            $reqDelete = $this->bdd->prepare('DELETE FROM fichefrais WHERE idVisiteur = :id_user AND mois = :mois');
            $reqDelete->execute(array(
                'id_user' => $id,
                'mois' => $mois
            ));
        }
    }

    //Remove all flat fee lines for an expense sheet
    function removeAllFraisForfait($id, $mois){
        $reqDelete = $this->bdd->prepare('DELETE FROM lignefraisforfait WHERE idVisiteur = :id_user AND mois = :mois');
        $reqDelete->execute(array(
            'id_user' => $id,
            'mois' => $mois
        ));
    }

    //Remove all out-of-pocket expenses for an expense sheet
    function removeAllFraisHorsForfait($id, $mois){
        $reqDelete = $this->bdd->prepare('DELETE FROM lignefraishorsforfait WHERE idVisiteur = :id_user AND mois = :mois');
        $reqDelete->execute(array(
            'id_user' => $id,
            'mois' => $mois
        ));
    }

    //Get all data of an expense sheet
    function getFicheFrais($id, $mois){
        $req = $this->bdd->prepare('SELECT * FROM fichefrais WHERE idVisiteur = :id_user AND mois = :mois');
        $req->execute(array(
            'id_user' => $id,
            'mois' => $mois
        ));

        $result = $req->fetch();

        $ficheFrais = new FicheFrais($result['idVisiteur'], $result['mois'], $result['nbJustificatifs'], $result['montantValide'], $result['idEtat'], $this->getAllFraisForfait($id, $mois), $this->getAllFraisHorsForfait($id, $mois));
        return $ficheFrais;
    }

    //Get all flat fee lines for an expense sheet
    function getAllFraisForfait($id, $mois){
        $req = $this->bdd->prepare('SELECT * FROM lignefraisforfait WHERE idVisiteur = :id_user AND mois = :mois');
        $req->execute(array(
            'id_user' => $id,
            'mois' => $mois
        ));
        $result = $req->fetchAll();

        $arrFraisForfait = array();

        foreach($result as $line){
            $fraisForfait = new FraisForfait($line['idVisiteur'],$line['mois'],$line['idFraisForfait'],$line['quantite']);
            $arrFraisForfait[$line['idFraisForfait']] = $fraisForfait;
        }

        return $arrFraisForfait;
    }

    //Get all out-of-pocket expenses for an expense sheet
    function getAllFraisHorsForfait($id, $mois){
        $req = $this->bdd->prepare('SELECT * FROM lignefraishorsforfait WHERE idVisiteur = :id_user AND mois = :mois ORDER BY date ASC');
        $req->execute(array(
            'id_user' => $id,
            'mois' => $mois
        ));
        $result = $req->fetchAll();

        $arrFraisHorsForfait = array();

        foreach($result as $line){
            $fraisHorsForfait = new FraisHorsForfait($line['id'],$line['idVisiteur'],$line['mois'],$line['libelle'],$line['date'],$line['montant']);
            $arrFraisHorsForfait[] = $fraisHorsForfait;
        }

        return $arrFraisHorsForfait;
    }

    //Get details for a flat fee
    function getDetailsForFraisForfait($id){
        $req = $this->bdd->prepare('SELECT * FROM fraisforfait WHERE id = :id_frais_forfait');
        $req->execute(array(
            'id_frais_forfait' => $id
        ));
        $result = $req->fetch();

        return $result;
    }

    //Get label for a state
    function getEtatLibelle($idEtat){
        $req = $this->bdd->prepare('SELECT libelle FROM etat WHERE id = :id_etat');
        $req->execute(array(
            'id_etat' => $idEtat
        ));
        $result = $req->fetch();

        return $result['libelle'];
    }

    //Insert out-of-pocket expense
    function insertFraisHorsForfait($id, $mois, $libelle, $date, $montant){
        $req = $this->bdd->prepare('INSERT INTO lignefraishorsforfait VALUES(0,:id_user,:mois,:libelle,:date_frais,:montant)');
        $req->execute(array(
            'id_user' => $id,
            'mois' => $mois,
            'libelle' => $libelle,
            'date_frais' => $date,
            'montant' => $montant
        ));
    }
    
    //Delete out-of-pocket expense
    function deleteFraisHorsForfait($id, $frais_hors_forfait_id){
        $req = $this->bdd->prepare('SELECT idVisiteur FROM lignefraishorsforfait WHERE id=:id');
        $req->execute(array(
            'id' => $frais_hors_forfait_id
        ));
        $result = $req->fetch();

        if($result){
            if($id == $result['idVisiteur']){
                $req = $this->bdd->prepare('DELETE FROM lignefraishorsforfait WHERE id=:id');
                $req->execute(array(
                    'id' => $frais_hors_forfait_id
                ));
            }
        }
    }
}

?>