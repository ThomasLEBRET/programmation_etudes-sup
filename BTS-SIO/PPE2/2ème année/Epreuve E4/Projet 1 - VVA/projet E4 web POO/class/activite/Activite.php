<?php

require_once("class/datas/Database.php");

class Activite extends Database {
  private $noact;
  private $codeanim;
  private $codeetatact;
  private $dateact;
  private $hrrdvact;
  private $prixact;
  private $hrdebutact;
  private $hrfinact;
  private $dateannuleact;
  private $nomresp;
  private $prenomresp;

  public function __construct() {
    $noact = "unknow";
    $codeanim = "unknow";
    $codeetatact = "unknow";
    $dateact = "unknow";
    $hrrdvact = "unknow";
    $prixact = "unknow";
    $hrdebutact = "unknow";
    $hrfinact = "unknow";
    $dateannuleact = "unknow";
    $nomresp = "unknow";
    $prenomresp = "unknow";
  }


  public function getNoact(){
    return $this->noact;
  }

  public function setNoact($noact){
    $this->noact = $noact;
  }

  public function getCodeanim(){
    return $this->codeanim;
  }

  public function setCodeanim($codeanim){
    $this->codeanim = $codeanim;
  }

  public function getCodeetatact(){
    return $this->codeetatact;
  }

  public function setCodeetatact($codeetatact){
    $this->codeetatact = $codeetatact;
  }

  public function getDateact(){
    return $this->dateact;
  }

  public function setDateact($dateact){
    $this->dateact = $dateact;
  }

  public function getHrrdvact(){
    return $this->hrrdvact;
  }

  public function setHrrdvact($hrrdvact){
    $this->hrrdvact = $hrrdvact;
  }

  public function getPrixact(){
    return $this->prixact;
  }

  public function setPrixact($prixact){
    $this->prixact = $prixact;
  }

  public function getHrdebutact(){
    return $this->hrdebutact;
  }

  public function sethrdebutact($hrdebutact){
    $this->hrdebutact = $hrdebutact;
  }

  public function getHrfinact(){
    return $this->hrfinact;
  }

  public function setHrfinact($hrfinact){
    $this->hrfinact = $hrfinact;
  }

  public function getDateannuleact(){
    return $this->dateannuleact;
  }

  public function setDateannuleact($dateannuleact){
    $this->dateannuleact = $dateannuleact;
  }

  public function getNomresp(){
    return $this->nomresp;
  }

  public function setNomresp($nomresp){
    $this->nomresp = $nomresp;
  }

  public function getPrenomresp(){
    return $this->prenomresp;
  }

  public function setPrenomresp($prenomresp){
    $this->prenomresp = $prenomresp;
  }

  /**
   * Construit un objet grâce à ses mutateurs associés
   * @param  array  $params une liste de paramètres correspondant aux champs associés à l'entité de l'objet et aux noms de mutateurs
   * @return Activite   retourne l'objet créé $this
   */
  public function buildObject(array $params) {
    foreach ($params as $key => $value) {
      $method = 'set'.ucfirst(strtolower($key));
      if(!empty($value)) {
        if(method_exists($this, $method)) {
          $this->$method($value);
        }
      }
    }
    return $this;
  }


  public function ajouteActivite($cdAnim) {
    $req = 
    "
    INSERT INTO activite(NOACT, CODEANIM, CODEETATACT, DATEACT, HRRDVACT, PRIXACT, HRDEBUTACT, HRFINACT, DATEANNULEACT, NOMRESP, PRENOMRESP)
    VALUES(?,?,'O',?,?,?,?,?,NULL,?,?)
    ";
    $params = 
    [
      $this->noact, $this->codeanim, $this->dateact, $this->hrrdvact, $this->prixact,
      $this->hrdebutact, date('h:i:s', strtotime($this->hrfinact)), Session::get('NOMCOMPTE'), Session::get('PRENOMCOMPTE')
    ];
    return $this->createQuery($req, $params) == true;
  }

  public function existeAnimationPourCetteDate($date, $cdAnim) {
    $req = "SELECT COUNT(*) as nbActDt FROM activite WHERE DATEACT = ? AND CODEANIM = ?";
    return $this->createQuery($req, [$date, $cdAnim])->fetch(PDO::FETCH_ASSOC)["nbActDt"] == 0;
  }

  public function annulerActiviter($noAct) {
    $req = "UPDATE activite SET CODEETATACT = 'N' WHERE NOACT = ?";
    return $this->createQuery($req ,[$noAct]) == true;
  }

  /**
  * Récupère les activités liés à une animation
  * @param  string $cdAnimation PK ANIMATION (code d'animation)
  * @return $req              un résultat de requête préparée
  */
  public function getActivitesValides($cdAnimation) {
    $req = "
    SELECT *, HOUR(A.HRRDVACT) as hourRdvAct, MINUTE(A.HRRDVACT) as minRdvAct, HOUR(A.HRDEBUTACT) as hourDebAct, MINUTE(A.HRDEBUTACT) as minDebAct
    FROM ACTIVITE A, ANIMATION AN
    WHERE A.CODEANIM = AN.CODEANIM
    AND AN.CODEANIM = ?
    AND A.CODEETATACT = 'O'
    AND A.DATEANNULEACT IS NULL
    AND A.DATEACT > DATE(NOW())";
    return $this->createQuery($req, [$cdAnimation]);
  }

  /**
   * Vérifie si un utilisateur est inscrit à une activité et n'a pas annulé son inscription
   * @param  string $user  le pseudo utilisateur PK COMPTE
   * @param  int $noAct le numéro d'activité lié à une animation PK ACTIVITE
   * @return bool        retourne vrai si l'utilisateur est inscrit à une ou plusieurs activités. False sinon
   */
  public function estInscritActivite($user, $cdAnim, $noAct) {
    $req =
    "
    SELECT I.*
    FROM INSCRIPTION I, ACTIVITE A, ANIMATION AN
    WHERE I.NOACT = A.NOACT
    AND A.CODEANIM = AN.CODEANIM
    AND I.USER = ?
    AND A.CODEANIM = ?
    AND I.NOACT = ?
    AND I.DATEANNULE IS NULL  
    ";
    $res = $this->createQuery($req, [Session::get('USER'), $cdAnim, $noAct])->fetch(PDO::FETCH_ASSOC);
    if($res) {
        if(($res['DATEANNULE'] == NULL && $res['DATEINSCRIP'] != NULL))
          return true;
    }
    return false;
  }


  /**
   * Vérifie si un utilisateur ayant une ligne pour une inscription données peut se réinscrire ou non
   * @param  string $user  le pseudo utilisateur (PK COMPTE)
   * @param  int $noAct le numéro d'activité lié à une animation (PK ACTIVITE)
   * @return bool        retourne vrai si l'utilisateur est inscrit à une ou plusieurs activités. False sinon
   */
  public function peutSeReinscrire($user, $cdAnim, $noAct) {
        $req =
        "
        SELECT I.*
        FROM INSCRIPTION I, ACTIVITE A, ANIMATION AN
        WHERE A.NOACT = I.NOACT
        AND AN.CODEANIM = A.CODEANIM
        AND I.USER = ?
        AND A.CODEANIM = ?
        AND A.NOACT = ?
        ";
        $res = $this->createQuery($req, [$user, $cdAnim, $noAct]);
        if($res) {
            if($res->rowCount() == 1 && $res->fetch(PDO::FETCH_ASSOC)['DATEANNULE'] != NULL) {
                return true;
            }
        }
      return false;
  }

  /**
   * Réalise une insertion d'inscription pour un utilisateur
   * @param  string $user   PK COMPTE
   * @param  string $cdAnim    PK ANIMATION
   * @param  int $noAct  PK ACTIVITE
   * @return bool         retourne vrai si une inscription a été insérée pour un utilisateur à une activité valide
   */
  public function execInscription($user, $cdAnim, $noAct) {
    if($this->peutSeReinscrire($user, $cdAnim, $noAct)) {
      $req = "
      UPDATE INSCRIPTION
      SET DATEANNULE = NULL, DATEINSCRIP = DATE(NOW())
      WHERE USER = ?
      AND NOACT = ?
      ";
      if($this->createQuery($req, [$user, $noAct]))
        return true;
    } else {
      $req = "
      INSERT INTO INSCRIPTION(USER, NOACT, DATEINSCRIP, DATEANNULE)
      VALUES
      (
          ?,
          ?,
          ?,
          NULL
      )";
      if($this->createQuery($req, [$user, $noAct, date("Y-m-d")]))
        return true;
    }
    return false;
  }

  /**
   * Annule une inscription à une activité concernant une inscription déjà existante
   * @param  string $user   PK COMPTE
   * @param  string $cdAnim PK ANIMATION
   * @param  int $noAct  PK ACTIVITE
   * @return bool         vrai si l'inscription à l'activité concernée à été annulée, false sinon
   */
  public function annuleInscription($user, $cdAnim, $noAct) {
    if($this->estInscritActivite($user, $cdAnim, $noAct)) {
      $req = "
      UPDATE INSCRIPTION
      SET DATEANNULE = ?
      WHERE NOACT = ?
      AND USER = ?
      ";
      if($this->createQuery($req, [date("Y-m-d"), $noAct, $user]))
        return true;
    }
    return false;
  }

  /**
   * Retourne la liste des activités
   * @return  PDO requête préparée au format PDO
   */
  public function getActivitesInscrit() {
    $req =
    "
    SELECT A.*, HOUR(TIMEDIFF(A.HRFINACT, A.HRDEBUTACT)) as hrDureeAnim, MINUTE(TIMEDIFF(A.HRFINACT, A.HRDEBUTACT)) as minDureeAnim, AN.NOMANIM, CONCAT(A.PRIXACT, '€') as PRIXACT, HOUR(A.HRRDVACT) as hourRdvAct, MINUTE(A.HRRDVACT) as minRdvAct, HOUR(A.HRDEBUTACT) as hourDebAct, MINUTE(A.HRDEBUTACT) as minDebAct
    FROM INSCRIPTION I, ACTIVITE A, ANIMATION AN
    WHERE AN.CODEANIM = A.CODEANIM
    AND I.NOACT = A.NOACT
    AND I.USER = ?
    AND I.DATEANNULE IS NULL
    ";
    return $this->createQuery($req, [Session::get('USER')]);
  }

  public function getListeInscrits($noAct) {
    $req = "SELECT USER FROM inscription WHERE NOACT = ? AND DATEANNULE IS NULL";
    return $this->createQuery($req, [$noAct]);
  }

  /**
   * Retourne l'ensemble des activités pour un encadrant
   * @return PDO Une requête préparée
   */
  public function getActivitesEncadrant() {
    $req =
    "
    SELECT A.*, HOUR(TIMEDIFF(A.HRFINACT, A.HRDEBUTACT)) as hrDureeAnim, MINUTE(TIMEDIFF(A.HRFINACT, A.HRDEBUTACT)) as minDureeAnim, AN.NOMANIM, CONCAT(A.PRIXACT, '€') as PRIXACT, HOUR(A.HRRDVACT) as hourRdvAct, MINUTE(A.HRRDVACT) as minRdvAct, HOUR(A.HRDEBUTACT) as hourDebAct, MINUTE(A.HRDEBUTACT) as minDebAct
    FROM  ACTIVITE A, ANIMATION AN
    WHERE A.CODEANIM = AN.CODEANIM
    ";
    return $this->createQuery($req);
  }

  public function getAllActivitesForEncadrant($cdAnim) {
      $req =
      "
      SELECT A.*, HOUR(TIMEDIFF(A.HRFINACT, A.HRDEBUTACT)) as hrDureeAnim, MINUTE(TIMEDIFF(A.HRFINACT, A.HRDEBUTACT)) as minDureeAnim, AN.NOMANIM, CONCAT(A.PRIXACT, '€') as PRIXACT, HOUR(A.HRRDVACT) as hourRdvAct, MINUTE(A.HRRDVACT) as minRdvAct, HOUR(A.HRDEBUTACT) as hourDebAct, MINUTE(A.HRDEBUTACT) as minDebAct
      FROM ACTIVITE A, ANIMATION AN
      WHERE A.CODEANIM = AN.CODEANIM
      AND A.CODEANIM = ?
      ";
      return $this->createQuery($req, [$cdAnim]);
  }

  public function estNumValide() {
    $req = "SELECT NOACT FROM activite WHERE NOACT = ?";
    return $this->createQuery($req, [$this->noact])->fetch(PDO::FETCH_ASSOC)["NOACT"] != $this->noact;
  }
}
