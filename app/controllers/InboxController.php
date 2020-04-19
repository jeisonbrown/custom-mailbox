<?php

namespace Controller;

use \Core\Mailer;
use \Core\Date;

class InboxController extends \Core\Controller
{

    private $rows;
    private $totalRows;
    private $currentPage;
    private $maxPages;
    private $filters;
    private $type;

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

    private function setTotalRows()
    {
        $strSQL = "SELECT id FROM emails WHERE user_id = '{$_SESSION['USER_ID']}' AND inbox AND NOT deleted";
        $this->totalRows = $this->db->query($strSQL)->execRowCount();
    }

    private function setCurrentPage()
    {
        $page = !empty($_GET['page']) ? $_GET['page'] : 1;

        if (empty($page) || $page < 1) {
            $page = 1;
        }

        if ($page > $this->maxPages) {
            $page = $this->maxPages;
        }

        $this->currentPage = $page;
    }

    private function setMaxPages()
    {
        $this->maxPages = ceil(($this->totalRows / $this->rows));
    }

    private function getFilteredEmails()
    {

        $limit  = $this->currentPage - 1;
        $strSQL = "SELECT * FROM emails WHERE user_id = '{$_SESSION['USER_ID']}' AND ";
        $strSQL .= implode(' AND ', $this->filters);
        $strSQL .= " ORDER BY id DESC LIMIT {$limit},{$this->rows}";
        return $this->db->query($strSQL)->resultset();
    }

    private function applyFiltersByType(){
        $this->type = !empty($_GET['type']) ? $_GET['type'] : 'inbox';
        switch ($this->type) {
            case 'sended':
                $this->filters[] = "sended AND NOT deleted";
                break;
            case 'important':
                $this->filters[] = "important AND NOT deleted";
                break;
            case 'deleted':
                $this->filters[] = "deleted";
                break;
            default: //inbox 
                $this->filters[] = "inbox AND NOT deleted";
                break;
        }
    }

    private function applyFiltersByState(){
        $this->state = !empty($_GET['state']) ? $_GET['state'] : 'all';
        switch ($this->state) {
            case 'viewed':
                $this->filters[] = "viewed";
                break;
            case 'not-viewed':
                $this->filters[] = "NOT viewed";
                break;
            default: //inbox 
                break;
        }
    }

    private function applyFilters()
    {
        $this->applyFiltersByType();
        $this->applyFiltersByState();
    }

    private function getTypeName(){
        switch ($this->type) {
            case 'sended':
                return 'Enviados';
                break;
            case 'important':
                return 'Destacados';
                break;
            case 'deleted':
                return 'Eliminados';
                break;
            default: //inbox 
                return 'Recibidos';
                break;
        }
    }

    public function getIndex()
    {

        $this->rows = intval(getenv('TABLE_ROWS_BY_PAGE', 10));
        $this->applyFilters();
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
            'typeName' => $this->getTypeName()
        ]);
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
