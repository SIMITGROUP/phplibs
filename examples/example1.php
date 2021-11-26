<?php
include "main.php";

use Simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/sample1.jrxml';
// $filename = __DIR__.'/je.jrxml';
// $filename = __DIR__.'/rc.jrxml';
// $filename = __DIR__.'/statement.jrxml';
// $filename = __DIR__.'/uat.jrxml';

$connectconf = [''];
$config = ['driver'=>'postgresql','host'=>'127.0.0.1','user'=>'postgres','pass'=>'postgres','name'=>'backend'];

$obj = new PHPJasperXML();

$obj->load_xml_file($filename)
        ->connect($config)        
        ->transferDBtoArray($config)        
        // ->connect(DBSERVER,DBUSER,DBPASS,DBNAME,DBDRIVER)->query() //buildin sql
        // ->connect(DBSERVER,DBUSER,DBPASS,DBNAME,DBDRIVER)->query($sql) //override sql
        // ->setData($array)        
        // ->outpage('I') // same with $obj->pdf(); //for compatibility 
        ->export('pdf') 
        // ->html()
        // ->xls()
        // ->xlsx()
        ;