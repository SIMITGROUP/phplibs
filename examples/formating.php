<?php
include "main.php";

use Simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/formating.jrxml';

function getS3($a)
{
    return "hi hi hi ".$a;
}

$data = [];
$faker = Faker\Factory::create('en_US');
for($i=0;$i<20;$i++)
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


$config = ['driver'=>'dummy','data'=>$data];

$pdffilename = '/tmp/sample1.pdf';
if(file_exists($pdffilename))
{
    unlink($pdffilename);
}

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setDataSource($config)
    ->export('Pdf');

