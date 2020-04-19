<?php

namespace Controller;

use Core\Mailer;
use Core\Date;

class InboxController extends \Core\Controller
{

    use \Controller\Traits\InboxTrait;
    use \Controller\Traits\InboxSendMessageTrait;
    use \Controller\Traits\InboxDetailTrait;

    public function downloadAttachment($email_id, $file){

        $fileRoute = $_SERVER['DOCUMENT_ROOT']."/../uploads/attachments/{$file}";
        if(file_exists($fileRoute)){
            $filetype=filetype($fileRoute);
            $filename=basename($fileRoute);
            header ("Content-Type: ".$filetype);
            header ("Content-Length: ".filesize($fileRoute));
            header ("Content-Disposition: attachment; filename=".$filename);
            readfile($fileRoute);
        } else {
            header("Location: /{$email_id}");
        }
    }

    public function getIndex() {

        $this->rows = intval(getenv('TABLE_ROWS_BY_PAGE', 10));
        $this->applyFilters();
        $this->setNotViewed();
        $this->setTotalRows();
        $this->setMaxPages();
        $this->setCurrentPage();
        $rsEmails = $this->getFilteredEmails();

        foreach ($rsEmails as $key => $rowEmail) {
            $rsEmails[$key]['created_at'] = Date::format($rowEmail['created_at']);
        }

        return $this->render('inbox.index', [
            'emails' => $rsEmails,
            'page' => $this->currentPage,
            'rows' => $this->rows * $this->currentPage,
            'maxPages' => $this->maxPages,
            'totalRows' => $this->totalRows,
            'type' => $this->type,
            'state' => $this->state,
            'typeName' => $this->getTypeName(),
            'notViewed' => $this->notViewed
        ]);
    }

    private function getEmailData($id){
        $strSQL="SELECT * FROM emails WHERE id = '{$id}' LIMIT 1";
        $rowEmail = $this->db->query($strSQL)->single();
        $rowEmail['created_at_human'] = Date::format($rowEmail['created_at'], 'human');
        $rowEmail['created_at'] = Date::format($rowEmail['created_at']);
        $rowEmail['attachment'] = intval($rowEmail['attachment']);

        if($rowEmail['attachment']){
            $strSQL="SELECT * FROM email_attachments WHERE email_id='{$id}'";
            $rowEmail['attachments'] = $this->db->query($strSQL)->resultset();
        }
        return $rowEmail;
    }

    public function getDetail($id = null) {

        $this->setNotViewed();
        $response = $this->getEmailData($id);
        $response['notViewed'] = $this->notViewed;
        return $this->render('inboxDetail.index', $response);
    }

    public function sendMessage() {

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
