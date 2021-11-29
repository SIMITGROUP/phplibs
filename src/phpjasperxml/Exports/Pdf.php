<?php
namespace Simitsdk\phpjasperxml\Exports;

use Com\Tecnick\Color\Model\Rgb;

// use \tecnickcom\tcpdf;

class Pdf extends \TCPDF implements ExportInterface
{
    use \Simitsdk\phpjasperxml\Tools\Toolbox;
    protected array $pagesettings=[];
    protected array $bands=[];
    protected string $lastdetailband='';
    protected array $elements=[];    
    protected int $groupcount=0;
    protected int $currentY=0;
    protected int $maxY=0;
    protected int $columnno=1;
    protected string $defaultfont='helvetica';
    protected int $currentRowNo=0;
    protected bool $debugband=true;
    public function __construct($prop)
    {           
        $this->pagesettings=$prop;
        
        $orientation = isset($prop['orientation'])? $this->left($prop['orientation'],1):'P';
        $unit='pt';
        $format=[(int)$prop['pageWidth'],(int)$prop['pageHeight']];
        $encoding='UTF-8';
        parent::__construct($orientation,$unit,$format,$encoding);  
        $this->SetAutoPageBreak(false);

        $this->setPrintHeader(false);        
        $this->setPrintFooter(false);
        $this->SetCreator('ks');
        $this->SetAuthor('simitsdk');
        $this->SetTitle('sample pdf');
        $this->SetSubject('subject1');
        $this->SetKeywords('keyword1');
        
    }

    public function NewPage()
    {
        $this->AddPage();
    }
    public function defineBands(array $bands,array $elements,int $groupcount)
    {
        
        $this->bands = $bands;
        $this->elements = $elements;      
        $this->groupcount = $groupcount;
        foreach($bands as $b=>$setting)
        {
            if(str_contains($b,'detail'))
            {
                $this->lastdetailband = $b;
            }
        }  
    }
    public function setData(array $data)
    {
        $this->rows = $data;
    }
    public function getBandHeight(string $bandname):int
    {
        return isset($this->bands[$bandname]['height']) ? $this->bands[$bandname]['height'] : 0;
    }
    public function ColumnNo():int
    {
        return $this->columnno;
    }
    public function PageNo():int
    {
        return parent::PageNo();
    }
    public function export()
    {
        $filename = '/tmp/sample1.pdf';
        // unlink($filename);
        // $this->Output($filename,'F');   //send out the complete page

        $this->Output($filename,'F');
    }

    //*********************************************** draw elements ***************************************************/    
    //*********************************************** draw elements ***************************************************/    
    //*********************************************** draw elements ***************************************************/    
    
    /**
     * draw all report elements according position
     */
    public function drawElement(string $uuid, array $prop,int $offsetx,int $offsety)
    {        
        // $prop = $this->prop($obj->reportElement);
        $x = $prop['x']+$offsetx;
        $y = $prop['y']+$offsety;//$this->currentY;
        $height = $prop['height'];
        $width = $prop['width'];

        // echo "draw element $uuid $obj->type:".print_r($prop,true)."\n";
        $this->setPosition($x,$y);
        $methodname = 'draw_'.$prop['type'];
        call_user_func([$this,$methodname],$uuid,$prop);
        
    }

    public function draw_line(string $uuid,array $prop)
    {
        // $prop = $this->prop($obj->reportElement);
        $x1=$this->GetX();
        $y1=$this->GetY();
        $x2=$x1+$prop['width'];
        $y2=$y1+$prop['height'];
        $forecolor = $this->convertColorStrToRGB($prop['forecolor']??'');
        // $this->SetDrawColor(50, 0, 0, 0);
        // $this->SetTextColor(100, 0, 0, 0);
        $this->SetDrawColor($forecolor['r'], $forecolor['g'],$forecolor['b']);
        $this->Line($x1,$y1,$x2,$y2);
        // echo "\ndrawline  $uuid ".print_r($prop,true)."\n";
    }

    public function draw_rectangle(string $uuid,array $prop)
    {
        $x=$this->GetX();
        $y=$this->GetY();
        $w = $prop['width'];
        $h = $prop['height'];
        $prop['forecolor'] = $prop['forecolor'] ??'';
        $prop['backcolor'] = $prop['backcolor'] ??'#FFFFFF';
        $forecolor = $this->convertColorStrToRGB($prop['forecolor']);
        $backcolor = $this->convertColorStrToRGB($prop['backcolor']);        
        
        
        // $fillcolors = [$backcolor['r'], $backcolor['g'],$backcolor['b']];
        $this->SetDrawColor($forecolor['r'], $forecolor['g'],$forecolor['b']);        
        $this->SetFillColor($backcolor['r'], $backcolor['g'],$backcolor['b']);

        if(isset($prop['mode']) && $prop['mode'] == 'Transparent')
        {
            $style='';
        }
        else
        {
            $style='FD';
        }        
        $this->Rect($x,$y,$w,$h,$style,[]);        
    }
    public function draw_ellipse(string $uuid,array $prop)
    {
        
        $w = $prop['width'];
        $h = $prop['height'];
        $rx =   $w/2;
        $ry =   $h/2;
        $x=$this->GetX() + $rx;
        $y=$this->GetY() + $ry;
    
        $prop['forecolor'] = $prop['forecolor'] ??'';
        $prop['backcolor'] = $prop['backcolor'] ??'#FFFFFF';
        $forecolor = $this->convertColorStrToRGB($prop['forecolor']);
        $backcolor = $this->convertColorStrToRGB($prop['backcolor']);

        // $fillcolors = [$backcolor['r'], $backcolor['g'],$backcolor['b']];
        $this->SetDrawColor($forecolor['r'], $forecolor['g'],$forecolor['b']);        
        $this->SetFillColor($backcolor['r'], $backcolor['g'],$backcolor['b']);
        if(isset($prop['mode']) && $prop['mode'] == 'Transparent')
        {
            $style='';
        }
        else
        {
            $style='FD';
        }

        $this->Ellipse($x,$y,$rx,$ry,0,0,360,$style);
    }
    public function draw_break(string $uuid,array $prop)
    {
        $this->AddPage();
        // $x=$this->GetX();
        // $y=$this->GetY();
        // $w = $prop['width'];
        // $h = $prop['height'];
        // $this->Rect($x,$y,$w,$h);
        // echo "\ndraw_rectangle  $uuid ".print_r($prop,true)."\n";
    }
    public function draw_staticText(string $uuid,array $prop,bool $isTextField=false)
    {
        $w=$prop['width'];
        $h=$prop['height'];        
        $forecolor = $this->convertColorStrToRGB($prop['forecolor']??'');
        $this->SetTextColor($forecolor["r"],$forecolor["g"],$forecolor["b"]);        
        $isfill=false;
        if(!empty($prop['backcolor']))
        {
            $isfill = true;
        }
        $backcolor = $this->convertColorStrToRGB($prop['backcolor']??'');
        $this->SetFillColor($backcolor['r'], $backcolor['g'],$backcolor['b']);
        $halign = !empty($prop['textAlignment']) ? $prop['textAlignment'] : 'L';
        $halign = $this->left($halign,1);
        //textAlignment="Right", L,C,R,J
        $valign = !empty($prop['verticalAlignment']) ? $prop['verticalAlignment'] : 'T'; 
        //B,C,T
        $markup = !empty($prop['markup']) ? $prop['markup'] : '';
        $prop['fontName'] = $prop['fontName']?? $this->defaultfont;
        $fontName=strtolower($prop['fontName']);        
        $fontstyle='';
        $fontstyle.= !empty($prop['isBold']) ? 'B':'';
        $fontstyle.= !empty($prop['isItalic']) ? 'I':'';
        $fontstyle.= !empty($prop['isUnderline']) ? 'U':'';
        $fontsize= !empty($prop['size']) ? $prop['size'] : 8;        
        
        $topPenlineWidth = !empty($prop['topPenlineWidth']) ? $prop['topPenlineWidth'] : 0; 
        $bottomPenlineWidth = !empty($prop['bottomPenlineWidth']) ? $prop['bottomPenlineWidth'] : 0; 
        $leftPenlineWidth = !empty($prop['leftPenlineWidth']) ? $prop['leftPenlineWidth'] : 0; 
        $rightPenlineWidth = !empty($prop['rightPenlineWidth']) ? $prop['rightPenlineWidth'] : 0; 
        $border='';
        
        $border.= ($topPenlineWidth>0)?'T':'';   
        $border.= ($bottomPenlineWidth>0)?'B':'';   
        $border.= ($leftPenlineWidth>0)?'L':'';   
        $border.= ($rightPenlineWidth>0)?'R':'';   
        
        $this->SetFont($fontName, $fontstyle, $fontsize);
        
        if($isTextField)
        {
            $text = $prop['textFieldExpression'];
        }
        else
        {
            $text = $prop['text'];
        }
        $this->Cell($w,$h,$text,$border,0,$halign,$isfill);
    }
    public function draw_textField(string $uuid,array $prop)
    {
        $this->draw_staticText($uuid,$prop,true);        
    }

    /****************************** draw all bands ********************************/
    /****************************** draw all bands ********************************/
    /****************************** draw all bands ********************************/
    /****************************** draw all bands ********************************/
    /****************************** draw all bands ********************************/

    /**
     * prepare band in pdf, and return x,y offsets
     * @param 
     */
    public function prepareBand(string $bandname, mixed $callback=null):array
    {        
        $offsets=[];
        echo "\nprepareband $bandname\n";
        if(str_contains($bandname,'detail'))
        {
            $methodname = 'draw_detail';
            $band = $this->bands[$bandname];
            $offsets = call_user_func([$this,$methodname],$bandname,$callback);
        }
        else if(str_contains($bandname,'group_'))
        {
            $methodname = 'draw_group';
            $band = $this->bands[$bandname];
            $offsets = call_user_func([$this,$methodname],$bandname,$callback);
        }        
        else if(in_array($bandname,['summary']))
        {
            
            $methodname = 'draw_'.$bandname;
            $band = $this->bands[$bandname];
            $offsets = call_user_func([$this,$methodname],$callback);
        }
        else
        {
            $methodname = 'draw_'.$bandname;
            $band = $this->bands[$bandname];
            $offsets = call_user_func([$this,$methodname],);
            
        }
        
        // echo "\n$methodname\n";
        // print_r($offsets);
        

        $witdh = $this->getPageWidth() - $this->lMargin - $this->rMargin;
        $height = isset($band['height'])? $band['height'] : 0;
        $offsety=0;
        if($height>0)
        {
            $offsetx = isset($offsets['x']) ? $offsets['x']: 0;
            $offsetx = (int)$offsetx;
            $offsety = isset($offsets['y']) ? $offsets['y']: 0 ;
            $offsety = (int)$offsety;
            $this->maxY=$offsety+$height;
            $this->currentY=$offsety;
            $this->SetXY($offsetx,$offsety);
            $offsets = ['x'=>$offsetx,'y'=>$offsety];
            if($this->debugband)
            {
                $this->SetFontSize(8);
                $this->SetDrawColor(50, 0, 0, 0);
                $this->SetTextColor(100, 0, 0, 0);            
                $this->Rect($offsetx,$offsety,$witdh ,$height);     
                $this->Cell($witdh,10,$bandname,0);    
            }
            
        }
        $this->bands[$bandname]['endY']=$offsety+$height;
        // echo "\n Print band $bandname, h = $height :".print_r($offsets,true)." \n";
        return $offsets;

    }
    
    public function draw_background()
    {        
        $offsety=$this->pagesettings['topMargin'];
        $offset = ['x'=>$this->pagesettings['leftMargin'],'y'=>$offsety];
        return $offset;
    }
    public function draw_title()
    {
        $offsety=$this->pagesettings['topMargin'];
        $offset = ['x'=>$this->pagesettings['leftMargin'],'y'=>$offsety];
        return $offset;
        
    }
    public function draw_pageHeader()
    {
        if($this->PageNo() == 1)
        {
            $offsety = $this->pagesettings['topMargin'] + $this->getBandHeight('title');
        }
        else
        {
            $offsety = $this->pagesettings['topMargin'];
        }
        
        $offset = ['x'=>$this->pagesettings['leftMargin'],'y'=>$offsety];
        return $offset;
    }
    public function draw_columnHeader()
    {
        if($this->PageNo() == 1)
        {
            $offsety = $this->pagesettings['topMargin'] + $this->getBandHeight('title') +  $this->getBandHeight('pageHeader');
        }
        else
        {
            $offsety = $this->pagesettings['topMargin'] + $this->getBandHeight('pageHeader');
        }
        $offset = ['x'=>$this->pagesettings['leftMargin'],'y'=>$offsety];
        //$this->drawBand($bandname,$offset);
        return $offset;
    }

    public function setDetailNextPage(string $detailname)
    {

    }
    public function draw_detail(string $detailbandname,mixed $callback=null)
    {
        $detailno = (int)str_replace('detail_','',$detailbandname);
        $totaldetailheight = 0;
        $offsetx = $this->pagesettings['leftMargin'];
        
        $prevband='';
        if($detailbandname =='detail_0')
        {
            
            if($this->groupcount>0)
            {
                $prevband='lastgroupband********************';
            }
            else
            {
                if($this->currentRowNo==0)
                {
                    $prevband='columnHeader';
                }
                else
                {
                    $prevband=$this->lastdetailband;
                }  
            }
            
        }
        else
        {
            $prevband = 'detail_'.((int)$detailno -1 );
        }
        $offsety = $this->bands[$prevband]['endY'];    
        
        if($this->PageNo() == 1)
        {
            $offsety += $this->getBandHeight('title');
        }

        $estimateY=$offsety+$this->getBandHeight($detailbandname);
        if($this->isEndDetailSpace($estimateY) && gettype($callback)=='object')
        {            
            $callback();
            $offsety = $this->bands['columnHeader']['endY'];    
        }

        $offset = ['x'=>$offsetx, 'y'=>$offsety];
        return $offset;
    }
    protected function isEndDetailSpace(int $estimateY)
    {
        $offsets = $this->draw_columnFooter();
        $offsety=$offsets['y'];
        if($estimateY > $offsety)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    public function setRowNumber(int $no)
    {
        $this->currentRowNo=$no;
    }
    public function draw_columnFooter()
    {        
        $pageheight = $this->getPageHeight();//1000
        $pagefooterheight =  $this->getBandHeight('pageFooter');
        $bottommargin =  $this->pagesettings['bottomMargin'];        
        $columnfooterheight = $this->getBandHeight('columnFooter');
        $offsety = $pageheight - $pagefooterheight - $bottommargin - $columnfooterheight;
        $offset = ['x'=>$this->pagesettings['leftMargin'] ,'y'=>$offsety];
        
        return $offset;
        
    }
    public function draw_pageFooter()
    {
        
        $pageheight = $this->getPageHeight();
        $pagefooterheight =  $this->getBandHeight('pageFooter');
        $bottommargin =  $this->pagesettings['bottomMargin'];
        
        $offsety = $pageheight - $pagefooterheight - $bottommargin ;
        $offset = ['x'=>$this->pagesettings['leftMargin'],'y'=>$offsety];

        return $offset;
        
    }
    public function draw_lastPageFooter()
    {
        return $this->draw_pageFooter();        
    }
    public function draw_summary(mixed $callback=null)
    {
        $offsety = $this->bands[$this->lastdetailband]['endY'];
        $estimateY=$offsety+$this->getBandHeight('summary');
        if($this->isEndDetailSpace($estimateY) && gettype($callback)=='object')
        {            
            $callback();
            $offsety = $this->bands['columnHeader']['endY'];    
        }


        $offset = ['x'=>$this->pagesettings['leftMargin'],'y'=>$offsety];        
        return $offset;
    }
    public function draw_noData()
    {
        
        $offsety = $this->pagesettings['topMargin'];
        $offset = ['x'=>$this->pagesettings['leftMargin'],'y'=>$offsety];
        return $offset;
    }

    public function draw_group(string $bandname)
    {
        
        echo "\ndraw_group:".$bandname."\n";
        $this->currentY=400;
        $offsety = $this->currentY;
        $offset = ['x'=>$this->pagesettings['leftMargin'],'y'=>$offsety];
        return $offset;
    }


    /*************** misc function ****************/
    
    protected function setPosition(int $x,int $y)
    {
        $this->SetXY($x,$y);
    }

    
    public function __call($methodname,$args)
    {
        if(!method_exists($this,$methodname))
        {
            echo "\n$methodname() does not exists\n";
        }
    }

    public function columnCount(): int
    {
        return $this->pagesettings['columnCount'];
    }

    public function getNumPages(): int
    {
        return parent::getNumPages();
    }

    protected function convertColorStrToRGB(string $colorstr):array
    {
        return array('forecolor'=>$colorstr,"r"=>hexdec(substr($colorstr,1,2)),"g"=>hexdec(substr($colorstr,3,2)),"b"=>hexdec(substr($colorstr,5,2)));
    }
}