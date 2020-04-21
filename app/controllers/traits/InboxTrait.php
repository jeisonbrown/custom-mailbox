<?php

namespace Controller\Traits;

use Controller\NotificationController;
trait InboxTrait
{

    private $rows;
    private $totalRows;
    private $currentPage;
    private $maxPages;
    private $filters;
    private $type;
    private $notViewed;

    private function setTotalRows() {
        $strSQL = "SELECT id FROM emails WHERE user_id = '{$_SESSION['USER_ID']}' AND inbox AND NOT deleted";
        $this->totalRows = $this->db->query($strSQL)->execRowCount();
    }

    private function setNotViewed() {
        $strSQL = "SELECT id FROM emails WHERE user_id = '{$_SESSION['USER_ID']}' AND inbox AND NOT viewed AND NOT deleted";
        $this->notViewed = $this->db->query($strSQL)->execRowCount();
    }

    private function setCurrentPage() {
        $page = !empty($_GET['page']) ? $_GET['page'] : 1;

        if (empty($page) || $page < 1) {
            $page = 1;
        }

        if ($page > $this->maxPages) {
            $page = $this->maxPages;
        }

        $this->currentPage = $page;
    }

    private function setMaxPages() {
        $this->maxPages = ceil(($this->totalRows / $this->rows));
    }

    private function getFilteredEmails() {

        $limit = $this->currentPage > 0  ? $this->currentPage - 1 : 0;
        $strSQL = "SELECT * FROM emails WHERE user_id = '{$_SESSION['USER_ID']}' AND ";
        $strSQL .= implode(' AND ', $this->filters);
        $strSQL .= " ORDER BY id DESC LIMIT {$limit},{$this->rows}";
        return $this->db->query($strSQL)->resultset();
    }

    private function arrayHeaderToCommaSeparated($headers, $type){
        $nameList = [];
        $emailList = [];

        if(property_exists($headers, $type)){
            $headerTypeList = $headers->{$type};
            foreach($headerTypeList as $header){
                $nameList[] = property_exists($headers, 'personal') ? $header['personal'] : '';
                $emailList[] = $header->mailbox . '@' . $header->host;
            }
        }

        $data['names'] = implode(',', $nameList);
        $data['emails'] = implode(',', $emailList);
        return $data;
    }

    public function getTracker() {
        $host = getenv('IMAP_HOST');
        $port = getenv('IMAP_PORT');
        $protocole = getenv('IMAP_PROTO');
        $username = getenv('IMAP_USERNAME');
        $password = getenv('IMAP_PASSWORD');
        $defaultMailbox = "{" . "{$host}:{$port}/{$protocole}/ssl}INBOX";

        $imap = imap_open($defaultMailbox, $username, $password);

        if ($imap) {

            $debug = boolval(getenv('DEBUG', false));
            
            $MC = imap_check($imap);
            imap_headers($imap);
            
            for($i = 1; $i < $MC->Nmsgs; $i++){

                $imapBody = imap_qprint(imap_body($imap, $i));
                // $replace = preg_replace('/(<span id=)(.*)(\[\[HASH\:\-\-)/', '[[HASH:--', $imapBody);
                // preg_match('/(\[\[HASH\:\-\-)(.*)(\-\-\]\])/', $replace, $matches, PREG_OFFSET_CAPTURE);
                preg_match('/(\[\[HASH\:\-\-)(.*)(\-\-\]\])/', htmlspecialchars($imapBody), $matches, PREG_OFFSET_CAPTURE);
                if(empty($matches[2])){
                    continue;
                }


                $token = $matches[2];
                
                if(empty($token[0])){
                    continue;
                }
                
                $strSQL="SELECT id, user_id, token FROM emails WHERE token='{$token[0]}' LIMIT 1";
                $rowEmail = $this->db->query($strSQL)->single();

                if(!$rowEmail){
                    continue;
                }

                $headers = imap_headerinfo($imap, $i);
                $message = addslashes($imapBody);
                $message = preg_replace('/(<span(.*)id=)(.*)(\[\[HASH\:\-\-)(.*)(\-\-\]\])(.*)(<\/(.*)span>)/', '', $message);
                $data['message'] = $message;
                $data['user_id'] = $rowEmail['user_id'];
                $data['parent_id'] = $rowEmail['id'];
                $data['token'] = $rowEmail['token'];
                $data['subject'] = $headers->subject;
                
                $fromList = $this->arrayHeaderToCommaSeparated($headers, 'from');
                $toList = $this->arrayHeaderToCommaSeparated($headers, 'to');
                $ccList = $this->arrayHeaderToCommaSeparated($headers, 'cc');
                $bccList = $this->arrayHeaderToCommaSeparated($headers, 'bcc');
                $replyToList = $this->arrayHeaderToCommaSeparated($headers, 'reply_to');

                $data['name'] = $fromList['names'];
                $data['from'] = $fromList['emails'];
                $data['to'] = $toList['emails'];
                $data['cc'] = $ccList['emails'];
                $data['bcc'] = $bccList['emails'];
                $data['reply'] = $replyToList['emails'];
                
                $data['message_id'] = $headers->message_id;
                $data['inbox'] = 1;
                $data['important'] = 0;
                $data['attachment'] = 0;
                $data['created_at'] = date('Y-m-d H:i:s', strtotime($headers->date));
                
                $keys = [];
                $values = [];
                foreach($data as $key => $value){
                    $keys[] ="`{$key}`";
                    $values[] ="'{$value}'";
                }

                $strSQL="INSERT INTO emails (" . implode(',', $keys) . ") VALUES (" . implode(',', $values) . ");";
                $this->db->query($strSQL)->execute();

                $id = $this->db->lastInsertId();
                NotificationController::send('Nuevo mensaje!', 'Has recibido un nuevo mensaje.', '/' . $id, 'received');
            }

            imap_close($imap);
        }
    }

    private function applyFiltersByType() {
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
                $this->getTracker();
                $this->filters[] = "inbox AND NOT deleted";
                break;
        }
    }

    private function applyFiltersByState() {
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

    private function applyFilters() {
        $this->applyFiltersByType();
        $this->applyFiltersByState();
    }

    private function getTypeName() {
        switch ($this->type) {
            case 'sended':
                return 'Enviados';
            case 'important':
                return 'Destacados';
            case 'deleted':
                return 'Eliminados';
            default: //inbox 
                return 'Recibidos';
        }
    }

    private function markAsViewed($id){
        $date = date('Y-m-d H:i:s');
        $strSQL="UPDATE emails SET viewed=1, updated_at='{$date}' WHERE id='{$id}' AND user_id='{$_SESSION['USER_ID']}' LIMIT 1";
        $this->db->query($strSQL)->execute();
    }
}
