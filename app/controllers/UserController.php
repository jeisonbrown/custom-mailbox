<?php

namespace Controller;

use Controller\NotificationController;

class UserController extends \Core\Controller
{

    use \Controller\Traits\NotificationTrait;

    private function isAdmin() {
        return $_SESSION['USER_ROLE'] == 1 || $_SESSION['USER_ROLE'] == 2;
    }

    public function getUser($id = null) {

        $strQueryFilter = "and u.active and not u.deleted";
        //Si es el perfil del usuario logueado 
        //no se necesita hacer el filtro por activos y eliminados
        if(empty($id)){
            $id = $_SESSION['USER_ID'];
            $strQueryFilter = "";
        }
        
        $strSQL="SELECT u.*, r.name as roleName FROM users u LEFT JOIN roles r ON r.id = u.role_id WHERE u.id='{$id}' {$strQueryFilter} limit 1";
        $rowUser = $this->db->query($strSQL)->single();
        if(!$rowUser){
            return $this->redirect('/');
        }

        $strSQL="SELECT * FROM roles WHERE NOT deleted";
        $rowRoles = $this->db->query($strSQL)->resultset();

        $notifications = $this->getNotifications();

       
        return $this->render('users.profile', [
            'notifications' => $notifications,
            'user' => $rowUser,
            'roles' => $rowRoles,
            'admin' => $this->isAdmin(),
        ]);
    }

    public function postUser() {

        var_dump($_POST);
        if(!$this->isAdmin() && $_POST['user_id'] !== $_SESSION['USER_ID']){
            return $this->redirect('/');
        }

        if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->redirect('/profile', [
                'errors' => ['email' => 'No es un correo electrónico válido']
            ]);
        }

        $strSQL="SELECT id FROM users WHERE email='{$_POST['email']}' AND id <> '{$_POST['user_id']}'";
        $mailExists = $this->db->query($strSQL)->execRowCount();
        if($mailExists) {
            return $this->redirect('/profile', [
                'errors' => ['email' => 'El correo ya existe']
            ]);
        }

        $strSQL="SELECT id FROM users WHERE id='{$_POST['user_id']}'";
        $userExists = $this->db->query($strSQL)->execRowCount();
        $date = date('Y-m-d H:i:s');

        if($userExists){
            $queryArray=[];
            $queryArray[] = "`updated_at`='{$date}'";
            foreach($_POST as $key => $value ){
                if($key !== 'user_id'){
                    $queryArray[] = "`{$key}`='{$value}'";
                }
            }

            
            $strSQL="UPDATE users SET " . implode(',', $queryArray) . " WHERE id='{$_POST['user_id']}' LIMIT 1";
            $this->db->query($strSQL)->execute();
            NotificationController::send('Usuario actualizado', 'Información de usuario actualizada', '/', 'user', $_POST['user_id']);
        
        } else {
            
            $keys=[];
            $values=[];
            foreach($_POST as $key => $value ){
                if($key !== 'user_id'){
                    $keys[] = "`{$key}`";
                    $values[] ="'{$value}'";
                }
            }


            $strSQL="INSERT INTO users (" . implode(',', $keys) . ") VALUES (" . implode(',', $values) . ")";
            $this->db->query($strSQL)->execute();
            $user_id = $this->db->lastInsertId();

            $strSQL="INSERT INTO emails (id, user_id, subject, message, name, `from`, `to`, inbox, important, attachment, created_at) values (1, '{$user_id}', 'Bienvenido!', 'Bienvenido al sistema', 'Email', 'inbox@inbox.com', '{$_POST['email']}', 1, 1, 1, '{$date}');";
            $this->db->query($strSQL)->execute();
            $email_id = $this->db->lastInsertId();
            
            NotificationController::send('Usuario creado', 'Has creado un nuevo usuario', '/', 'user');
            NotificationController::send('Nuevo mensaje!', 'Has recibido un nuevo mensaje!', '/' . $email_id, 'received', $user_id);
        }

        return $this->redirect('/');
    }

    public function getUsers() {
        $strSQL="SELECT * FROM users WHERE NOT deleted";
        $rsUsers = $this->db->query($strSQL)->resultset();
        $this->render('users.list', [
            'users' => $rsUsers
        ]);
    }

}
