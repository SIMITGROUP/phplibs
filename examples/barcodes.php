<?php
include "main.php";
use Simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/barcodes.jrxml';

$data=[
    ['a'=>1]
]; // 1 row

$config = ['driver'=>'dummy','data'=>$data];
$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

