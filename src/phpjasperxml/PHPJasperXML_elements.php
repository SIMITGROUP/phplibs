<?php

namespace Simitsdk\phpjasperxml;

use SimpleXMLElement;

trait PHPJasperXML_elements
{


    /************************************************************************************/
    /*************************** supported elements *************************************/
    /************************************************************************************/
    /**
     * initialize line element's parameter in report, combine simple xml object attribute into $prop
     * @param array $prop properties setting
     * @param object $obj element object in simplexml 
     * @return array $prop 
     */
    protected function element_line(array $prop, object $obj): array
    {        
        if(gettype($obj->graphicElement->pen)=='object')
        {
            $prop=$this->appendprop($prop,$obj->graphicElement->pen);
        }
        return $prop;
    }

    /**
     * draw line element in report
     * @param string $uuid unique id
     * @param array $prop
     */
    protected function draw_line(string $uuid,array $prop)
    {
        $this->output->draw_line($uuid,$prop);
    }

    /**
     * initialize rectangle element's parameter in report, combine simple xml object attribute into $prop
     * @param array $prop properties setting
     * @param object $obj element object in simplexml 
     * @return array $prop 
     */
    protected function element_rectangle(array $prop, object $obj): array
    {
        $prop=$this->appendprop($prop,$obj->graphicElement->pen);        
        return $prop;
    }

    /**
     * draw rectangle element in report
     * @param string $uuid unique id
     * @param array $prop
     */
    public function draw_rectangle(string $uuid,array $prop){
        $this->output->draw_rectangle($uuid,$prop);
    }

    /**
     * initialize ellipse element's parameter in report, combine simple xml object attribute into $prop
     * @param array $prop properties setting
     * @param object $obj element object in simplexml 
     * @return array $prop 
     */
    protected function element_ellipse(array $prop, object $obj): array
    {
        $prop=$this->appendprop($prop,$obj->graphicElement->pen);        
        return $prop;
    }

    /**
     * draw ellipse element in report
     * @param string $uuid unique id
     * @param array $prop
     */
    public function draw_ellipse(string $uuid,array $prop)
    {
        
        $this->output->draw_ellipse($uuid,$prop);
    }

    /**
     * initialize image element's parameter in report, combine simple xml object attribute into $prop
     * @param array $prop properties setting
     * @param object $obj element object in simplexml 
     * @return array $prop 
     */
    protected function element_image(array $prop, object $obj): array
    {
        $prop['imageExpression']= (string)$obj->imageExpression;
        $prop = $this->addBorders($prop,$obj);
        return $prop;
    }

    /**
     * draw image element in report
     * @param string $uuid unique id
     * @param array $prop
     */
    protected function draw_image(string $uuid,array $prop)
    {
        $prop['imageExpression'] = $this->executeExpression($prop['imageExpression']);
        // $prop['hyperlinkReferenceExpression'] = $prop['hyperlinkReferenceExpression']?? '';
        
        
        
        $this->output->draw_image($uuid,$prop);
    }

    /**
     * initialize page break element's parameter in report, combine simple xml object attribute into $prop
     * @param array $prop properties setting
     * @param object $obj element object in simplexml 
     * @return array $prop 
     */
    protected function element_break(array $prop, object $obj): array
    {
        return $prop;
    }
    
    /**
     * add page break element in report
     * @param string $uuid unique id
     * @param array $prop
     */
    public function draw_break(string $uuid,array $prop){
        $this->output->draw_break($uuid,$prop);
    }
    
    /**
     * initialize static text element's parameter in report, combine simple xml object attribute into $prop
     * @param array $prop properties setting
     * @param object $obj element object in simplexml 
     * @return array $prop 
     */
    protected function element_staticText(array $prop, object $obj): array
    {
        if(isset($obj->textElement->font))
        {
            $prop = $this->appendprop($prop,$obj->textElement->font);
        }        
        $prop = $this->addBorders($prop,$obj);
        if(isset($obj->text))
        {
            $prop['text']=$obj->text;
        }        
        return $prop;
    }


    /**
     * draw static text in report
     * @param string $uuid unique id
     * @param array $prop
     */
    public function draw_staticText(string $uuid,array $prop,bool $isTextField=false){
        // $link = $prop['hyperlinkReferenceExpression']??'';
        
        // if(!empty($link))
        // {
        //     // $this->console("link $link");
        //     $prop['hyperlinkReferenceExpression'] = $this->executeExpression($link);
        // }
        $this->output->draw_staticText($uuid,$prop);
    }

    /**
     * initialize textField element's parameter in report, combine simple xml object attribute into $prop
     * @param array $prop properties setting
     * @param object $obj element object in simplexml 
     * @return array $prop 
     */
    protected function element_textField(array $prop, object $obj): array
    {      
        $prop = $this->element_staticText($prop,$obj);          
        $prop['textFieldExpression']=$obj->textFieldExpression;  
        if(isset($obj->patternExpression))      
        {
            $prop['patternExpression']=(string)$obj->patternExpression;
        }
        return $prop;
    }

    /**
     * draw line textField in report
     * @param string $uuid unique id
     * @param array $prop
     */
    public function draw_textField(string $uuid,array $prop){
        $prop['textFieldExpression']=$this->executeExpression($prop['textFieldExpression']);
        // $link = $prop['hyperlinkReferenceExpression']??'';        
        if(!empty($prop['patternExpression']))
        {
            $prop['pattern']= $this->executeExpression($prop['patternExpression']);
        }
        // if(!empty($link))
        // {
        //     $prop['hyperlinkReferenceExpression'] = $this->executeExpression($link);
        // }
        $this->output->draw_textField($uuid,$prop);
    }

    /**
     * initialize frame element's parameter in report, combine simple xml object attribute into $prop
     * @param array $prop properties setting
     * @param object $obj element object in simplexml 
     * @return array $prop 
     */
    protected function element_frame(array $prop, object $obj): array
    {
        $prop = $this->addBorders($prop,$obj);
        // if(isset($obj->box))
        // {
        //     $prop=$this->appendprop($prop,$obj->box);
        //     if(isset($obj->box->pen))
        //     {
        //         $prop=$this->appendprop($prop,$obj->box->pen);
        //     }         
        // }        
        return $prop;
    }

    /**
     * draw line frame in report
     * @param string $uuid unique id
     * @param array $prop
     */
    /**
     * draw rectangle element in report
     * @param string $uuid unique id
     * @param array $prop
     */
    public function draw_frame(string $uuid,array $prop){
        $this->output->draw_frame($uuid,$prop);
    }
    

    /**************************************************************************************/
    /*************************** unsupported elements *************************************/
    /**************************************************************************************/
    protected function element_genericElement(array $prop, object $obj): array
    {
        return $prop;
    }    
    public function draw_genericElement(string $uuid,array $prop)
    {
        $this->output->draw_unsupportedElement($uuid,$prop);
    }

    // protected function element_frame(array $prop, object $obj): array
    // {
    //     return $prop;
    // }
    // public function draw_frame(string $uuid,array $prop)
    // {
    //     $this->output->draw_unsupportedElement($uuid,$prop);
    // }
    protected function element_subreport(array $prop, object $obj): array
    {
        return $prop;
    }
    public function draw_subreport(string $uuid,array $prop)
    {
        $this->output->draw_unsupportedElement($uuid,$prop);
    }
    protected function element_componentElement(array $prop, SimpleXMLElement $obj): array
    {        
        $subtype='';
        $childtypes = ['jr','c','sc','cvc'];
        foreach($childtypes as $childtype)
        {
            $children = $obj->children($childtype,true);
            foreach($children as $k=>$v)
            {
                $subtype=$k;
                $prop['subtype']=$k;
               
            }  
        }
        // print_r($prop);
        return $prop;
    }
    public function draw_componentElement(string $uuid,array $prop)
    {
        $this->output->draw_unsupportedElement($uuid,$prop);
    }

    protected function element_crosstab(array $prop, object $obj): array
    {        
        return $prop;
    }
    public function draw_crosstab(string $uuid,array $prop)
    {
        $this->output->draw_unsupportedElement($uuid,$prop);
    }
    
    public function element_chart(array $prop, object $obj): array
    {        
        return $prop;
    }

    public function draw_chart(string $uuid,array $prop)
    {
        $this->output->draw_unsupportedElement($uuid,$prop);
    }

    
    



    
    
    
    /**************************************************************************************/
    /****************************** misc functions ****************************************/
    /**************************************************************************************/

    protected function drawElement(string $uuid,array $prop,int $offsetx,int $offsety)
    {
                // $prop = $this->prop($obj->reportElement);
                $x = $prop['x']+$offsetx;
                $y = $prop['y']+$offsety;//$this->currentY;
                $height = $prop['height'];
                $width = $prop['width'];
        
                // $this->console("early draw element $uuid x=$x, y=$y\n");
                if(isset($prop['hyperlinkReferenceExpression']))
                {
                    $prop['hyperlinkReferenceExpression'] = $this->executeExpression($prop['hyperlinkReferenceExpression']);
                }
                $this->output->setPosition($x,$y);                
                $methodname = 'draw_'.$prop['type'];
                call_user_func([$this,$methodname],$uuid,$prop);
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