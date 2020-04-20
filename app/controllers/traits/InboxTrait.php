<?php

namespace Controller\Traits;

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

        $limit = $this->currentPage - 1;
        $strSQL = "SELECT * FROM emails WHERE user_id = '{$_SESSION['USER_ID']}' AND ";
        $strSQL .= implode(' AND ', $this->filters);
        $strSQL .= " ORDER BY id DESC LIMIT {$limit},{$this->rows}";
        return $this->db->query($strSQL)->resultset();
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
