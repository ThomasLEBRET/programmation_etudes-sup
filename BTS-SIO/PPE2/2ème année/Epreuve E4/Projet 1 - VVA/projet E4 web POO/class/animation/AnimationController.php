<?php

require_once("Animation.php");

class AnimationController extends Animation {

    private $animation;

    public function __construct() {
        $this->animation = new Animation();
    }

    /**
    * Renvoi la liste des animations valides
    * @return void demande la vue associée
    */
    public function voirAnimations() {
        if(!empty(Session::get('TYPEPROFIL')) && Session::get('TYPEPROFIL') == 'EN') {
            $anims = $this->animation->getAllAnimationForEncadrant();
        } else {
            $anims = $this->animation->getAnimationsValides();
        }
        require('view/animation/animations.php');
    }

    public function addAnimation($dataPost) {
        if(!empty(Session::get('TYPEPROFIL')) && Session::get('TYPEPROFIL') == 'EN') {
            $this->animation->buildObject($dataPost);
            $this->animation->setDatecreationanim(date('Y-m-d'));
            if($this->animation->allFiledsIsIsset($dataPost)) {
                if($this->animation->isUniqueCodeAnim($this->animation->getCodeanim())) {
                    if($this->animation->getDatecreationanim() == date('Y-m-d')) {
                        if($this->animation->getDatevaliditeanim() >= date('Y-m-d')) {
                            if($this->animation->getDureeanim() > 0) {
                                if($this->animation->getLimiteage() > 0) {
                                    if($this->getTarifanim() >= 0) { //Tarif peut etre gratuit
                                        if($this->animation->getNbreplaceanim() > 0) {
                                            $this->animation->ajouterAnimation();
                                            require('view/animation/success/successInsertAnimation.php');
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

        } else {
            require('view/animation/errors/errorAccess.php');
        }
    }

    public function deleteAnimation($cdAnim) {
        if(!empty(Session::get('TYPEPROFIL')) && Session::get('TYPEPROFIL') == 'EN') {
          $this->animation->getAnimation($cdAnim);
          if($this->animation->annuleInscriptions($cdAnim)) {
            if($this->animation->annuleActivites($cdAnim)) {
              if($this->animation->annuleAnimation($cdAnim)) {
                echo "success";
                die();
              }
            }
          }

            // Annule les inscriptions aux activités issues de cette animation
            // Annule les animations de cette activité
            // Annule l'animation
        } else {
            require('view/animation/errors/errorAccess.php');
        }
    }

    public function updateAnimation() {
        $oldCodeAnim = $_POST['oldCodeAnim'];
        unset($_POST['oldCodeAnim']);
        if(!empty(Session::get('TYPEPROFIL')) && Session::get('TYPEPROFIL') == 'EN') {
            $this->animation->getAnimation($oldCodeAnim, $_POST); //récupère l'ancienne animation
            $this->animation->buildPartialObject($_POST);

            if($this->animation->allFiledsIsIsset($_POST)) {
                if($this->animation->isUniqueCodeAnim($this->animation->getCodeanim(), $oldCodeAnim)) {
                    if($this->animation->getDatecreationanim() == date('Y-m-d')) {
                        if($this->animation->getDatevaliditeanim() >= date('Y-m-d')) {
                            if($this->animation->getDureeanim() > 0) {
                                if($this->animation->getLimiteage() > 0) {
                                    if($this->getTarifanim() >= 0) { //Tarif peut etre gratuit
                                        if($this->animation->getNbreplaceanim() > 0) {
                                            $this->animation->modifierAnimation($oldCodeAnim);
                                            require('view/animation/success/successUpdateAnimation.php');
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else echo "error";

        } else {
            require('view/animation/errors/errorAccess.php');
        }
    }

}
