<?php

namespace Simitsdk\phpjasperxml;

trait PHPJasperXML_expression
{

    protected function parseExpression(string $expression): string
    {
        $value = $expression;
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
            $data = $this->getFieldValue($fieldname);
            $value = str_replace('$F{'.$fieldname.'}', $data,$value);
        }
        foreach($varnames as $v => $varname)
        {
            $data = $this->getVariableValue($varname);
            $value = str_replace('$V{'.$fieldname.'}', $data,$value);
        }
        foreach($paranames as $p => $paraname)
        {
            $data = $this->getParameterValue($paraname);
            $value = str_replace('$P{'.$fieldname.'}', $data,$value);
        }

        return $value;
    }

    protected function getParameterValue(string $name)
    {
        $value=$this->parameters[$name]['value'];
        return $value;
    }
    protected function getVariableValue(string $name)
    {
        $value=$this->variables[$name]['value'];
        return $value;
    }
    protected function getFieldValue(string $name)
    {
        $value=$this->row[$name];
        return $value;
    }
}