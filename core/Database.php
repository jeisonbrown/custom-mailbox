<?php 

namespace Core;
use \PDO;
use \PDOException;
use Core\MailboxException;

define('DB_HOST', getenv('DB_HOST'));
define('DB_USERNAME', getenv('DB_USERNAME'));
define('DB_PASSWORD', getenv('DB_PASSWORD'));
define('DB_DATABASE', getenv('DB_DATABASE'));
define('DEBUG', boolval(getenv('DEBUG', false)));

class Database {

    private $host;
    private $user;
    private $pass;
    private $dbname;

    private $dbh;
    public $conx = array();
    private $error;
    private $stmt;
    private $dbGetQuery;
    private static $instance = null;

    public function __construct() {
        $this->host   = DB_HOST;
        $this->user   = DB_USERNAME;
        $this->pass   = DB_PASSWORD;
        $this->dbname = DB_DATABASE;
        $this->setPDOConfig();
    }

    private function setPDOConfig() {
        // Set DSN
        $dsn = 'mysql:host=' . $this->host . ';port=3306;dbname=' . $this->dbname . ';charset=utf8';
        // Set options  PDO::ATTR_PERSISTENT
        $options = array(
            // PDO::ATTR_PERSISTENT    => false,
            PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION,
            // PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET utf8"
        );

        //Create a new PDO instance
        try {
            
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
            //var_dump( $this->dbh );
        } catch (PDOException $e) {
             
            if(DEBUG){
                $this->error = $e->getMessage();
                var_dump($e); 
                print_r($this->error);
                
            }

            exit();
        }

        //$this->dbGetQuery=array();
        //$this->bd = $this;
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this;
    }

    public function getQuery() {
        $str = str_replace(array("\n", "\r\n", "\r"), '', $this->dbGetQuery);
        return $str;
    }
    public function setQuery($query) {
        $this->dbGetQuery[] = $query;
    }

    public function query($query) {
        $this->dbGetQuery[] = $query;
        $this->stmt = $this->dbh->prepare($query);
        return $this;
    }

    public function execExist($table, $name, $value, $queryAdd = "") {
        $this->query("SELECT 1 FROM $table WHERE $name='$value' " . $queryAdd);
        $this->execute();
        return $this->stmt->fetchColumn();
    }

    public function getConnectionId() {
        $this->query("SELECT CONNECTION_ID()");
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }


    // public function bindArray($array)
    // {
    //     foreach ($array as $key => $value) {
    //         bind($key, $value);
    //     }
    // }

    public function execute(){
        try{
            return $this->stmt->execute();
        } catch(\Exception $e){
            $errors = $this->getQuery();
            MailboxException::showMessage($e, 500, $errors);
        }
    }


    public function resultset() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function execRowCount() {
        $this->execute();
        return $this->stmt->rowCount();
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }


    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }


    /**
     * Transactions allow multiple changes to a database all in one batch.
     */

    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }


    public function endTransaction() {
        return $this->dbh->commit();
    }


    public function cancelTransaction() {
        return $this->dbh->rollBack();
    }


    public function debugDumpParams() {
        return $this->stmt->debugDumpParams();
    }
}