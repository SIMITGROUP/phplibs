<?php

namespace Simitsdk\phpjasperxml;

trait PHPJasperXML_elements{

    protected function element_textField(array $prop, object $obj): array
    {                        
        if(isset($obj->textElement->font))
        {
            $prop = $this->appendprop($prop,$obj->textElement->font);
        }
        $prop = $this->addBorders($prop,$obj);        
        $prop['textFieldExpression']=$obj->textFieldExpression;
        $prop['printWhenExpression']=$obj->printWhenExpression;        
        return $prop;
    }

    protected function element_staticText(array $prop, object $obj): array
    {
        if(isset($obj->textElement->font))
        {
            $prop = $this->appendprop($prop,$obj->textElement->font);
        }        
        $prop = $this->addBorders($prop,$obj);
        $prop['text']=$obj->text;
        $prop['printWhenExpression']=$obj->printWhenExpression;        
        return $prop;
    }

    protected function element_line(array $prop, object $obj): array
    {        
        if(gettype($obj->graphicElement->pen)=='object')
        {
            $prop=$this->appendprop($prop,$obj->graphicElement->pen);
        }
        return $prop;
    }
    protected function element_rectangle(array $prop, object $obj): array
    {
        return $prop;
    }
    protected function element_ellipse(array $prop, object $obj): array
    {
        return $prop;
    }
    protected function element_image(array $prop, object $obj): array
    {
        return $prop;
    }
    protected function element_subreport(array $prop, object $obj): array
    {
        return $prop;
    }

    protected function element_break(array $prop, object $obj): array
    {
        return $prop;
    }
    
    protected function addBorders(array $prop, object $obj): array
    {
        if(isset($obj->box))
        {
            $prop = $this->appendprop($prop, $obj->box->pen,'pen');
            $prop = $this->appendprop($prop, $obj->box->topPen,'topPen');
            $prop = $this->appendprop($prop, $obj->box->leftPen,'leftPen');
            $prop = $this->appendprop($prop, $obj->box->bottomPen,'bottomPen');
            $prop = $this->appendprop($prop, $obj->box->rightPen,'rightPen');
        }
        return $prop;
    }

}