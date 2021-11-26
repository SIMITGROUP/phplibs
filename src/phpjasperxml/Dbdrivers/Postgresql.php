<?php
namespace Simitsdk\phpjasperxml\Dbdrivers;

class Postgresql
{
    protected $conn;
    public function __construct(array $config)
    {
        $host = $config['host'];
        $user =  $config['user'];
        $pass =  $config['pass'];
        $name =  $config['name'];
        
        $this->conn = $this->connect($host, $user, $pass, $name);                
        if(!$this->conn)
        {
            die('Cannot connect to postgresql');
        }
    }

    public function connect(string $host, string $user, string $pass, string $name)
    {
        $cnstring = sprintf("host=%s user=%s password=%s dbname=%s options='--client_encoding=UTF8 '",$host,$user,$pass,$name);        
        $cn = pg_connect($cnstring,PGSQL_CONNECT_FORCE_NEW);        
        return $cn;
    }

    public function query(string $sql) 
    {   
        $query = pg_query($this->conn, $sql);        
        return $query;
    }

    public function fetchArray($q)
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
        
    public function fetchFields($q): array
    {
        try
        {
            if($q)
            {                                
                $data =[];
                $fieldqty = pg_num_fields($q);
                for ($i = 0; $i < $fieldqty; $i++)
                {
                    $fieldname = pg_field_name($q, $i);
                    $fieldtype =  pg_field_type($q, $i);
                    $data[$fieldname]=[
                        'datatype'=>$fieldtype,
                    ];
                }

                return $data;
            }
            else
            {
                return [];
            }
        }
        catch(\Exception $e) 
        {
            return [];
        }
    }

    public function num_rows($q) : int
    {
        return pg_num_rows($q);
    }
}