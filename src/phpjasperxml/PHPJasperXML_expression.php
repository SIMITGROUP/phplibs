<?php

namespace Simitsdk\phpjasperxml;

use Throwable;

trait PHPJasperXML_expression
{
    protected bool $validate = true;
    protected array $resettypes = ['Report','Group','None','Page'];
    protected bool $debugtxt = false;

    protected function executeExpression(string $expression,int $addrowqty=0): mixed
    {
        $value = $this->parseExpression($expression,$addrowqty);

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
            die("Cannot eval formula \"$evalstr\"");

        }
    }
    protected function parseExpression(string $expression,int $addrowqty=0): string
    {
        $value = $expression;
        // echo "\nexpression: $expression :";
        $fieldpattern = '/\$F{(.*?)}/';
        $varpattern = '/\$V{(.*?)}/';
        $parapattern = '/\$P{(.*?)}/';


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
            $data = $data ?? '';
            // $value = str_replace('$F{'.$fieldname.'}', $data,$value);
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
            $value = str_replace($parastrings[$p], $data,$value);
        }
        // echo "$value\n";
        return $value;
        
        
    }
    
    protected function resetGroupValue(string $varname,array $setting, int $rowno)
    {
        $calculation = $setting['calculation'];
        $variableExpression = $setting['variableExpression'];
        $resetGroup = $setting['resetGroup'];
        $compute = $setting['compute'];
        $groupExpression = $setting['groupExpression'];
        $lastgroupevalue = $compute['groupExpressionValue'];
        $newgroupvalue = $this->parseExpression($groupExpression);
        if($calculation == 'Count')
        {
            $tmpvar = 1;
        }
        else
        {
            $tmpvar = $this->parseExpression($variableExpression);
        }
        
        
        if($lastgroupevalue != $newgroupvalue)
        {
            $compute['groupExpressionValue'] = $newgroupvalue;
            switch($calculation)
            {
                case 'Count':
                    $tmpvar = 1;
                break;
            
            }
            //reset all variable which is under next level of group too
        }
        else
        {
            $varvalue=$tmpvar;
        }

        /*
            foreach($this->groups as $groupname=>$groupsetting)
            {
                $value=0;
                //if group value not exists, then add 1
                if(!isset($compute['Group'][$groupname]))
                {
                    $compute['Group'][$groupname]=0;
                }
                
                $lastgroupvalue = $this->group[$groupname]['value'];
                $groupExpression = $this->group[$groupname]['groupExpression'];
                $newgroupvalue = $this->parseExpression($groupExpression);
                
                if($calculation=='Count')
                {
                    $value = 1;
                }
                else
                {
                    
                    $value = $this->parseExpression($variableExpression);
                }
                
                if($newgroupvalue !== $lastgroupvalue || $resettherest==true) //reset
                {
                    $computer['Group'][$groupname]=$value;
                    $resettherest=true;
                }
                else //increament
                {
                    $compute['Group'][$groupname]++;
                }
                // $computer['Group'][$groupname] = !isset($computer['Group'][$groupname]) ? 1 : $compute['Group'][$groupname]++;
            }
        */
    }

    protected function computeVariables(int $rowno)
    {
        foreach($this->variables as $varname=>$setting)
        {
            echo $varname;
            print_r($setting);
            $setting['calculation']=$setting['calculation']??'';
            $setting['incrementType']=$setting['incrementType']??'';
            $setting['initialValueExpression']=$setting['initialValueExpression']??'';
            $setting['variableExpression']=$setting['initialValueExpression']??'';
            $setting['incrementType']=$setting['incrementType']??'';
            $setting['incrementGroup']=$setting['incrementGroup']??'';
            $setting['resetType']=$setting['resetType']??'None';
            $resettype = $setting['resetType'];
            $setting['compute']=$setting['compute'] ?? [];
            $setting['resetGroup']=$setting['resetGroup']??'';
                
            $setting['compute']['lastresetvalue']='--no reset value yet--';
            


            if(!in_array($resettype,$this->resettypes ))
            {
                $msg = sprintf("variable %s using unsupported resettype %s",$varname,$resettype);
                die($msg);
            }
            $calculation = $setting['calculation'];
            if(!empty($calculation))
            {
                $computeMethodName = 'compute_'.$calculation;
            }
            else
            {
                $computeMethodName = 'compute_None';
            }

            if(!method_exists($this,$computeMethodName))
            {
                $msg = sprintf("Variable '%s' use calculation type '%s' which is not supported, '%s()' not found",$varname,$calculation,$computeMethodName);
                if($this->debugtxt) echo "\n".$msg."\n";
            }
            else
            {
                $computevalue = call_user_func([$this,$computeMethodName],$varname,$setting,$rowno);
            }
            
            /*
                switch($setting['calculation'])
                {
                    case 'Count':
                        $compute = $setting['compute']??[];
                        //define count in overall
                        $compute['Report']=$i;
                        
                        //define count by page
                        if(!isset($compute['PageNo']))
                        {
                            $compute['PageNo'] = $this->output->PageNo(); 
                        }
                        if($compute['PageNo'] != $this->output->PageNo())
                        {
                            $compute['Page']=0;
                        }
                        $compute['Page']++;

                        //define count by group
                        if(!isset($compute['Group']))
                        {
                            $compute['Group']=[];
                        }
                        $resettherest=false;
                        foreach($this->groups as $groupname=>$groupsetting)
                        {
                            //if group value not exists, then add 1
                            if(!isset($compute['Group'][$groupname]))
                            {
                                $compute['Group'][$groupname]=0;
                            }
                            
                            $lastgroupvalue = $this->group[$groupname]['value'];
                            $groupExpression = $this->group[$groupname]['groupExpression'];
                            $newgroupvalue = $this->parseExpression($groupExpression);
                            if($newgroupvalue !== $lastgroupvalue || $resettherest==true) //reset
                            {
                                $computer['Group'][$groupname]=1;
                                $resettherest=true;
                            }
                            else //increament
                            {
                                $compute['Group'][$groupname]++;
                            }
                            // $computer['Group'][$groupname] = !isset($computer['Group'][$groupname]) ? 1 : $compute['Group'][$groupname]++;
                        }
                        
                        // $setting['compute'] = $this->computeVar_Count();
                        
                        if($resettype == 'Group')
                        {
                            $groupname = $setting['resetGroup'];
                            $setting['value']=$setting['compute']['Group'][$groupname];
                        }
                        else
                        {
                            $setting['value']=$setting['compute'][$resettype];
                        }                     

                        break;
                    case 'Sum':
                        // $this->variables[$varname]['value'];
                        break;
                    case 'Average':
                        break;
                    case 'Lowest':
                        break;
                    case 'Highest':
                        break;
                    case 'Standard Deviation':
                        break;
                    case 'Variance':
                        break;
                    case 'System':
                        break;
                    case 'First':
                        break;
                    case 'Distinct Count':
                        break;
                    case '':
                        break;
                    default:
                        break;
                }
            */
        }
        // die;
    }

    protected function compute_Count(string $varname,array $setting, int $rowno): array
    {        
        $resettype = $setting['resetType'];        
        $lastresetvalue = $setting['lastresetvalue'];
        
        
        if($resettype=='Page')
        {  
            $newresetvalue = $this->output->PageNo();
        }
        else if($resettype =='Group')
        {
            $resetGroup = $setting['resetGroup'];
            $newresetvalue = $this->groups[$resetGroup]['value'];            
        }
        else
        {
            $newresetvalue = ($rowno+1);
        }

        if($newresetvalue != $lastresetvalue)
        {
            $setting['lastresetvalue']=$newresetvalue;
            $setting['value']=1;
        }
        else
        {
            $setting['value']+=1;
        }        
        return $setting;
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
            if($this->validate)
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
        $datatype = $this->parameters[$key]['datatype'];
        
        $value = $this->escapeIfRequire($value,$datatype);
        return $value ; 
    }
    protected function getVariableValue($key)
    {        
        switch($key)
        {
            case 'PAGE_NUMBER':
                return $this->output->PageNo();
            break;
            case 'MASTER_CURRENT_PAGE':
                return '***MASTER_CURRENT_PAGE NOT SUPPORTED***';
            break;
            case 'MASTER_TOTAL_PAGES':
                return '***MASTER_TOTAL_PAGES NOT SUPPORTED***';
            break;
            case 'COLUMN_NUMBER':
                return $this->output->ColumnNo();
            break;
            case 'COLUMN_COUNT':
                return $this->output->columnCount();
            break;
            case 'REPORT_COUNT':                
                return ($this->currentRow+1);
            break;
            case 'PAGE_COUNT':
                return $this->output->getNumPages();
            break;
            default:
            
                if(!isset($this->variables[$key]))
                {
                    foreach($this->groups as $groupname => $groupsetting )
                    {
                        $varname_groupcount = $groupname.'_COUNT';
                        if($key==$varname_groupcount)
                        {
                            // echo "\n$key:\n";
                            // print_r($groupsetting);
                            return $groupsetting['count'];
                        }
                    }
                    if($this->validate)
                    {
                        die("variable \"$key\" is not defined in report");
                    }
                    else
                    {
                        $data= '';
                    }                    
                }
                else
                {
                    $data = $this->variables[$key]['value'];
                }
                $datatype = $this->variables[$key]['datatype'];

                return $this->escapeIfRequire($data,$datatype);
            break;
        }
    }


    public function escapeIfRequire(mixed $value,mixed $datatype): mixed
    {
        if(gettype($datatype)=='null')
        {
            $datatype = 'string';
        }
        $data = $value;
        switch($datatype)
        {
            case 'string':
                $data = "'".addslashes($value)."'";
            break;
            default:
                
            break;
        }
        return $data;
    }
}