<?php
include "main.php";
use Simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/allelements.jrxml';

$data=[];

$config = ['driver'=>'dummy','data'=>$data];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setParameter(['subreportconnection'=>$config ])
    ->setDataSource($config)
    ->export('Pdf');

