<?php

namespace Simitsdk\phpjasperxml;

trait PHPJasperXML_output
{                    
    protected array $pageproperties=[];
    protected $output = null;
    protected int $currentRow=0;
    protected array $row = [];
    // protected $bandsequence = [];
    public function export(string $type)
    {
        $classname = '\\Simitsdk\\phpjasperxml\\Exports\\'.ucfirst($type);
        $this->output  = new $classname($this->pageproperties);        
        $this->output->defineBands($this->bands,$this->elements);
        // print_r($this->bands);die;
        if($this->rowcount>0)
        {
            
            $this->setRow(0);
            $this->draw_background();
            $this->draw_title();
            $this->draw_pageHeader();
            $this->draw_columnHeader();
            // $this->draw_groupsHeader();            

            foreach($this->rows as $i=>$r)
            {
                $this->setRow($i);
                $this->draw_detail();
            }
            
            
            // $this->draw_groupsFooter();
            $this->draw_columnFooter();
            $this->draw_summary();
            $this->draw_lastPageFooter(); //if no content, it will call draw_pageFooter
        }
        else
        {
            $this->draw_noData();
        }
        $this->output->export();
    }

    protected function setRow(int $i)
    {
        $this->currentRow=$i;
        $this->row = $this->rows[$i];
        $this->output->setRowNumber($i);
    }

    protected function drawBand(string $bandname)
    {
        $offsets = $this->output->prepareBand($bandname);
        $offsetx=(int)$offsets['x'];
        $offsety=(int)$offsets['y'];

        // echo "\n$bandname: $offsetx $offsety\n";
        foreach($this->elements[$bandname] as $uuid =>$element)
        {
            $tmp = $element;
            if(isset($tmp['textFieldExpression']))
            {
                $tmp['textFieldExpression']=$this->parseExpression($tmp['textFieldExpression']);
            }
            $this->output->drawElement($uuid,$tmp,$offsetx,$offsety);
        }
    }
    protected function draw_background()
    {        
        $this->drawBand('background');
    }

    protected function draw_title()
    {
        $this->drawBand('title');
    }

    protected function draw_pageHeader()
    {
        $this->drawBand('pageHeader');
    }

    protected function draw_columnHeader()
    {
        $this->drawBand('columnHeader');
    }

    protected function draw_groupsHeader()
    {
        $this->drawBand('groupsHeader');
    }

    //may consist multiple detail band
    protected function draw_detail()
    {
        foreach($this->bands as $bandname=>$setting)
        {

            if(str_contains($bandname,'detail_'))    
            {
                echo "$bandname, ";
                $this->drawBand($bandname);
            }
        }
        die;
        
    }
    protected function draw_groupsFooter()
    {
        $this->drawBand('groupsFooter');
    }
    protected function draw_columnFooter()
    {
        $this->drawBand('columnFooter');
    }
    protected function draw_summary()
    {
        $this->drawBand('summary');
    }
    protected function draw_lastPageFooter()
    {
        $this->drawBand('lastPageFooter');
    }
    protected function draw_pageFooter()
    {
        $this->drawBand('pageFooter');
    }    



}