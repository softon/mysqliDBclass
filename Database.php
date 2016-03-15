<?php
# Name: Database.php
# File Description: MySQLi Singleton Class to allow easy and clean access to common mysql commands
# Author: Shiburaj
# Web: http://www.shiburaj.com/
# Update: 03/04/2014
# Version: 1.0.1
# Copyright 2014 shiburaj.com


/*
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/




###################################################################################################
###################################################################################################
###################################################################################################
class Database {

	/**
     * @uses Enable or Disable Debug Mode
     */
	public $debug = true;
    
    /**
     * @uses Set to true to print the query executed
     */
    public $show_query = true;
	
	/**
     * @uses Database Instance
     */
	private static $instance;

    /**
     * @uses Database Variables for Establishing Connection
     */
	private	$server   = ""; //database server
	private	$user     = ""; //database login name
	private	$pass     = ""; //database login password
	private	$database = ""; //database name

    /**
     * @uses Stores Error Count
     * @var Int
     */
	private	$error = "";

	/**
     * @uses Number of Rows affected by previous query
     */
	public	$affected_rows = 0;
    
    /**
     * @uses Database Connection Link ID & Query Return ID
     */
	private	$link_id = 0;
	private	$query_id = 0;
	
	/**
     * @uses Enable or Disable Transaction
     */
	private $transactionMode = false;
    
    /**
     * @uses Enable or Disable Transaction Debug Info on Screen
     */
	public $transactionDebug = true;
    

public function __construct($server=null, $user=null, $pass=null, $database=null){
	// error catching if not passed in
	if($server==null || $user==null || $database==null){
		$this->oops("Database information must be passed in when the object is first created.");
	}

	$this->server=$server;
	$this->user=$user;
	$this->pass=$pass;
	$this->database=$database;

    $this->link_id=@mysqli_connect($this->server,$this->user,$this->pass,$this->database);

    return $this;
}   //constructor


    /**
     * Singleton Initialization of the Database Variable
     * @param null $server
     * @param null $user
     * @param null $pass
     * @param null $database
     * @return Database
     */
    public static function obtain($server=null, $user=null, $pass=null, $database=null){
        if (!self::$instance){
            self::$instance = new Database($server, $user, $pass, $database);
        }

        return self::$instance;
    }   //obtain


    /**
     * @uses close the connection
     */
    public function close(){
        if(!@mysqli_close($this->link_id)){
            $this->oops("Connection close failed.");
        }
    }   //close


    /**
     * @uses escapes characters to be mysql ready
     * @param string
     * @return string
     */
    public function escape($string){
        if(get_magic_quotes_runtime()) $string = stripslashes($string);
        return @mysqli_real_escape_string($this->link_id, $string);
    }   //escape


    /**
     * Runs a query of raw SQL
     * @param $sql
     * @return $this|int
     */
    public function query($sql){
        // do query
        if($this->show_query===true){
            $this->oops('Query >> '.$sql,'db_warning');
        }
        $this->query_id = @mysqli_query($this->link_id, $sql);

        if (!$this->query_id){
            $this->error++;
            $this->oops("<b>MySQL Query fail:</b> $sql");
            return 0;
        }

        $this->affected_rows = @mysqli_affected_rows($this->link_id);

        return $this;
    }   //query


    /**
     * does a query, fetches the first row only
     * @param $query_string
     * @return array|null
     */
    public function query_first($query_string){
        return $this->query($query_string)->fetch();
    }   // query_first


    /**
     * fetches and returns results one line at a time
     * @param int $type
     * @return array|null
     */
    public function fetch($type=MYSQLI_ASSOC){

        if (isset($this->query_id)){
            $record = @mysqli_fetch_array($this->query_id,$type);
        }else{
            $this->oops("Invalid query_id. Records could not be fetched.");
        }

        return $record;
    }   // fetch


    /**
     * returns all the results of the current query
     * @param int $type
     * @return array
     */
    public function fetch_all($type=MYSQLI_ASSOC){

        $out = array();

        while ($row = $this->fetch($type)){
            $out[] = $row;
        }

        return $out;
    }   //fetch_array


    /**
     * get all rows matching sql criteria (auto escaped vars)
     * @param $sql
     * @param $vars
     * @return $this
     */
    public function get($sql, $vars){
        $safe = array();
        foreach($vars as $key=>$var){
            $safe[':'.$key] = $this->escape($var);
        }
        $this->query(strtr($sql,$safe));
        return $this;
    }


    /**
     * returns all columns by id (Column name should be id)
     * @param $table
     * @param $id
     * @return Database
     */
    public function getByID($table, $id){
        return $this->get("SELECT * FROM $table WHERE `id` = ':id'",['id'=>$id]);

    }   //getByID


    /**
     * returns single row of a table search by column
     * @param $table
     * @param $col_name
     * @param $col_val
     * @return Database
     */
    public function getByCol($table, $col_name, $col_val){
        return $this->get("SELECT * FROM $table WHERE `:col` = ':val'",['col'=>$col_name,'val'=>$col_val]);

    }   //getByCol


    /**
     * Count rows of a table
     * @param $table
     * @param string $where
     * @return mixed
     */
    public function count_rows($table, $where=" 1 "){
        $sql="SELECT COUNT(*) as row_cnt FROM `$table` WHERE ".$where;
        $data = $this->query($sql)->fetch();
        return $data['row_cnt'];
    }   //count_rows


    /**
     * does an update query with an array
     * @param $table
     * @param $data
     * @param string $where
     * @return bool
     */
    public function update($table, $data, $where='1'){
        $q="UPDATE `$table` SET ";

        foreach($data as $key=>$val){
            if(strtolower($val)=='null') $q.= "`$key` = NULL, ";
            elseif(strtolower($val)=='now()') $q.= "`$key` = NOW(), ";
            elseif(preg_match("/^increment\((\-?\d+)\)$/i",$val,$m)) $q.= "`$key` = `$key` + $m[1], ";
            else $q.= "`$key`='".$this->escape($val)."', ";
        }

        $q = rtrim($q, ', ') . ' WHERE '.$where.';';

        $this->query($q);

        if($this->query_id){
            return true;
        }

        return false;
    }   // update


    /**
     * Bulk update entries
     * @param $table
     * @param $data_arr
     * @param bool|false $use_tr
     * @return bool
     */
    public function bulk_update($table, $data_arr, $use_tr=false){
        $error=0;
        if($use_tr===true)
            $this->start_transaction();

        foreach($data_arr as $key=>$data){
            if(!$this->update($table,$data," `id` = '$key'"))
                $error++;
        }

        if($use_tr===true){
            if($this->error<=0){
                $this->commit();
                return true;
            }else{
                $this->rollback();
                return false;
            }
        }else{
            if($error<=0){
                return true;
            }else{
                return false;
            }
        }

    }   //bulk_insert


    /**
     * does an insert query with an array
     * @param $table
     * @param $data
     * @return bool|int|string
     */
    public function insert($table, $data){
        $q="INSERT INTO `$table` ";
        $v=''; $n='';

        foreach($data as $key=>$val){
            $n.="`$key`, ";
            if(strtolower($val)=='null') $v.="NULL, ";
            elseif(strtolower($val)=='now()') $v.="NOW(), ";
            else $v.= "'".$this->escape($val)."', ";
        }

        $q .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .");";
        //echo $q;
        $this->query($q);
        if($this->query_id){
            return mysqli_insert_id($this->link_id);
        }

        return false;

    }   //insert


    /**
     * Bulk insert
     * @param $table
     * @param $data_arr
     * @param bool|false $use_tr
     * @return bool
     */
    public function bulk_insert($table, $data_arr, $use_tr=false){
        $error=0;
        if($use_tr===true)
            $this->start_transaction();

        foreach($data_arr as $key=>$data){
            if(!$this->insert($table,$data))
                $error++;
        }

        if($use_tr===true){
            if($this->error<=0){
                $this->commit();
                return true;
            }else{
                $this->rollback();
                return false;
            }
        }else{
            if($error<=0){
                return true;
            }else{
                return false;
            }
        }

    }   //bulk_insert


    /**
     * Delete Query
     * @param $table
     * @param string $where
     * @return bool
     */
    public function delete($table, $where=" id = 'NaN'"){
        $q="DELETE FROM `$table` WHERE ".$where;
        $this->query($q);
        if($this->query_id){
            return true;
        }

        return false;

    }   //delete


/**
 * @uses frees the resultset
 * @param query_id for mysql run. if none specified, last used
 */
private function free_result($query_id=false){
	if ($query_id!==false){
		$this->query_id=$query_id;
	}
	mysqli_free_result($this->query_id);
}   // free_result


/***************************************************************
***************** Transactions *********************************
***************************************************************/

/**
 * @uses Starts a Transaction
 * @return Bool
 */
public function start_transaction(){
	
    if($this->transactionDebug===true){
        $this->oops('Starting Transaction.','db_info');
    }
    
	$this->transactionMode = true;
	$this->error = 0;
	return mysqli_autocommit($this->link_id,FALSE);
    
}   //start_transaction


/**
 * @uses Stop and Process a Transaction
 * @return Bool (True: Transaction Commited)/(False: Transaction Rolled Back)
 */
public function stop_transaction(){
	if($this->transactionDebug===true){
        $this->oops('Stoping Transaction. Error Count: '.$this->error,'db_info');
    }
    
	if($this->transactionMode && $this->error<=0){
		$this->commit();
		$this->transactionMode = false;
		$this->error = 0;
		return true;
	}else{
		$this->rollback();
		$this->transactionMode = false;
		$this->error = 0;
		return false;
	}
    
}   //stop_transaction


/**
 * @uses Commits a Transaction ie saves all the changes
 * @return Bool
 */
public function commit(){
	if($this->transactionDebug===true){
        $this->oops('Commiting Transaction.','db_success');
    }
    
	$return = mysqli_commit($this->link_id);
    mysqli_autocommit($this->link_id,TRUE);
	return $return;
}   //commit


/**
 * @uses Roolback a Transaction ie discard all the changes
 * @return Bool
 */
public function rollback(){
	if($this->transactionDebug===true){
        $this->oops('Rolling Back Transaction.','db_warning');
    }
	$return = mysqli_rollback($this->link_id);
	mysqli_autocommit($this->link_id,TRUE);
	return $return;
    
}   //rollback


/**
 * @uses backups the database tables to file specified(Sql Dump)
 * @params: $folder,$table
 * @return: true/false
 */
function backup_tables($folder,$tables = '*'){
    $return = '';
    //get all of the tables
    if($tables == '*'){
        $tables = array();
        while($row = $this->query('SHOW TABLES')->fetch(MYSQLI_NUM)){
            var_dump($row);
          $tables[] = $row[0];
        }
    }else{
        $tables = is_array($tables) ? $tables : explode(',',$tables);
    }
  
    //cycle through
    foreach($tables as $table){
        $result = $this->query('SELECT * FROM '.$table);
        $num_fields = mysqli_num_fields($result);
        
        $return.= 'DROP TABLE '.$table.';';
        $row2 = $this->fetch($this->query('SHOW CREATE TABLE '.$table),MYSQLI_NUM);
        $return.= "\n\n".$row2[1].";\n\n";
        
        for ($i = 0; $i < $num_fields; $i++){
          while($row = $this->fetch($result,MYSQLI_NUM)){
            $return.= 'INSERT INTO '.$table.' VALUES(';
            for($j=0; $j<$num_fields; $j++){
              $row[$j] = addslashes($row[$j]);
              $row[$j] = ereg_replace("\n","\\n",$row[$j]);
              if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
              if ($j<($num_fields-1)) { $return.= ','; }
            }
            $return.= ");\n";
          }
        }
        $return.="\n\n\n";
    }
    
    //save file
    $handle = fopen($folder.'backup-'.time().'-db.sql','w+');
    $state = fwrite($handle,$return);
    fclose($handle);
    
    return $state;	

}   //backup_tables


/**
 * @uses throw an error message in debug mode only
 * @param [optional] any custom error to display
 */
private function oops($msg='',$type='db_error'){
	if(!empty($this->link_id)){
		$error = mysqli_error($this->link_id);
	}
	else{
		$error="<b>WARNING:</b> No link_id found. Not connected to any Database.<br />$msg";
	}
    
    $db_title = array('db_info'=>'Database Information',
                        'db_success'=>'Database Success',
                        'db_warning'=>'Database Warning',
                        'db_error'=>'Database Error'
                    );

	// if no debug, done here
	if(!$this->debug) return;
	?>
        <style type="text/css">
        <!--
            
        	.db_info, .db_success, .db_warning, .db_error {
                margin: 10px 0px;
                padding:12px;
                border-radius:.5em;
                border: 1px solid;
            }
            .db_info li, .db_success li, .db_warning li, .db_error li {
                margin: 0px 22px;
                padding:5px;
                border-bottom: 1px dotted;
            }
            .db_info {
                color: #00529B;
                background-color: #BDE5F8;
            }
            .db_success {
                color: #4F8A10;
                background-color: #DFF2BF;
            }
            .db_warning {
                color: #9F6000;
                background-color: #FEEFB3;
            }
            .db_error {
                color: #D8000C;
                background-color: #FFBABA;
            }
            
        -->
        </style>
        
        <div class="<?php echo $type; ?>">
           <div><strong><?php echo $db_title[$type]; ?></strong></div>
           <div>
                <li><span>Message: </span> <?php echo $msg; ?></li>
                <?php if(!empty($error)) echo '<li><span>MySQL Error:</span> '.$error.'</li>'; ?>
                <li><span>Date:</span> <?php echo date("l, F j, Y \a\\t g:i:s A"); ?></li>
        		<?php if(!empty($_SERVER['REQUEST_URI'])) echo '<li><span>Script:</span> <a href="'.$_SERVER['REQUEST_URI'].'">'.$_SERVER['REQUEST_URI'].'</a></li>'; ?>
        		<?php if(!empty($_SERVER['HTTP_REFERER'])) echo '<li><span>Referer:</span> <a href="'.$_SERVER['HTTP_REFERER'].'">'.$_SERVER['HTTP_REFERER'].'</a></li>'; ?>
           </div>
           
        </div>
		
	<?php
}   //oops


}   // Class DB
?>
