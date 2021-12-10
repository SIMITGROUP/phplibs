<?php
include "main.php";

use Simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/example1.jrxml';
// $filename = __DIR__.'/multicolumn-vertical.jrxml';
// $filename = __DIR__.'/multicolumn-horizontal.jrxml';
// $filename = __DIR__.'/je.jrxml';
// $filename = __DIR__.'/rc.jrxml';
// $filename = __DIR__.'/statement.jrxml';
// $filename = __DIR__.'/uat.jrxml';



$data = [];
$faker = Faker\Factory::create();
for($i=0;$i<1;$i++)
{
    $tmp=[
        'fullname' => $faker->name(),
        'email' => $faker->email(),
        'gender' => $faker->randomElement(['M', 'F']),
        'globaluser_id'=> $i+100000000,
        'description'=>$faker->text(),
        'country_code'=>$faker->randomElement(['SG','AU','US','MY'])
    ];
    array_push($data,$tmp);
}


// $config = ['driver'=>'postgresql','host'=>'127.0.0.1','user'=>'postgres','pass'=>'postgres','name'=>'backend']; //postgresql db
$config = ['driver'=>'dummy','data'=>$data];


$report = new PHPJasperXML();
$report->load_xml_file($filename)
    // ->setParameter(['para1'=>1,'para2'=>2])
    ->setDataSource($config)
    ->export('Pdf');

