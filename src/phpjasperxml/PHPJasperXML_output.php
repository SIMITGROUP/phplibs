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
                
                $this->draw_groupsHeader();
                $this->draw_detail();                        
                $this->draw_groupsFooter();
            }
            $this->endPage();
        }
        else
        {
            $this->draw_noData();
        }
        $this->output->export();
    }
    protected function newPage($withTitle=false)
    {
        
        if(!$withTitle)
        {
            $this->draw_columnFooter();
            $this->draw_pageFooter(); //if no content, it will call draw_pageFooter
        }    

        $this->output->AddPage();          
        $this->draw_background();
        if($withTitle)
        {
            $this->draw_title();
        }        
        $this->draw_pageHeader();
        $this->draw_columnHeader();
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

    protected function draw_groupsHeader(string $groupname='')
    {
        foreach($this->groups as $groupname=>$groupsetting)
        {
            $bandname = $this->groupbandprefix.$groupname.'_header';            
            if($groupsetting['ischange'])
            {
                $this->groups[$groupname]['ischange']=false;
                $this->output->groups[$groupname]['ischange']=false;
                $groupExpression = $groupsetting['groupExpression'];
                $newgroupvalue = $this->parseExpression($groupExpression);
                $this->groups[$groupname]['value'] = $newgroupvalue;                

                $this->drawBand($bandname,function(){
                    $this->newPage();
                });
            }
            
        }
        
    }

    /**
     * draw detail bands(multiple) element
     */
    protected function draw_detail()
    {
        foreach($this->bands as $bandname=>$setting)
        {
            if(str_contains($bandname,'detail_'))    
            {                
                $this->drawBand($bandname,function(){
                    $this->newPage();
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
    protected function draw_groupsFooter()
    {

        //$this->resetGroupIfRequire(($i+1));        
        $descgroupnames = [];
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


            array_push($descgroupnames,$groupname);                        
        }        

        for($i=count($descgroupnames)-1;$i>=0;$i--)
        {                        
            $groupname = $descgroupnames[$i];
            $bandname = $this->groupbandprefix.$groupname.'_footer';
            // echo "\n currentRow $this->currentRow ==  ($this->rowcount-1) rowcount-1 \n";
            if($this->groups[$groupname]['ischange'] || $this->currentRow == ($this->rowcount-1) )
            {
                $this->drawBand($bandname,function(){
                    $this->newPage();
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
            $this->newPage();
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