<?php
include "main.php";

use Simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/multicolumn-vertical.jrxml';
// $filename = __DIR__.'/multicolumn-horizontal.jrxml';
// $filename = __DIR__.'/je.jrxml';
// $filename = __DIR__.'/rc.jrxml';
// $filename = __DIR__.'/statement.jrxml';
// $filename = __DIR__.'/uat.jrxml';

$config = ['driver'=>'postgresql','host'=>'127.0.0.1','user'=>'postgres','pass'=>'postgres','name'=>'backend']; //db
// $config = ['driver'=>'dummy','data'=>$array];


(new PHPJasperXML())->load_xml_file($filename)->setParameter(['para1'=>1,'para2'=>2])->setDataSource($config)->export('Pdf');