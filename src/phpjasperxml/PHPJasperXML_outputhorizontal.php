<?php

namespace Simitsdk\phpjasperxml;

trait PHPJasperXML_outputhorizontal{
    protected $currentRowTop=0;
    protected $maxDetailEndY=0;
    protected function draw_columnHeaderHorizontal()
    {
        
    }
    protected function draw_columnFooterHorizontal()
    {

    }
    protected function draw_groupsHeaderHorizontal()
    {
        
        //set lastX at first column        
        // $this->output->setColumnNo(0);
        $lastcolumno = $this->output->getColumnNo();
        $this->output->setColumnNo(0);
        $this->draw_groupsHeader();
        $this->output->setColumnNo($lastcolumno);
        $this->currentRowTop = $this->output->getLastBandEndY();
        
        //output print group
            //set callback use next page instead
        // echo 'a';   
    }
    protected function draw_detailHorizontal()
    {           
        // $lastdetailendy = $this->output->getLastBandEndY();
        
        $this->draw_detail('Horizontal');
        $currentEndY= $this->output->getLastBandEndY();
        if($currentEndY > $this->maxDetailEndY)
        {
            $this->maxDetailEndY =  $currentEndY;
        }

        echo "\ndraw_detailHorizontal maxDetailEndY $this->maxDetailEndY\n";
        $this->setNextAvailableSlot();
    }
    protected function setNextAvailableSlot()
    {
        $this->output->nextColumn();
        if($this->output->getColumnNo() == $this->columnCount)
        {
            $this->output->setColumnNo(0);            
        }
        $this->output->setLastBandEndY($this->currentRowTop);
    }

    protected function draw_groupsFooterHorizontal()
    {
        $endY=$this->maxDetailEndY;
        echo "\ndraw_groupsFooterHorizontal begin with  $endY\n";
        $isgroupchange = $this->identifyGroupChange();
        if($isgroupchange)
        {
            $this->output->setColumnNo(0);
            $this->output->setLastBandEndY($endY);
        }
        for($i=count($this->descgroupnames)-1;$i>=0;$i--)
        {                        
            $groupname = $this->descgroupnames[$i];
            $bandname = $this->groupbandprefix.$groupname.'_footer';

            // echo "\n currentRow $this->currentRow ==  ($this->rowcount-1) rowcount-1 \n";
            if($this->groups[$groupname]['ischange'] || $this->currentRow == ($this->rowcount-1) )
            {                                
                // $lastdetailendy = $this->bands[$this->lastbandname]['endY'];
                // $this->output->setLastBandEndY($this->maxDetailEndY);
                $this->drawBand($bandname,function(){
                    $this->nextColumn();
                });                
            }
            
        }   


        // $lastcolumno = $this->output->getColumnNo();
        // $this->output->setColumnNo(0);
        // $this->draw_groupsFooter();
        // $this->output->setColumnNo($lastcolumno);
        // $this->currentRowTop = $this->maxDetailEndY;// $this->output->getLastBandEndY();
        // $this->output->setLastBandEndY($this->currentRowTop);
        
        
       
        
    }

    protected function nextColumnHorizontal()
    {
        if($this->output->getColumnNo()<$this->columnCount-1 )
        {
            // $this->draw_columnFooter();
            $this->output->setLastBandEndY($this->currentRowTop);
            $this->output->nextColumn();
            
            // $this->draw_columnHeader();
        }
        else
        {
            $this->currentRowTop = $this->output->getLastBandEndY();
            $this->output->setColumnNo(0);
            
        }
        // die($this->output->getColumnNo().":$this->columnCount nomore");
        
    }
}
