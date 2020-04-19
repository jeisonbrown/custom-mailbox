<?php 

namespace Controller;

class AuthController extends \Core\Controller {


    public function __construct() {
        parent::__construct();
    }

    public function getLogin(){
        return $this->render('auth.login', $_GET);
    }

    public function getLogout(){
        session_destroy();
        return $this->redirect('/login');
    }

    public function postLogin(){
        $pass = sha1($_POST['password']);
        $strSQL="SELECT id, email, password, role_id from users WHERE email='{$_POST['email']}' and password='{$pass}'";
        $row = $this->db->query($strSQL)->single();
        
        if($row){
            $_SESSION['auth'] = true;
            $_SESSION['user']['id'] = $row['id'];
            $_SESSION['user']['name'] = $row['name'];
            $_SESSION['user']['email'] = $row['email'];
            $_SESSION['user']['role_id'] = $row['role_id'];
            return $this->redirect('/');
        }

        return $this->redirect('/login', [ "error" => true ]);
    }

    public function getSignup(){
        return $this->render('auth.signup');
    }

    public function getForgotPassword(){
        return $this->render('auth.forgotPassword');
    }

    public function getResetPassword(){
        return $this->render('auth.resetPassword');
    }
}