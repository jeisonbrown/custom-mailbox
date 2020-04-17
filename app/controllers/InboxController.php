<?php 

namespace Controller;

class InboxController extends \Core\Controller {

    public function getIndex(){
        return $this->render('inbox.index');
    }

    public function getDetail($id = null){
        return $this->render('inboxDetail.index');
    }

    public function getMain(){
        return $this->render('main');
    }

    public function getLayout(){
        return $this->render('inboxLayout');
    }
}