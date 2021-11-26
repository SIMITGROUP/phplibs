<?php
namespace Simitsdk\phpjasperxml\Exports;

use Com\Tecnick\Color\Model\Rgb;

// use \tecnickcom\tcpdf;

class Pdf extends \TCPDF implements ExportInterface
{
    use \Simitsdk\phpjasperxml\Tools\Toolbox;
    protected $pagesettings=[];
    protected $bands=[];
    protected $elements=[];
    protected $bandsheight=[];
    protected $currentY=0;
    protected $defaultfont='times';
    protected $currentRowNo=0;
    public function __construct($prop)
    {           
        
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
        $this->AddPage();
    }

    public function defineBands(array $bands,array $elements)
    {
        
        $this->bands = $bands;
        $this->elements = $elements;        
    }
    public function setData(array $data)
    {
        $this->rows = $data;
    }
    public function getBandHeight(string $bandname):int
    {
        return isset($this->bands[$bandname]['height']) ? $this->bands[$bandname]['height'] : 0;
    }

    public function export()
    {
        $filename = '/tmp/aa.pdf';
        unlink($filename);
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
        $y = $prop['y']+$this->currentY;
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
        $backcolor = $this->convertColorStrToRGB($prop['backcolor']??'');
        $this->SetFillColor($backcolor['r'], $backcolor['g'],$backcolor['b']);
        $halign = !empty($prop['textAlignment']) ? $prop['textAlignment'] : 'L';
        $valign = !empty($prop['verticalAlignment']) ? $prop['verticalAlignment'] : 'T'; 
        $markup = !empty($prop['markup']) ? $prop['markup'] : '';
        $prop['fontName'] = $prop['fontName']?? $this->defaultfont;
        $fontName=strtolower($prop['fontName']);        
        $fontstyle='';
        $fontstyle.= !empty($prop['isBold']) ? 'B':'';
        $fontstyle.= !empty($prop['isItalic']) ? 'I':'';
        $fontstyle.= !empty($prop['isUnderline']) ? 'U':'';
        $fontsize= !empty($prop['size']) ? $prop['size'] : 8;        

        $this->SetFont($fontName, $fontstyle, $fontsize);
        
        if($isTextField)
        {
            $text = $prop['textFieldExpression'];
        }
        else
        {
            $text = $prop['text'];
        }
        $this->Cell($w,$h,$text);
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
    public function prepareBand(string $bandname):array
    {        
        $offsets=[];
        $methodname = 'draw_'.$bandname;
        $band = $this->bands[$bandname];
        $offsets = call_user_func([$this,$methodname],);

        $witdh = $this->getPageWidth() - $this->lMargin - $this->rMargin;
        $height = isset($band['height'])? $band['height'] : 0;
        
        if($height>0)
        {
            $offsetx = isset($offsets['x']) ? $offsets['x']: 0;
            $offsetx = (int)$offsetx;
            $offsety = isset($offsets['y']) ? $offsets['y']: 0 ;
            $offsety = (int)$offsety;
            $this->SetDrawColor(50, 0, 0, 0);
            $this->SetTextColor(100, 0, 0, 0);
            $this->currentY=$offsety;
            $this->SetXY($offsetx,$offsety);
            $this->Rect($offsetx,$offsety,$witdh ,$height);     
            $this->Cell($witdh,$height,$bandname,1);
            $offsets = ['x'=>$offsetx,'y'=>$offsety];
        }
        
        // echo "\n Print band $bandname, h = $height :".print_r($offsets,true)." \n";
        return $offsets;

    }
    
    public function draw_background()
    {        
        $offsety=$this->tMargin;
        $offset = ['x'=>$this->lMargin,'y'=>$offsety];
        $bandname='background';        
        //$this->drawBand($bandname,$offset);
        return $offset;
    }
    public function draw_title()
    {
        $offsety=$this->tMargin;
        $offset = ['x'=>$this->lMargin,'y'=>$offsety];
        $bandname='title';
        //$this->drawBand($bandname,$offset);
        return $offset;
        
    }
    public function draw_pageHeader()
    {
        if($this->PageNo() == 1)
        {
            $offsety = $this->tMargin + $this->getBandHeight('title');
        }
        else
        {
            $offsety = $this->tMargin;
        }
        
        $offset = ['x'=>$this->lMargin,'y'=>$offsety];
        $bandname='pageHeader';
        //$this->drawBand($bandname,$offset);
        return $offset;
    }
    public function draw_columnHeader()
    {
        if($this->PageNo() == 1)
        {
            $offsety = $this->tMargin + $this->getBandHeight('title') +  $this->getBandHeight('pageHeader');
        }
        else
        {
            $offsety = $this->tMargin + $this->getBandHeight('pageHeader');
        }
        $offset = ['x'=>$this->lMargin,'y'=>$offsety];
        $bandname='columnHeader';
        //$this->drawBand($bandname,$offset);
        return $offset;
    }
    public function draw_detail(string $detailno)
    {
        $offsety=$this->tMargin + $this->getBandHeight('title') +  $this->getBandHeight('pageHeader')+  $this->getBandHeight('columnHeader') + $this->getBandHeight('detail')*$this->currentRowNo;
        if($this->PageNo() == 1)
        {
            $offsety += $this->getBandHeight('title');
        }
        $offset = ['x'=>$this->lMargin,'y'=>$offsety];
        $bandname='detail';
        //$this->drawBand($bandname,$offset);
        return $offset;
    }

    public function setRowNumber(int $no)
    {
        $this->currentRowNo=$no;
    }
    public function draw_columnFooter()
    {
        
    }
    public function draw_pageFooter()
    {
        
        $pageheight = $this->getPageHeight();//1000
        $pagefooterheight =  $this->getBandHeight('pageFooter');
        $bottommargin =  $this->getFooterMargin();
        $offsety = $pageheight - $pagefooterheight - $bottommargin ;
        $offset = ['x'=>$this->lMargin,'y'=>$offsety];
        $bandname='pageFooter';
        //$this->drawBand($bandname,$offset);
        return $offset;
        
    }
    public function draw_lastPageFooter()
    {
        $pageheight = $this->getPageHeight();//1000
        $pagefooterheight =  $this->getBandHeight('lastPageFooter');
        $bottommargin =  $this->getFooterMargin();
        $offsety = $pageheight - $pagefooterheight - $bottommargin ;
        $offset = ['x'=>$this->lMargin,'y'=>$offsety];
        $bandname='pageFooter';
        //$this->drawBand($bandname,$offset);
        return $offset;
        
    }
    public function draw_summary()
    {
        $this->currentY=400;        
        $offsety = $this->currentY;
        $offset = ['x'=>$this->lMargin,'y'=>$offsety];
        $bandname='summary';
        //$this->drawBand($bandname,$offset);
        return $offset;
    }
    public function draw_noData()
    {
        $this->currentY=400;
        $offsety = $this->currentY;
        $offset = ['x'=>$this->lMargin,'y'=>$offsety];
        return $offset;
    }

    public function draw_groupsHeader()
    {
        $this->currentY=400;
        $offsety = $this->currentY;
        $offset = ['x'=>$this->lMargin,'y'=>$offsety];
        return $offset;
    }
    public function draw_groupsFooter()
    {
        $this->currentY=400;
        $offsety = $this->currentY;
        $offset = ['x'=>$this->lMargin,'y'=>$offsety];
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


    protected function convertColorStrToRGB(string $colorstr):array
    {
        return array('forecolor'=>$colorstr,"r"=>hexdec(substr($colorstr,1,2)),"g"=>hexdec(substr($colorstr,3,2)),"b"=>hexdec(substr($colorstr,5,2)));
    }
}