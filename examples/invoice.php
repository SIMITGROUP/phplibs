<?php
include "main.php";

use simitsdk\phpjasperxml\PHPJasperXML;
$filename = __DIR__.'/invoice.jrxml';


$data = [];
$faker = Faker\Factory::create('en_US');

$email = $faker->email();
$customername = $faker->company();
$phoneNumber = $faker->phoneNumber();
$customeraddress = $faker->address()."\n".$faker->city()." ".$faker->postcode()."\n".$faker->country();
$invoiceno="IV00001";
$totalamount = 0;
$totalqty = 0;
for($i=0;$i<20;$i++)
{
    $qty = $faker->numberBetween(1,20);
    $unitprice = $faker->numberBetween(10,300).'.00';
    $lineamount = $qty * $unitprice;
    $totalamount +=$lineamount;
    $totalqty +=$qty;
    $attn = $faker->name;
    $tmp=[
        'customername'=>$customername,
        'customeraddress'=>$customeraddress,
        'customercontact'=>"\nAttn: $attn\nTel: $phoneNumber Email: $email",
        'invoiceno'=>$invoiceno,
        'salesagent'=>$faker->name,
        'termname'=>'30 Days',
        'invoicedate'=> date('Y-m-d'),
        'itemname'=>'Item '. ($i+1),
        'description'=>$faker->text(800),
        'qty'=>$qty,
        'unit'=>'Ea',
        'unitprice'=> $unitprice,
        'linetotal'=> $lineamount,
        'totalamount'=>$totalamount,
        'totalqty'=>$totalqty,
        'statustxt'=>'Draft',
    ];
    $data[$i]=$tmp;
}


$config = ['driver'=>'hasharray','data'=>$data];
$paras = [
    'companyname'=>$faker->company(),
    'address'=>"10, block 10, Street 1, Street 2, Street3, 112345, MY",
    'registrationno'=>'Company Reg. No: AB-00998877-UUU',
    'contacts'=>'Tel:'.$faker->phoneNumber(). ' Email: '.$faker->email(),
    'documenttitle'=>'INVOICE',
];

$report = new PHPJasperXML();
$report->load_xml_file($filename)    
    ->setParameter($paras)
    ->setDataSource($config)
    ->export('Pdf');

