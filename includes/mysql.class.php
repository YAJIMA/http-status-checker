<?php
/**
 * MySQL PDO 拡張処理クラス
 *
 * @package     General
 * @author      Y.Yajima <yajima@hatchbit.jp>
 * @copyright   2014, HatchBit & Co.
 * @license     http://www.hatchbit.jp/resource/license.html
 * @link        http://www.hatchbit.jp
 * @since       Version 1.0
 * @filesource
 */

class dbEngine extends PDO {
    
    #make a connection
    public function __construct($dsn = DB_DSN,$dbname = DB_DATABASE,$username = DB_SERVER_USERNAME,$password = DB_SERVER_PASSWORD) {
        try {
            parent::__construct($dsn,$username,$password);
            parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch (PDOException $e) {
            die($e->getMessage());
        }
    }
    
    # closes the database connection when object is destroyed.
    public function __destruct(){
        $this->connection = null;
    }
    
    #get the number of rows in a result
    public function num_rows($query){
        # create a prepared statement
        $stmt = parent::prepare($query);
        if($stmt){
            # execute query 
            $stmt->execute();
            return $stmt->rowCount();
        }else{
            return self::get_error();
        }
    }

    #display error
    public function get_error() {
        $this->connection->errorInfo();
    }
    
    public function delete($tableName = "", $id = 0) {
        $sql = "DELETE FROM $tableName WHERE `id` = ".intval($id);
        $flag = parent::exec($sql);
        return $flag;
    }
    
    public function perform($tableName, $tableData, $performType='INSERT', $performFilter='', $debug=false) {
        /*
        ## $performType = ( INSERT | UPDATE )
        ## $tableData = array ( 
        ##     'fieldName' => fieldName, 
        ##     'value' => value, 
        ##    'type' => ( csv | passthru | float | integer | string | noquotestring | currency | date|enum | regexp ) 
        ## )
        ## $performFilter = WHERE_STRINGS
        */
        switch (strtolower($performType)) {
            case 'upsert':
                $upsertString = "";
                $upsertString = "INSERT INTO ".$tableName." (";
                foreach ($tableData as $key => $value) {
                    if ($debug === true) {
                        echo $value['fieldName'] . '#';
                    }
                    $upsertString .= $value['fieldName'] . ", ";
                }
                $upsertString = substr($upsertString, 0, strlen($upsertString)-2) . ') VALUES (';
                reset($tableData);
                foreach ($tableData as $key => $value) {
                    $bindVarValue = $this->getBindVarValue($value['value'], $value['type']);
                    $upsertString .= $bindVarValue . ", ";
                }
                $upsertString = substr($upsertString, 0, strlen($upsertString)-2) . ') ';
                $upsertString .= "ON DUPLICATE KEY UPDATE ";
                foreach ($tableData as $key => $value) {
                    if(isset($value['index']) && $value['index'] === 1) continue;
                    $upsertString .= "`".$value['fieldName']."`=VALUES(`".$value['fieldName']."`), ";
                }
                $upsertString = substr($upsertString, 0, strlen($upsertString)-2);
                //"visit=visit+1, browse=browse+values(browse)";
                if ($debug === true) {
                    echo $upsertString;
                    die();
                } else {
                    $stmt = parent::query($upsertString);
                }
                break;
            case 'insert':
                $insertString = "";
                $insertString = "INSERT INTO " . $tableName . " (";
                foreach ($tableData as $key => $value) {
                    if ($debug === true) {
                        echo $value['fieldName'] . '#';
                    }
                    $insertString .= $value['fieldName'] . ", ";
                }
                $insertString = substr($insertString, 0, strlen($insertString)-2) . ') VALUES (';
                reset($tableData);
                foreach ($tableData as $key => $value) {
                    $bindVarValue = $this->getBindVarValue($value['value'], $value['type']);
                    $insertString .= $bindVarValue . ", ";
                }
                $insertString = substr($insertString, 0, strlen($insertString)-2) . ')';
                if ($debug === true) {
                    echo $insertString;
                    die();
                } else {
                    $stmt = parent::query($insertString);
                }
                break;
            case 'update':
                $updateString ="";
                $updateString = 'UPDATE ' . $tableName . ' SET ';
                foreach ($tableData as $key => $value) {
                    $bindVarValue = $this->getBindVarValue($value['value'], $value['type']);
                    $updateString .= $value['fieldName'] . '=' . $bindVarValue . ', ';
                }
                $updateString = substr($updateString, 0, strlen($updateString)-2);
                if ($performFilter != '') {
                    $updateString .= ' WHERE ' . $performFilter;
                }
                if ($debug === true) {
                    echo $updateString;
                    die();
                } else {
                    $stmt = parent::query($updateString);
                }
                break;
        }
    }/* EOF:function perform */
    
    public function getBindVarValue($value, $type) {
        $typeArray = explode(':',$type);
        $type = $typeArray[0];
        switch ($type) {
            case 'csv':
                return $value;
                break;
            case 'passthru':
                return $value;
                break;
            case 'float':
                return ($value=='' || $value == 0) ? 0 : $value;
                break;
            case 'integer':
                return (int)$value;
                break;
            case 'string':
                return $this->quote($value, PDO::PARAM_STR);
                break;
            case 'date':
                return $this->quote($value, PDO::PARAM_STR);
                break;
            case 'enum':
                return $this->quote($value, PDO::PARAM_STR);
                break;
            case 'regexp':
                $searchArray = array('[', ']', '(', ')', '{', '}', '|', '*', '?', '.', '$', '^');
                foreach ($searchArray as $searchTerm) {
                    $value = str_replace($searchTerm, '\\' . $searchTerm, $value);
                }
                return $this->quote($value, PDO::PARAM_STR);
                break;
            default:
                die('var-type undefined: ' . $type . '('.$value.')');
        }
    }/* EOF:function getBindVarValue */
    
    public function insert_ID() { /* 直近の INSERT 操作で生成された ID を得る */
        return parent::lastInsertId();
    }/* EOF:function insert_ID */
    
}
?>