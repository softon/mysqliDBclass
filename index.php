<?php

include_once('config.inc.php');
include_once('Database.php');


// Create Database Object
$db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);

// Enable Debugging (Optional)
$db->debug = false;
$db->show_query = false;
$db->transactionDebug = false;

//Simple Raw Query
$result = $db->query("SELECT * FROM authors")->fetch();

//Parametrised Query and fetch one Record
$result = $db->query("SELECT * FROM :table WHERE :name = ':value'",['table'=>'authors','name'=>'paperid','value'=>'P12206436'])->fetch();

//Parametrised Query and fetch all Records
$result_array = $db->query("SELECT * FROM :table WHERE :name = ':value'",['table'=>'authors','name'=>'paperid','value'=>'P12206436'])->fetch_all();

// Find a row by id and fetch
$result = $db->findById('authors',1);

// Find a row by column and fetch
$result = $db->findByCol('authors','paperid','P12206436');


// Count All rows in a table
$result = $db->count('authors');

// Count All rows in a table with condition
$result = $db->count('authors'," astatus='Approved'");

// Update a row
$result = $db->update('authors',['paperid'=>'SHIBU']," id = '1'");

// Update a row with parameter protection
$result = $db->update('authors',['paperid'=>'SHIBU','aname'=>'test','aorg'=>'test']," id = '1'",['paperid','aname']);

// Bulk Update a row with parameter protection without transaction
$result = $db->bulk_update('authors',array(
    1=> array('paperid'=>'SHIBU','aname'=>'test','aorg'=>'test'),
    2=> array('paperid'=>'SHIBU2','aname'=>'test2','aorg'=>'test2')
),['paperid','aname']);


// Bulk Update a row without parameter protection with transaction
$result = $db->bulk_update('authors',array(
    1=> array('paperid'=>'SHIBU','aname'=>'test','aorg'=>'test'),
    2=> array('paperid'=>'SHIBU2','aname'=>'test2','aorg'=>'test2')
),null,true);

// Insert Data
$result = $db->insert('authors',['paperid'=>rand(1234,99999),'aname'=>'Test Ing']);

// Bulk Insert with parameter protection with transaction
$result = $db->bulk_insert('authors',array(
    array('paperid'=>'SHIBU','aname'=>'test','aorg'=>'test'),
    array('paperid'=>'SHIBU2','aname'=>'test2','aorg'=>'test2')
),['paperid','aname'],true);


// Delete a record
$result = $db->delete('authors'," id=1 ");

// Backup Database Sql File (Dump)
$db->backup_tables('./');

var_dump($result);





$db->close();