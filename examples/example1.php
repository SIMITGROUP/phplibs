<?php
include "main.php";

use Simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/example1.jrxml';
// $filename = __DIR__.'/multicolumn-vertical.jrxml';
// $filename = __DIR__.'/multicolumn-horizontal.jrxml';
// $filename = __DIR__.'/multipleelements.jrxml';
$filename = __DIR__.'/letter.jrxml';
// $filename = __DIR__.'/barcodes.jrxml';
// $filename = __DIR__.'/statement.jrxml';
// $filename = __DIR__.'/uat.jrxml';

function getS3($a)
{
    return "hi hi hi ".$a;
}

$data = [];
$faker = Faker\Factory::create('en_US');
for($i=0;$i<40;$i++)
{
    $tmp=[
        'fullname' => $faker->name(),
        'email' => $faker->email(),
        'gender' => $faker->randomElement(['M', 'F']),
        'globaluser_id'=> $i+100008,
        'description'=>"Begin $i.\n".$faker->realText(70)."\n".$faker->realText() ."\n Ending",
        'country_code'=>$faker->randomElement(['SG','AU','US','MY']),
        'created'=>$faker->date("Y-m-d H:i:s")

    ];
    $data[$i]=$tmp;
}


$configsubreport = ['driver'=>'postgresql','host'=>'127.0.0.1','user'=>'postgres','pass'=>'postgres','name'=>'backend']; //postgresql db
$config = ['driver'=>'dummy','data'=>$data];

$pdffilename = '/tmp/sample1.pdf';
if(file_exists($pdffilename))
{
    unlink($pdffilename);
}

$report = new PHPJasperXML();
$report->load_xml_file($filename)
    ->setParameter(['subreportconnection'=>$configsubreport])
    ->setDataSource($config)
    ->export('Pdf',$pdffilename);

