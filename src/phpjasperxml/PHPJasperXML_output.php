<?php

namespace Simitsdk\phpjasperxml;

trait PHPJasperXML_output
{                    
    protected array $pageproperties=[];
    protected $output = null;
    protected int $currentRow=0;
    protected array $descgroupnames=[];
    protected array $row = [];
    
    // protected $bandsequence = [];
    public function export(string $type)
    {
        $classname = '\\Simitsdk\\phpjasperxml\\Exports\\'.ucfirst($type);
        $this->output  = new $classname($this->pageproperties);        
        $this->output->defineBands($this->bands,$this->elements,$this->groups);
        if($this->rowcount>0)
        {
            foreach($this->rows as $i=>$r)
            {
                $this->setRow($i);
                if($i==0)
                {
                   $this->newPage(true);
                }
                $postfix='';
                if($this->printOrder=='Horizontal')
                {
                    $postfix='Horizontal';                                    
                }
                call_user_func([$this,'draw_groupsHeader'.$postfix]);
                call_user_func([$this,'draw_detail'.$postfix]);
                call_user_func([$this,'draw_groupsFooter'.$postfix]);
                // call_user_func([$this.'draw_groupsFooter'.$postfix]);
                
            }
            $this->endPage();
        }
        else
        {
            $this->draw_noData();
        }
        $this->output->export();
    }
    protected function newBlankPage()
    {
        $this->output->AddPage();     
        $this->draw_background();     
    }
    protected function newPage($withTitle=false)
    {
        
        if(!$withTitle)
        {
            $this->draw_columnFooter();
            $this->draw_pageFooter(); //if no content, it will call draw_pageFooter
        }    
        echo "\nAdd Page\n";
        $this->output->AddPage();          
        $this->draw_background();
        if($withTitle)
        {
            $this->draw_title();
        }        
        $this->draw_pageHeader();
        $this->prepareColumn();
        $this->draw_columnHeader();
        
    }
    protected function nextColumn()
    {
        if($this->output->getColumnNo()<$this->columnCount-1 )
        {
            $this->draw_columnFooter();
            $this->output->nextColumn();
            $this->draw_columnHeader();
        }
        else
        {
            $this->output->setColumnNo(0);
        }
        
    }
    protected function prepareColumn()
    {
        $this->output->prepareColumn($this->columnCount,$this->columnWidth);
    }
    protected function endPage()
    {        
        $this->draw_columnFooter();
        $this->draw_summary();
        $this->draw_lastPageFooter(); //if no content, it will call draw_pageFooter
    }
    protected function setRow(int $i)
    {
        $this->currentRow=$i;
        $this->row = $this->rows[$i];
        $this->computeVariables($i);
        $this->output->setRowNumber($i);
        
    }

    
    
    protected function drawBand(string $bandname, mixed $callback=null)
    {
        $offsets = $this->output->prepareBand($bandname,$callback);
        $offsetx=(int)$offsets['x'];
        $offsety=(int)$offsets['y'];

        // echo "\n$bandname: $offsetx $offsety\n";
        $height = $this->bands[$bandname]['height'];
        if($height>0)
        {
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
        foreach($this->groups as $groupname=>$groupsetting)
        {
            $bandname = $this->groupbandprefix.$groupname.'_header';            
            if($groupsetting['ischange'])
            {
                
                if($groupsetting['isStartNewPage'] && $this->currentRow>0)
                {

                    $this->newPage();
                }
                else if($groupsetting['isStartNewColumn'] && $this->currentRow>0)
                {
                    $currentcolumn = $this->output->getColumnNo();
                    $pageno = $this->output->PageNo();
                    echo "\n isStartNewColumn ($pageno) $this->columnCount == $currentcolumn + 1 \n";
                    if($this->columnCount == $currentcolumn + 1 )
                    {
                        echo "\n new page:\n";
                        $this->newPage();
                        $pageno = $this->output->PageNo();
                        echo "\n new page with no $pageno\n";
                    }
                    else
                    {
                        echo "\nnew column\n";
                        $this->nextColumn();
                    }
                    
                }
                $this->groups[$groupname]['ischange']=false;
                echo "\ngroup $groupname\n";
                // print_r($this->groups[$groupname]);
                $this->output->groups[$groupname]['ischange']=false;
                $groupExpression = $groupsetting['groupExpression'];
                $newgroupvalue = $this->parseExpression($groupExpression);
                $this->groups[$groupname]['value'] = $newgroupvalue;                

                $this->drawBand($bandname,function(){
                    
                    if($this->printOrder=='Vertical')
                    {
                        $columnno = $this->output->getColumnNo();
                        if($columnno == $this->columnCount -1)
                        {
                            $this->newPage();
                        }
                        else
                        {
                            $this->nextColumn();
                        }
                        
                    }
                    else
                    {
                        $this->maxDetailEndY=0;
                        $this->newPage();
                    }
                    // $this->nextColumn();
                });
            }
            
        }
        
    }

    /**
     * draw detail bands(multiple) element
     */
    protected function draw_detail(string $mode='Vertical')
    {
        
        foreach($this->bands as $bandname=>$setting)
        {
            if(str_contains($bandname,'detail_'))    
            {                
                $this->drawBand($bandname,function() use ($mode){
                    
                    if($mode=='Vertical')
                    {
                        $columnno = $this->output->getColumnNo();
                        if($columnno == $this->columnCount -1)
                        {
                            $this->newPage();
                        }
                        else
                        {
                            $this->nextColumn();
                        }
                        
                    }
                    else
                    {
                        $this->maxDetailEndY=0;
                        $this->newPage();
                    }
                    
                });
            }
        }        
    }


    // protected function resetGroupIfRequire(int $rowno=null)
    // {
    //     $resettherest = false;        
    //     foreach($this->groups as $groupname=>$groupsetting)
    //     {
    //         $groupExpression = $groupsetting['groupExpression'];
    //         $lastgroupvalue = $groupsetting['value'];
    //         $newgroupvalue = $this->parseExpression($groupExpression,$rowno);
            
    //         if($lastgroupvalue != $newgroupvalue)
    //         {
    //             $resettherest=true;
    //             $this->groups[$groupname]['value'] = $newgroupvalue;                
    //         }
            

    //         if($resettherest)
    //         {                
    //             $this->groups[$groupname]['count']=0;
    //             $this->groups[$groupname]['ischange']=true;
    //             $this->output->groups[$groupname]['ischange']=true;
    //             //reset all variables under this group
    //             foreach($this->variables as $varname=>$varsetting)
    //             {
    //                 $this->variables[$varname]['value']='--value reset--';
    //                 $this->variables[$varname]['lastresetvalue']='--lastvalue reset--';
    //             }
                
    //         }
    //         else
    //         {
    //             $this->groups[$groupname]['ischange']=false;
    //             $this->output->groups[$groupname]['ischange']=false;
    //         }

    //         $this->groups[$groupname]['count']++;
            
    //     }
    // }
    protected function identifyGroupChange(): bool
    {

        //$this->resetGroupIfRequire(($i+1));        
        $this->descgroupnames = [];
        $resettherest=false;
        foreach($this->groups as $groupname=>$groupsetting)
        {
            $groupExpression = $groupsetting['groupExpression'];
            $lastgroupvalue = $groupsetting['value'];
            $newgroupvalue = $this->parseExpression($groupExpression,1);
            
            if($lastgroupvalue != $newgroupvalue)
            {
                $resettherest=true;
                $this->groups[$groupname]['value'] = $newgroupvalue;                
            }

            if($resettherest)
            {                
                // echo "\nchanged group $groupname\n";
                $this->groups[$groupname]['count']=0;
                $this->groups[$groupname]['ischange']=true;
                $this->output->groups[$groupname]['ischange']=true;
                //reset all variables under this group
                foreach($this->variables as $varname=>$varsetting)
                {
                    $this->variables[$varname]['value']='--value reset--';
                    $this->variables[$varname]['lastresetvalue']='--lastvalue reset--';
                }
                
            }
            else
            {
                $this->groups[$groupname]['ischange']=false;
                $this->output->groups[$groupname]['ischange']=false;
            }


            array_push($this->descgroupnames,$groupname);                        
        }        
        return $resettherest;
    }
    protected function draw_groupsFooter()
    {
        $this->identifyGroupChange();
        for($i=count($this->descgroupnames)-1;$i>=0;$i--)
        {                        
            $groupname = $this->descgroupnames[$i];
            $bandname = $this->groupbandprefix.$groupname.'_footer';
            // echo "\n currentRow $this->currentRow ==  ($this->rowcount-1) rowcount-1 \n";
            if($this->groups[$groupname]['ischange'] || $this->currentRow == ($this->rowcount-1) )
            {
                $this->drawBand($bandname,function(){
                    $this->nextColumn();
                });
            }
            
        }   
    }
    protected function draw_columnFooter()
    {
        $this->drawBand('columnFooter');
    }
    protected function draw_summary()
    {        
        $this->drawBand('summary',function(){
            $this->newBlankPage();
            return $this->output->getMargin('top');
        });
    }
    protected function draw_lastPageFooter()
    {        
        if($this->bands['lastPageFooter']['height']==0)
        {
            $this->draw_pageFooter();
        }
        else
        {
            $this->drawBand('lastPageFooter');
        }
    }
    protected function draw_pageFooter()
    {
        $this->drawBand('pageFooter');
    }
}