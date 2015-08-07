<?php 

require_once __DIR__ . '/../src/config/config.inc.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

use Softon\MySqlDB\Database;


/************************************************************
*******************  Creating Objects  **********************
************************************************************/

// Create Database Object
$db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);


// Connect to the server
$db->connect(); 

// Escaping Variables
$ins_data['ip'] = $db->escape($_GET['ip']);

// Debug Flags
//$db->debug = true;              // Master Flag to enable debug mode
//$db->show_query = true;         // Show all executed Query on screen
//$db->transactionDebug = true;   // Show Transaction Debug Messages

// Query SQL statement & fetch a row
$db->query('SELECT * FROM ipban');
$data = $db->fetch();


// Query & Retrive First Entry
$data = $db->query_first('SELECT * FROM ipban WHERE `id`=18');

// Fetch all rows based on the query
$all_rows = $db->fetch_array('SELECT * FROM ipban');


// Fetch all rows based on query with escape values
$all_rows = $db->get("SELECT * FROM ipban WHERE `id`='?' AND `ts`='?' ",array('1','Test'));

// Get a Row by its id val
$id_data = $db->getByID('ipban',18);

// Get a Row by any of its column
$id_data = $db->getByCol('ipban','ip','Test2');


// Return Num of Rows
$num_rows = $db->count_rows('ipban');

// Database Insert & Update
$data_ins_arr['ip'] = 'Test';
$data_ins_arr['ts'] = 'NOW()';

$insert_id = $db->insert('ipban',$data_ins_arr);

$db->update('ipban',$ins_data," `id` = '18'");

// Delete rows matched on the where clause
$db->delete('ipban'," `id`='18'");



// Transactions Demo
$db->start_transaction();
$db->insert('ipban',$data_ins_arr);
$db->insert('ipban',array('ip'=>'Shibu'));
$db->insert('ipban',array('ip'=>'TestTest'));
$db->delete('ipban'," id=176 ");
$db->stop_transaction();


// Dump Table to file
$db->backup_tables('./','ipban');


$data_bulk[0]['ip'] = 'Shibu';
$data_bulk[0]['ts'] = 'NOW()';
$data_bulk[1]['ip'] = 'Deepu';
$data_bulk[1]['ts'] = 'NOW()';
if($db->bulk_insert('ipban',$data_bulk,true)){
    echo 'Done Bulk Insert';
}else{
    echo 'Error Bulk Insert';
}


$data2_bulk[16]['ip'] = 'Shibu';
$data2_bulk[16]['ts'] = 'NOW()';
$data2_bulk[17]['ip'] = 'Deepu';
$data2_bulk[17]['ts'] = 'NOW()';
if($db->bulk_insert('ipban',$data2_bulk,true)){
    echo 'Done Bulk Update';
}else{
    echo 'Error Bulk Update';
}
?>