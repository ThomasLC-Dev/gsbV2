<?php

//////////////////////////////////////////
//                                      //
//          Class : Utils               //
//                                      //
//////////////////////////////////////////

class Utils{

    //Var to store the db
    private $bdd;
    //Var to store the error message for index.php
    public $err_msg;

    public function __construct()
    {
        $this->bdd = new PDO('mysql:host=localhost;dbname=gsbv2;charset=utf8', 'visiteur1', 'password');
    }

    //Function to check login and password and connect user
    function connect($login, $password){
    
        $login = htmlspecialchars($login);
        $password = htmlspecialchars($password);
    
        $req = $this->bdd->prepare('SELECT id, mdp, nom, prenom FROM visiteur WHERE login = :login');
        $req->execute(array(
            'login' => $login
        ));
        $result = $req->fetch();
    
        if(!$result){
            $this->err_msg = 'Identifiant inconnu !';
        }
        else{
            if($result['mdp'] == $password){
                $_SESSION['id'] = $result['id'];
                $_SESSION['login'] = $login;
                $_SESSION['name'] = strtoupper($result['nom']). ' ' .$result['prenom'];
                header('Location: dashboard.php');
            }
            else{
                $this->err_msg = 'Mot de passe incorrect !';
            }
        }
    }
    
    //Disconnect user
    function disconnect(){
        session_destroy();
        header('Location: index.php');
    }

    //Convert english month in french month
    function convertToFr($mois){
        $months = array(
            'January' => 'Janvier',
            'February' => 'Février',
            'March' => 'Mars',
            'April' => 'Avril',
            'May' => 'Mai',
            'June' => 'Juin',
            'July' => 'Juillet',
            'August' => 'Août',
            'September' => 'Septembre',
            'October' => 'Octobre',
            'November' => 'Novembre',
            'December' => 'Décembre'
        );

        return $months[$mois];
    }

    //Convert a "YYYY-MM-DD" format in a "DD FrenchMonth YYYY" format
    function convertToFrDate($date){
        $dateFormat = new DateTime($date);
        return $dateFormat->format('d'). " " . $this->convertToFr($dateFormat->format('F')) ." ".$dateFormat->format('Y');
    }
}

?>