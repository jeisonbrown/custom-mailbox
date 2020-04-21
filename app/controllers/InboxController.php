<?php

namespace Controller;

use Core\Mailer;
use Core\Date;

class InboxController extends \Core\Controller
{

    use \Controller\Traits\InboxTrait;
    use \Controller\Traits\InboxSendMessageTrait;
    use \Controller\Traits\InboxDetailTrait;
    use \Controller\Traits\NotificationTrait;

    public function downloadAttachment($email_id, $file) {

        $fileRoute = $_SERVER['DOCUMENT_ROOT'] . "/../uploads/attachments/{$file}";
        if (file_exists($fileRoute)) {
            $filetype = filetype($fileRoute);
            $filename = basename($fileRoute);
            header("Content-Type: " . $filetype);
            header("Content-Length: " . filesize($fileRoute));
            header("Content-Disposition: attachment; filename=" . $filename);
            readfile($fileRoute);
        }
        else {
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
            'notViewed' => $this->notViewed,
            'notifications' => $this->getNotifications()
        ]);
    }

    private function getEmailData($id) {
        $strSQL = "SELECT * FROM emails WHERE id = '{$id}' LIMIT 1";
        $rowEmail = $this->db->query($strSQL)->single();
        $rowEmail['created_at_human'] = Date::format($rowEmail['created_at'], 'human');
        $rowEmail['created_at'] = Date::format($rowEmail['created_at']);
        $rowEmail['attachment'] = intval($rowEmail['attachment']);

        if ($rowEmail['attachment']) {
            $strSQL = "SELECT * FROM email_attachments WHERE email_id='{$id}'";
            $rowEmail['attachments'] = $this->db->query($strSQL)->resultset();
        }
        return $rowEmail;
    }

    public function getDetail($id = null) {
        if (empty($_GET['not-viewed'])) {
            $this->markAsViewed($id);
        }
        $this->setNotViewed();
        $response = $this->getEmailData($id);
        $response['message'] = preg_replace('/(Content-.*\:.*)|(\-\-00000.*)/', '', $response['message']);
        $response['notifications'] = $this->getNotifications();
        $response['notViewed'] = $this->notViewed;
        return $this->render('inboxDetail.index', $response);
    }

    public function markAs($id) {
        $update = [];
        $queryString = $_POST['viewed'] == 1 ? '/?not-viewed=1' : '';
        foreach ($_POST as $key => $value) {
            $newValue = $value == 1 ? 0 : 1;
            $update[] = "{$key}='$newValue'";
        }

        $strSQL = "UPDATE emails SET " . implode(',', $update) . " WHERE id='{$id}' AND user_id='{$_SESSION['USER_ID']}' LIMIT 1";
        $this->db->query($strSQL)->execute();

        if (!empty($_POST['deleted'])) {
            return $this->redirect('/');
        }

        return $this->redirect('/' . $id . $queryString);
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

        $mailer->addReplyTo(getenv('IMAP_USERNAME'), getenv('IMAP_ALIAS'));
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
