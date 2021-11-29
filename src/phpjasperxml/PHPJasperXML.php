<?php
namespace Simitsdk\phpjasperxml;

class PHPJasperXML{
    use PHPJasperXML_load;
    use PHPJasperXML_datasource;
    use PHPJasperXML_output;
    use PHPJasperXML_elements;
    use PHPJasperXML_expression;
    use Tools\Toolbox;
    public function __construct()
    {

    }


    public function __call($methodname, $args)
    {
        
        if(method_exists($this,$methodname))
        {
            die('method '.$methodname.' does not exists');
        }
        else
        {
            return call_user_func_array([$this,$methodname],$args);
        }
        
    }

    public function setParameter(array $paras=[]):self
    {
        foreach($paras as $k => $v)
        {
            if(isset($this->parameters[$k]))
            {
                $this->parameters[$k]['value']=$v;
            }
            else
            {
                $this->parameters[$k]=['name'=>$k,'value'=>$v,'class'=>'java.lang.String'];
            }
        }
        return $this;
    }
    
}


