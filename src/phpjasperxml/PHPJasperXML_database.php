<?php

namespace Simitsdk\phpjasperxml;

trait PHPJasperXML_database{
    protected $db = null;
    protected $rows = [];
    protected $rowcount = 0 ;
    
    public function connect(array $setting):self
    {
        
        if(empty($setting['driver']))
        {
            die('undefined db driver');
        }
        else
        {
            $driver = $setting['driver'];
            $driverfile = __DIR__.'/Dbdrivers/'.$driver.'.php';
            if(!file_exists($driverfile))
            {
                die("$driverfile does not exists");
            }
            else
            {
                $classname = '\\Simitsdk\\phpjasperxml\\dbdrivers\\' . ucfirst($driver);
                $this->db = new $classname($setting);     
                return $this;           
            }
        }   
    }


    public function transferDBtoArray(array $setting) : self
    {
        $this->connect($setting);
        $q = $this->db ->query("SELECT * FROM global_user");

        while($r=$this->db->fetchArray($q))
        {
            array_push($this->rows,$r);
            $this->rowcount++;
        }
        return $this;
    }

    public function setData(array $data):self
    {
        $this->rows = $data;
        return $this;
    }

}