<?php

namespace Controller;

use \Core\Mailer;

class InboxController extends \Core\Controller
{

    private function sendMessageValidator($requiredFields)
    {
        $errors = [];
        foreach ($requiredFields as $field) {
            $_POST[$field] = trim($_POST[$field]);
            if (empty($_POST[$field])) {
                $errors[$field] = true;
            }
        }

        return $errors;
    }

    private function addEmailsToMailer($mailer)
    {
        $errors = [];
        $emailValues = [];
        $emailFields = ['to', 'reply', 'emailFrom', 'cc', 'bcc'];
        $requiredEmailFields = ['to', 'reply', 'emailFrom'];

        foreach ($emailFields as $field) {
            if (empty($_POST[$field]) || empty(trim($_POST[$field]))) {
                continue;
            }

            $values = explode(',', $_POST[$field]);
            foreach ($values as $value) {
                $email = trim($value);
                $isEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
                $inArray = !empty($emailValues[$field]) && in_array($email, $emailValues[$field]);

                if ($isEmail && !$inArray) {
                    switch ($field) {
                        case 'to':
                            $mailer->addAddress($email);
                            break;
                        case 'cc':
                            $mailer->addCC($email);
                            break;
                        case 'bcc':
                            $mailer->addBCC($email);
                            break;
                        case 'reply':
                            if (empty($emailValues[$field])) {
                                $mailer->addReplyTo($email, $_POST['nameFrom']);
                            }
                            break;
                        default:
                            break;
                    }

                    $emailValues[$field][] = $email;
                }
            }
        }

        foreach ($requiredEmailFields as $field) {
            if (empty($emailValues[$field])) {
                $errors[$field] = true;
            }
        }

        if (count($errors)) {
            return ['errors' => $errors];
        }

        return $mailer;
    }

    public function getIndex()
    {
        $strSQL="SELECT * FROM emails WHERE user_id = '{$_SESSION['USER_ID']}' AND inbox AND NOT deleted ORDER BY id DESC";
        $rsEmails = $this->db->query($strSQL)->resultset();
        return $this->render('inbox.index', $rsEmails);
    }

    public function getDetail($id = null)
    {
        return $this->render('inboxDetail.index');
    }

    public function sendMessage()
    {

        //Verifica los campos requeridos
        $requiredFields = ['to', 'reply', 'subject', 'message', 'emailFrom', 'nameFrom'];
        $errors = $this->sendMessageValidator($requiredFields);
        
        if (count($errors)) {
            return $this->redirect('/', [
                "errors" => $errors,
                "post" => $_POST,
            ]);
        }

        $mailer = new Mailer();
        $mailer = $this->addEmailsToMailer($mailer);

        if (is_array($mailer) && count($mailer['errors'])) {
            return $this->redirect('/', [
                "errors" => $mailer['errors'],
                "post" => $_POST,
            ]);
        }

        $mailer->setFrom($_POST['emailFrom'], $_POST['nameFrom']);
        $mailer->setSubject($_POST['subject']);
        $mailer->setBody($_POST['message']);

        if ($_FILES['attachment'] && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
            $mailer->AddAttachment(
                $_FILES['attachment']['tmp_name'],
                $_FILES['attachment']['name']
            );
        }

        $sended = $mailer->send();
        if ($sended) {
            $mailer->save();
            return $this->redirect('/');
        }

        return $this->redirect('/', [
            "errors" => [],
            "post" => $_POST,
        ]);
    }
}
