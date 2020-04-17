<?php 

namespace Controller;

class InboxController extends \Core\Controller {

    public function getIndex(){
        return $this->render('inbox');
    }
}