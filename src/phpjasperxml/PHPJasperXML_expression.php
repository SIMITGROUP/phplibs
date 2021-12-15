<?php

namespace Simitsdk\phpjasperxml;

use Throwable;

trait PHPJasperXML_expression
{
    protected bool $validate = true;
    protected array $resettypes = ['Report','Group','None','Page'];
    protected bool $debugtxt = false;


    protected function isDisplay(string $expression)
    {
        $result=true;
        if(!empty($expression))
        {
            $result = $this->executeExpression($expression);        
        }
        // $this->console("expression $expression === $result");
        return $result;
    }
    protected function executeExpression(string $expression,int $addrowqty=0): mixed
    {   
        // echo "\n  executeExpression: $expression\n";
        $value = $this->parseExpression($expression,$addrowqty);

        //special result, direct return raw value
        if(gettype($value)=='object' || gettype($value)=='array')
        {
            return $value;
        }
        //it consist of string, use concate instead of maths operation
        if(str_contains($value,'"') || str_contains($value,"'"))
        {
            $value = str_replace('+',' . ',$value);
        }
        
        
        $evalstr = "return $value;";
        try{
            $finalvalue = eval($evalstr);
            return $finalvalue;
        }catch(Throwable $err){
            die("Cannot eval formula \"$evalstr\"".$err->getMessage());

        }
    }

    protected function parseExpression(string $expression,int $addrowqty=0): mixed
    {
        $value = $expression;
        $fieldpattern = '/\$F{(.*?)}/';
        $varpattern = '/\$V{(.*?)}/';
        $parapattern = '/\$P{(.*?)}/';

        // echo "\nparseExpression $expression\n";
        preg_match_all($fieldpattern, $value, $matchfield);
        preg_match_all($varpattern, $value, $matchvar);
        preg_match_all($parapattern, $value, $matchpara);

        $fieldstrings = $matchfield[0];
        $fieldnames = $matchfield[1];        
        $parastrings = $matchpara[0];
        $paranames = $matchpara[1];
        $varstrings = $matchvar[0];
        $varnames = $matchvar[1];    
        
        foreach($fieldnames as $f => $fieldname)
        {
            $data = $this->getFieldValue($fieldname,$addrowqty);
            $value = str_replace($fieldstrings[$f], $data,$value);
        }
        foreach($varnames as $v => $varname)
        {
            $data = $this->getVariableValue($varname);
            $value = str_replace($varstrings[$v], $data,$value);
        }
        foreach($paranames as $p => $paraname)
        {
            $data = $this->getParameterValue($paraname);
            if(gettype($data)=='array' || gettype($data)=='object')
            {
                return $data;
            }
            $value = str_replace($parastrings[$p], $data,$value);
        }
        return $value;        
    }    


    protected function getFieldValue(string $name,int $addrowqty=0)
    {
        $rowno = $this->currentRow+$addrowqty;
        $datatype = $this->fields[$name]['datatype'];
        if(isset($this->rows[$rowno]))
        {
            $row=$this->rows[$rowno] ;
            $value=$row[$name];
        }
        else
        {
            $value=null;
        }
                
        $value = $this->escapeIfRequire($value,$datatype);
        return $value;
    }


    protected function getParameterValue($key)
    {
        $value=null;
        if(!isset($this->parameters[$key]))
        {
            if($key=='REPORT_CONNECTION')
            {
                $value = 'REPORT_CONNECTION';
            }
            else if(str_contains($key,'_SCRIPTLET'))
            {
                $scriptletname = str_replace('_SCRIPTLET','',$key);                
                if(isset($this->scriptlets[$scriptletname]))
                {                    
                    $value = $this->executeExpression($this->scriptlets[$scriptletname]);
                }
                else
                {
                    die("Scriptlet $scriptletname undefined!");
                }
            }
            else if($this->validate)
            {
                die("parameter \"$key\" is not defined in report");
            }
            else
            {
                $value =  '';
            }                    
        }
        else
        {
            
            $value = $this->parameters[$key]['value'];
        } 
        $datatype = $this->parameters[$key]['datatype']??'string';
        $value = $this->escapeIfRequire($value,$datatype);
        return $value ; 
    }


    protected function getVariableValue($key)
    {        
        // echo "\n getVariableValue $key: \n";
        $datatype = "number";//by default all datatype is number, unless variable class defined
        switch($key)
        {
            case 'PAGE_NUMBER':
                $result = $this->output->PageNo();
            break;
            case 'MASTER_CURRENT_PAGE':
                $result = '***MASTER_CURRENT_PAGE NOT SUPPORTED***';
            break;
            case 'MASTER_TOTAL_PAGES':
                $result = '***MASTER_TOTAL_PAGES NOT SUPPORTED***';
            break;
            case 'COLUMN_NUMBER':
                $result = $this->output->ColumnNo();
            break;
            case 'COLUMN_COUNT':
                $result = $this->output->columnCount();
            break;
            case 'REPORT_COUNT':                
                $result = ($this->currentRow+1);
            break;
            case 'PAGE_COUNT':
                $result = $this->output->getNumPages();
            break;
            default:
            
                if(!isset($this->variables[$key]))
                {
                    foreach($this->groups as $groupname => $groupsetting )
                    {
                        $varname_groupcount = $groupname.'_COUNT';
                        
                        if($key==$varname_groupcount)
                        {
                            $data = $groupsetting['count'];
                        }
                    }
                }
                else
                {
                    $data = $this->variables[$key]['value'];
                    $datatype = $this->variables[$key]['datatype'];
                }

                
                // echo "\nvar $key type = $datatype, data = $data \n";
                $result = $this->escapeIfRequire($data,$datatype);
            break;
        }
        return $result ;
    }

    /**
     * if the value is string, add single quote. beside, if specicial character exists, escape it
     * @param mixed $value string/numbers/boolean/null
     * @param mixed $datatype string, number, boolean or null
     * @return mixed $data string or number value;
     */
    public function escapeIfRequire(mixed $value,mixed $datatype): mixed
    {
        if(gettype($datatype)=='NULL')
        {
            $datatype = 'string';
        }
        switch($datatype)
        {
            case 'array':
                return $value;
            break;
            case 'number':
            case 'boolean':
                if(gettype($value)=='NULL')
                {
                    $value = 0;
                }
                $data =  $value ?? 0;
            break;
            default:
            case 'string':                
                if(gettype($value)=='NULL')
                {
                    $value = '';
                }
                $data = "'".addslashes($value)."'";
            break;
        }
        return (string) $data;
    }
}