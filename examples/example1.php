<?php
include "main.php";

use Simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/example1.jrxml';
// $filename = __DIR__.'/multicolumn-vertical.jrxml';
// $filename = __DIR__.'/multicolumn-horizontal.jrxml';
$filename = __DIR__.'/multipleelements.jrxml';
// $filename = __DIR__.'/barcodes.jrxml';
// $filename = __DIR__.'/statement.jrxml';
// $filename = __DIR__.'/uat.jrxml';

function getS3($a)
{
    return "hi hi hi ".$a;
}

$data = [];
$faker = Faker\Factory::create();
for($i=0;$i<1;$i++)
{
    $tmp=[
        'fullname' => $faker->name(),
        'email' => $faker->email(),
        'gender' => $faker->randomElement(['M', 'F']),
        'globaluser_id'=> $i+100008,
        'description'=>$faker->text(),
        'country_code'=>$faker->randomElement(['SG','AU','US','MY']),
        'created'=>$faker->date("Y-m-d H:i:s")

    ];
    $data[$i]=$tmp;
}


// $config = ['driver'=>'postgresql','host'=>'127.0.0.1','user'=>'postgres','pass'=>'postgres','name'=>'backend']; //postgresql db
$config = ['driver'=>'dummy','data'=>$data];


$report = new PHPJasperXML();
$report->load_xml_file($filename)
    // ->setParameter(['para1'=>1,'para2'=>2])
    ->setDataSource($config)
    ->export('Pdf');

