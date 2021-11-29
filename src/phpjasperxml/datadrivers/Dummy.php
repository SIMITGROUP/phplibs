<?php
namespace Simitsdk\phpjasperxml\datadrivers;

class Dummy implements DataInterface
{
    protected $conn;
    protected array $data=[];
    public function __construct(array $config)
    {
        if(!isset($config['data']))
        {
            die('Undefine data in dummy driver');
        }
        else
        {
            $this->data=$config['data'];
        }
    }
    public function fetchData(mixed $querypara):array
    {
        
        return $this->data;
    }

}
