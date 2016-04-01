mysqliDBclass
=============

MySQLi Database Class for PHP Developers

A simple Object Oriented approach for accessing Mysql Databases. This class will make your life easy while developing your products and saves a lot of time that is wasted while creating simple CRUD operations. This class also suports Transactions. Good Error Reporting and Debuging features. Additional quick commands that provide better security and ease of developing. Refer example.php for all the sample code. 

This class is an enhanced version of PHP MySQL wrapper v3 by <a href="http://www.ricocheting.com/code/php/mysql-database-class-wrapper-v3">ricocheting.com</a>. Hence it is compatible with the class. To use the enhanced MySQL Wrapper simply include the Database.class.php in your code and everything should work as before. 

<h2>How to Setup</h2>

Before you start, edit the config.inc.php to match your database credentials. Then include the Class file and config file on the top of the code where you want to use the class as show below.

<pre><code>
include_once('config.inc.php');
include_once('Database.php');
</code></pre>


Create a DB object to interact with the class.

<pre><code>
$db = new Database(DB_SERVER,DB_USER,DB_PASS,DB_DATABASE);
</code></pre>

You are all set to run any query with this $db object. If you are inside a function or a another class you may call the obtain function to recover the $db object.

<pre><code>
$db = new Database::obtain();
</code></pre>

<h2>How to Use</h2>


```php

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

```