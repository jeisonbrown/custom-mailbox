<?php

namespace Controller;

use Core\Mailer;
use Core\View;
use Controller\NotificationController;

class AuthController extends \Core\Controller
{


    public function __construct() {
        parent::__construct();
    }

    public function getLogin() {
        return $this->render('auth.login', $_GET);
    }

    public function getLogout() {
        session_destroy();
        return $this->redirect('/login');
    }

    public function postLogin() {
        $pass = sha1($_POST['password']);
        $strSQL = "SELECT id, email, password, role_id from users WHERE email='{$_POST['email']}' and password='{$pass}'";
        $row = $this->db->query($strSQL)->single();

        if ($row) {
            $_SESSION['AUTH'] = true;
            $_SESSION['USER_ID'] = $row['id'];
            $_SESSION['USER_NAME'] = $row['name'];
            $_SESSION['USER_EMAIL'] = $row['email'];
            $_SESSION['USER_ROLE'] = $row['role_id'];
            return $this->redirect('/');
        }

        return $this->redirect('/login', ["error" => true]);
    }

    public function getForgotPassword() {
        return $this->render('auth.forgotPassword');
    }

    public function postForgotPassword() {
        $email = trim($_POST['email']);
        if ($email) {
            $strSQL = "SELECT id, name FROM users WHERE email='{$email}' LIMIT 1";
            $rowUser = $this->db->query($strSQL)->single();
            if ($rowUser) {
                $token = sha1(time());
                $expirationDate = date('Y-m-d H:i:s', strtotime('+6 hours'));

                $strSQL = "UPDATE users SET token='{$token}', expiration_token='{$expirationDate}' WHERE id='{$rowUser['id']}' LIMIT 1";
                $this->db->query($strSQL)->execute();

                $view = new View();
                $template = $view->load('auth/resetEmail');
                $body = $template->render([
                    'url'   => $_SERVER['HTTP_ORIGIN'],
                    'name'  => $rowUser['name'],
                    'token' => $token
                ]);

                $mailer = new Mailer();
                $mailer->setFrom(getenv('SMTP_USERNAME'));
                $mailer->addAddress($email);
                $mailer->setSubject('Cambio de clave');
                $mailer->setBody($body);
                $mailer->send();
            }
        }
        return $this->redirect('/login');
    }

    public function getResetPassword($token = null) {
        
        $valid = false;
        $userId = 0;
        if(!$token && $_SESSION['AUTH']){
            $valid = true;
            $userId = $_SESSION['USER_ID'];
        }

        if($token){
            $date = date('Y-m-d H:i:s');
            $strSQL="SELECT id FROM users WHERE token='{$token}' AND expiration_token > '{$date}' limit 1";
            $rowUser = $this->db->query($strSQL)->single();
            if($rowUser) {
                $valid = true;
                $userId = $rowUser['id'];
            }
        }

        if(!$valid || !$userId){
            return $this->redirect('/forgot-password');
        }
        
        return $this->render('auth.resetPassword', [
            'token' => $token,
            'user_id' => $userId,
            'error' => isset($_GET['error']) ? $_GET['error'] : false
        ]);
    }

    public function postResetPassword() {

        $strSQL="SELECT id, password, token, email FROM users WHERE id='{$_POST['user_id']}' LIMIT 1";
        $rowUser = $this->db->query($strSQL)->single();

        $passwordIsValid = !empty($_POST['old_password']) && sha1($_POST['old_password']) === $rowUser['password'];
        $tokenIsValid = !empty($_POST['token']) && $_POST['token'] === $rowUser['token'];
        $passwordConfirmated = $_POST['password'] === $_POST['confirm_password'];
        
        if(!$passwordConfirmated || !$passwordIsValid){
            return $this->redirect("reset-password/{$_POST['token']}?error=true");
        }

        if($passwordIsValid || $tokenIsValid){
            $date = date('Y-m-d H:i:s');
            $password = sha1($_POST['confirm_password']);
            $strSQL="UPDATE users SET expiration_token='{$date}', updated_at='{$date}', password='{$password}' WHERE id='{$rowUser['id']}' LIMIT 1";
            $this->db->query($strSQL)->execute();
            $_POST['email'] = $rowUser['email'];
            $this->postLogin();
            NotificationController::send('Cambio de contraseña', 'Has cambiado tu contraseña de acceso!', '/', 'user');
            return true;
        }

        return $this->redirect('/');
    }
}