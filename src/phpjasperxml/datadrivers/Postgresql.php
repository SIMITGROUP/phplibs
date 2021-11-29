<?php
namespace Simitsdk\phpjasperxml\datadrivers;

class Postgresql implements DataInterface
{
    protected $conn;
    public function __construct(array $config)
    {
        
        
        $this->conn = $this->connect($config);
        if(!$this->conn)
        {
            die('Cannot connect to postgresql');
        }
    }

    
        
    public function fetchData(mixed $querypara):array
    {
        $q = $this->query($querypara);
        $data=[];
        while($r=$this->fetchArray($q))
        {
            array_push($data,$r);
        }

        return $data;
    }

    /**************************** internal method ******************************/
    /**************************** internal method ******************************/
    /**************************** internal method ******************************/
    protected function connect(array $config)
    {
        $host = $config['host'];
        $user =  $config['user'];
        $pass =  $config['pass'];
        $name =  $config['name'];
        $cnstring = sprintf("host=%s user=%s password=%s dbname=%s options='--client_encoding=UTF8 '",$host,$user,$pass,$name);        
        $cn = pg_connect($cnstring,PGSQL_CONNECT_FORCE_NEW);        
        return $cn;
    }

    protected function query(string $sql) 
    {   
        $query = pg_query($this->conn, $sql);        
        return $query;
    }

    protected function fetchArray($q)
    {
        try 
        {
                $row = pg_fetch_assoc($q);
                return $row;                
        }
        catch (\Throwable $e)
        {
            // Throw error.
        }
    }
    // public function fetchFields($q): array
    // {
    //     try
    //     {
    //         if($q)
    //         {                                
    //             $data =[];
    //             $fieldqty = pg_num_fields($q);
    //             for ($i = 0; $i < $fieldqty; $i++)
    //             {
    //                 $fieldname = pg_field_name($q, $i);
    //                 $fieldtype =  pg_field_type($q, $i);
    //                 $data[$fieldname]=[
    //                     'datatype'=>$fieldtype,
    //                 ];
    //             }

    //             return $data;
    //         }
    //         else
    //         {
    //             return [];
    //         }
    //     }
    //     catch(\Exception $e) 
    //     {
    //         return [];
    //     }
    // }

    // public function num_rows($q) : int
    // {
    //     return pg_num_rows($q);
    // }
}