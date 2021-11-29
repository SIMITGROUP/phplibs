<?php

namespace Simitsdk\phpjasperxml;

trait PHPJasperXML_datasource{
    protected $db = null;
    protected $rows = [];
    protected $rowcount = 0 ;
    protected $dataloaded=false;
    public function setDataSource(array $setting):self
    {
        
        if(empty($setting['driver']))
        {
            die('undefined db driver');
        }
        else
        {
            $driver = $setting['driver'];
            $driverfile = __DIR__.'/datadrivers/'.$driver.'.php';
            if(!file_exists($driverfile))
            {
                die("$driverfile does not exists");
            }
            else
            {
                $classname = '\\Simitsdk\\phpjasperxml\\datadrivers\\' . ucfirst($driver);
                $this->db = new $classname($setting);     
                $this->fetchData();
                return $this;           
            }
        }   
    }


    public function fetchData() : self
    {
        $sql = $this->parseExpression($this->querystring);
        $data =$this->db->fetchData($sql);
        $this->loadData($data);
        return $this;
    }

    public function loadData(array $data):self
    {
        $this->dataloaded=true;
        $this->rowcount = count($data);
        $this->rows = $data;
        return $this;
    }

}