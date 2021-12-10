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
    public array $groups=[];
    public array $groupnames=[];
    protected int $currentY=0;
    protected int $lastBandEndY=0;
    protected int $lastColumnEndX=0;
    protected int $maxY=0;
    protected int $columnno=0;
    protected int $columnWidth;
    protected int $columnCount;
    protected string $defaultfont='helvetica';
    protected int $currentRowNo=0;
    protected bool $debugband=true;
    protected string $groupbandprefix = 'report_group_';
    protected int $printbandcount=0;
    public function __construct($prop)
    {           
        $this->pagesettings=$prop;
        // $fontfolder = sys_get_temp_dir().'/phpjasperxml/fonts';        
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

    public function setLastBandEndY(int $lastBandEndY)
    {
        $this->lastBandEndY=$lastBandEndY;
    }
    public function getLastBandEndY()
    {
        return $this->lastBandEndY;
    }

    public function NewPage()
    {
        $this->AddPage();        
    }
    
    public function isFontExists(string $fontname): bool
    {
        return true;
    }
    public function nextColumn()
    {
        $this->columnno++;
        // echo "\n nextColumn $this->columnno\n";
    }
    public function getColumnNo()
    {
        return $this->columnno;
    }
    public function setColumnNo(int $columnno)
    {        
        $this->columnno = $columnno;
        // echo "\nsetColumnNo $this->columnno\n";
    }
    public function defineBands(array $bands,array $elements,array $groups)
    {
        
        $this->bands = $bands;
        $this->elements = $elements;              
        $this->groups = $groups;
        foreach($groups as $gname=>$gsetting)
        {
            array_push($this->groupnames,$gname);
        }
        foreach($bands as $b=>$setting)
        {
            if(str_contains($b,'detail'))
            {
                $this->lastdetailband = $b;
            }
        }  
    }
    public function getMargin(string $location)
    {
        return $this->pagesettings[$location.'Margin'];
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
    public function export(string $filename='')
    {
        // if(empty($filename))
        {
            $filename = '/tmp/sample1.pdf';
        }
        
        // unlink($filename);
        // $this->Output($filename,'F');   //send out the complete page
        // print_r($this->bands);
        $this->Output($filename,'F');
        // echo $filename;
    }

    //*********************************************** draw elements ***************************************************/    
    //*********************************************** draw elements ***************************************************/    
    //*********************************************** draw elements ***************************************************/    
    
    /**
     * draw all report elements according position
     */
    // public function drawElement(string $uuid, array $prop,int $offsetx,int $offsety)
    // {        
    //     // $prop = $this->prop($obj->reportElement);
    //     $x = $prop['x']+$offsetx;
    //     $y = $prop['y']+$offsety;//$this->currentY;
    //     $height = $prop['height'];
    //     $width = $prop['width'];

    //     // echo "draw element $uuid $obj->type:".print_r($prop,true)."\n";
    //     $this->setPosition($x,$y);
    //     $methodname = 'draw_'.$prop['type'];
    //     call_user_func([$this,$methodname],$uuid,$prop);
        
    // }

    public function draw_line(string $uuid,array $prop)
    {
        $x1=$this->GetX();
        $y1=$this->GetY();
        $x2=$x1+$prop['width'];
        $y2=$y1+$prop['height'];
        $forecolor = $this->convertColorStrToRGB($prop['lineColor']??'');        
        $dash="";
        $lineWidth = $prop['lineWidth'];
        $prop["lineStyle"]=$prop["lineStyle"]?? '';
        switch($prop["lineStyle"])
        {
            case "Dotted":
                $dash=sprintf("%d,%d",$lineWidth,$lineWidth);
            break;
            case "Dashed":
                $dash=sprintf("%d,%d",$lineWidth*4,$lineWidth*2);
                break;
            default:
                $dash="";
            break;
        }
        
        $style=[
            'width'=> $lineWidth,
            'color'=>$forecolor,
            'dash'=>$dash,
            'cap'=>'butt',
            'join'=>'miter',
        ];
        $this->Line($x1,$y1,$x2,$y2,$style);        
    }

    public function draw_image(string $uuid,array $prop)
    {
        $x=$this->GetX();
        $y=$this->GetY();
        $height = $prop['height'];
        $width = $prop['width'];
        $imageExpression = str_replace('"','',$prop['imageExpression']);
        $this->Image($imageExpression,$x,$y,$width,$height);
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
    protected function useFont(string $fontName, string $fontstyle, int $fontsize=8)
    {
        $fontName=$this->defaultfont;
        $this->SetFont($fontName, $fontstyle, $fontsize);
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
        $mode = $prop['mode']  ?? 'Transparent';
        $x=$this->GetX();
        $y=$this->GetY();

        // RotateObject
        // $this->Rotate(90,$x,$y);
        // $this->console("begining draw_staticText $x, $y");
        
        $forecolor = $this->convertColorStrToRGB($prop['forecolor']??'');
        $this->SetTextColor($forecolor["r"],$forecolor["g"],$forecolor["b"]);                
        $backcolor = $this->convertColorStrToRGB($prop['backcolor']??'');
        $fill=false;
        $prop['mode'] = $prop['mode']?? 'Transparent';
        
        if($prop['mode'] == 'Opaque')
        {
            $fill = $backcolor;
        }
        $this->SetFillColor($backcolor['r'], $backcolor['g'],$backcolor['b']);
        $halign = !empty($prop['textAlignment']) ? $prop['textAlignment'] : 'L';
        $halign = $this->left($halign,1);        
        $valign = !empty($prop['verticalAlignment']) ? $prop['verticalAlignment'] : 'Top'; 
        $valign = $this->left($valign,1);
        //B,C,T
        $markup = !empty($prop['markup']) ? $prop['markup'] : '';
        $prop['fontName'] = $prop['fontName']?? $this->defaultfont;
        $fontName=strtolower($prop['fontName']);        
        $fontstyle='';
        $fontstyle.= !empty($prop['isBold']) ? 'B':'';
        $fontstyle.= !empty($prop['isItalic']) ? 'I':'';
        $fontstyle.= !empty($prop['isUnderline']) ? 'U':'';
        $fontstyle.= !empty($prop['isStrikeThrough']) ? 'D':'';
        $fontsize= !empty($prop['size']) ? $prop['size'] : 8;     
           
        $rotation = $prop['rotation']?? '';
        
        $this->StartTransform();
        switch($rotation)
        {
            case 'Left':                
                $this->SetXY($x,$y+$h);
                $this->Rotate(90);
                $tmpw=$w;
                $w=$h;
                $h=$tmpw;
                
                
                
                break;
            case 'Right':
                $this->SetXY($x+$w,$y);
                $this->Rotate(270);
                $tmpw=$w;
                $w=$h;
                $h=$tmpw;
                // $this->SetY($y+$w/2);
                break;
            case 'UpsideDown':
                $this->SetXY($x+$w,$y+$h);
                $this->Rotate(180);
                break;
            default:
            break;
        }
        $topPenlineWidth = !empty($prop['topPenlineWidth']) ? $prop['topPenlineWidth'] : 0; 
        $bottomPenlineWidth = !empty($prop['bottomPenlineWidth']) ? $prop['bottomPenlineWidth'] : 0; 
        $leftPenlineWidth = !empty($prop['leftPenlineWidth']) ? $prop['leftPenlineWidth'] : 0; 
        $rightPenlineWidth = !empty($prop['rightPenlineWidth']) ? $prop['rightPenlineWidth'] : 0; 
        $textAdjust = !empty($prop['textAdjust']) ? $prop['textAdjust'] : ''; 
        $border=[];
        if($topPenlineWidth>0)
        {
            $penlineStyle = $prop['topPenlineStyle']??'';
            $penlineColor = $prop['topPenlineColor']??'';
            $border['T'] = $this->getLineStyle($penlineStyle,$topPenlineWidth,$penlineColor); 
        }
        if($bottomPenlineWidth>0)
        {
            $penlineStyle = $prop['bottomPenlineStyle']??'';
            $penlineColor = $prop['bottomPenlineColor']??'';
            $border['B']= $this->getLineStyle($penlineStyle,$bottomPenlineWidth,$penlineColor); 
        }
        if($rightPenlineWidth>0)
        {
            $penlineStyle = $prop['rightPenlineStyle']??'';
            $penlineColor = $prop['rightPenlineColor']??'';
            $border['R']= $this->getLineStyle($penlineStyle,$leftPenlineWidth,$penlineColor); 
        }
        if($leftPenlineWidth>0)
        {
            $penlineStyle = $prop['leftPenlineStyle']??'';
            $penlineColor = $prop['leftPenlineColor']??'';
            $border['L']= $this->getLineStyle($penlineStyle,$rightPenlineWidth,$penlineColor); 
        }
        
        
        $this->useFont($fontName, $fontstyle, $fontsize);
        
        if($isTextField)
        {
            $text = $prop['textFieldExpression'];
        }
        else
        {
            $text = $prop['text'];
        }
        $x=$this->GetX();
        $y=$this->GetY();
        if($prop['uuid']=='724ac379-e567-49bf-8186-fff31392bf83')
        {
            $this->console("draw_staticText $x, $y");
            print_r($prop);
        }
        $topPadding=$prop['topPadding']??0;
        $leftPadding=$prop['leftPadding']??0;
        $rightPadding=$prop['rightPadding']??0;
        $bottomPadding=$prop['bottomPadding']??0;
        $prop['markup']=$prop['markup']??'';
        $link = $prop['hyperlinkReferenceExpression']??'';        
        $pattern = $prop['pattern']??'';
        if(!empty($pattern))
        {
            $text = $this->formatValue($text,$pattern);
        }
        $this->console("hyperlink $link");
        $this->setCellPaddings( $leftPadding, $topPadding, $rightPadding, $bottomPadding);
        $ishtml=0;
        if($prop['markup']=='html')
        {
            $ishtml=1;
            $finaltxt=$text;
        }
        else
        {
            if(!empty($link))
            {
                $finaltxt = $this->convertToLink($text,$link);
                $ishtml=1;
            }
            else
            {
                $finaltxt=$text;
            }
        }
        

        $stretchtype=0;
        $maxheight=$h;
        if($textAdjust=='StretchHeight')
        {
            $stretchtype=0;
            $maxheight=0;
        }
        else if($textAdjust=='ScaleFont')
        {           
            $stretchtype=2;
        }
        else
        {
            $stretchtype=0;
            $finaltxt = $this->convertToLink( $this->reduceString($text,$w),$link);
        }
        $this->MultiCell($w,$h,$finaltxt,$border,$halign,$fill,0,$x,$y,true,$stretchtype,$ishtml,true,$maxheight,$valign);
        $this->StopTransform();
    }

    public function draw_textField(string $uuid,array $prop)
    {
        $this->draw_staticText($uuid,$prop,true);        
    }

    public function reduceString(mixed $txt, int $width): string
    {
        $txt = (string)$txt;
        while($this->GetStringWidth($txt) > $width) 
        {          
            $txt=substr_replace($txt,"",-1);                            
        }
        return $txt;
    }
    public function draw_unsupportedElement(string $uuid,array $prop)
    {

        $type = $prop['type'];
        $x=$this->GetX();
        $y=$this->GetY();
        $w=$prop['width'];
        $h=$prop['height'];  
        $this->SetFontSize(8);
        $color1=100;
        $color2=100;
        $this->SetDrawColor($color1,$color2 , 0, 0);
        $this->SetTextColor($color1, $color2, 0, 0);           
        $style=[
            'width'=> 1,
            'dash'=>'',
            'cap'=>'butt',
            'join'=>'miter',
        ];
        $this->SetLineStyle($style);
        $this->Rect($x,$y,$w ,$h);
        $subtypetxt = '';
        if(isset($prop['subtype']))
        {
            $subtypetxt = '('. $prop['subtype'].')';
        }
        $this->Cell($w,10,"element $type $subtypetxt is not support",0);          
            // $offsetx = isset($offsets['x']) ? $offsets['x']: 0;
            // $offsetx = (int)$offsetx;
            // $offsety = isset($offsets['y']) ? $offsets['y']: 0 ;
            // $offsety = (int)$offsety;
            // $this->maxY=$offsety+$height;
            // $this->currentY=$offsety;
            // $this->SetXY($offsetx,$offsety);
            // $offsets = ['x'=>$offsetx,'y'=>$offsety];
            // if($this->debugband)
            // {
                
            //     if(str_contains($bandname,$this->groupbandprefix))
            //     {
            //         $color1=100;
            //         $color2=100;
            //     }
            //     else
            //     {
            //         $color1=50;
            //         $color2=0;
            //     }

            //     if(in_array($bandname,['columnHeader','columnFooter']) || str_contains($bandname,$this->groupbandprefix) || str_contains($bandname,'detail_'))
            //     {
            //         $width = $this->columnWidth;
            //     }
            //     $this->printbandcount++;  
            //     $this->SetFontSize(8);
            //     $this->SetDrawColor($color1,$color2 , 0, 0);
            //     $this->SetTextColor($color1, $color2, 0, 0);           
            //     // $linestyle = ['dash'=>'','width'=>1];
            //     // $this->SetLineStyle(); 
            //     $this->Rect($offsetx,$offsety,$width ,$height);    
            //     $this->lastBandEndY=$offsety+$height;; 
            //     $this->Cell($width,10,$bandname."--$this->printbandcount",0);    
            // }
            
        
    }
    /****************************** draw all bands ********************************/
    /****************************** draw all bands ********************************/
    /****************************** draw all bands ********************************/
    /****************************** draw all bands ********************************/
    /****************************** draw all bands ********************************/
    public function getColumnBeginingX()
    {
        // echo "\n getColumnBeginingX $this->columnno\n";
        $x = $this->columnno * $this->columnWidth + $this->pagesettings['leftMargin'];
        return $x;
    }
    public function prepareColumn(int $columnCount,mixed $columnWidth)
    {
        $this->columnWidth = $columnWidth;
        $this->columnCount = $columnCount;
        $beginY=$this->bands['pageHeader']['endY'];
        $this->columnno=0;
        $endY=$this->draw_columnFooter()['y']+$this->bands['columnFooter']['height'];
        $this->SetDrawColor(40,10,10,0);
        $this->SetTextColor(40,10,10,0);

        if($this->debugband)
        {        
            for($i=0;$i<$columnCount;$i++)
            {
                $colname='column '.$i;
                $x=$this->pagesettings['leftMargin'] + $i*$columnWidth;
                $this->SetAlpha(0.5);
                $this->Rect($x,$beginY,$columnWidth ,($endY - $beginY),'FD','',[5,5,5,0.1] );     
                $this->SetAlpha(1);
                $this->SetXY($x,$beginY);
                $this->Cell($columnWidth,10,$colname,0,'','C');    
            }
        }
    }
    /**
     * prepare band in pdf, and return x,y offsets
     * @param 
     */
    public function prepareBand(string $bandname, mixed $callback=null):array
    {      
        
        $offsets=[];
        // echo "\nprepareband $bandname, $this->groupbandprefix\n";
        if(str_contains($bandname,'detail'))
        {
            $methodname = 'draw_detail';
            $band = $this->bands[$bandname];
            $offsets = call_user_func([$this,$methodname],$bandname,$callback);
        }
        else if(str_contains($bandname,$this->groupbandprefix))
        {
            $methodname = 'draw_group';
            $band = $this->bands[$bandname];
            $groupname = str_replace([$this->groupbandprefix,'_header','_footer'],'',$bandname);
            $groupno = $this->groups[$groupname]['groupno'];
            if(str_contains($bandname,'_header'))
            {
                $offsets = $this->draw_groupHeader($bandname,$callback);
            }
            else
            {
                $offsets = $this->draw_groupHeader($bandname,$callback);
            }
            // $offsets = call_user_func([$this,$methodname],$bandname,$callback);
        }        
        // else if(in_array($bandname,['summary']))
        // {
            
        //     $methodname = 'draw_'.$bandname;
        //     $band = $this->bands[$bandname];
        //     $offsets = call_user_func([$this,$methodname],$callback);
        // }
        else
        {
            $methodname = 'draw_'.$bandname;
            $band = $this->bands[$bandname];
            $offsets = call_user_func([$this,$methodname],$callback);
            
        }
        $offsetx=0;
        // echo "\n$methodname\n";
        // print_r($offsets);
        

        $width = $this->getPageWidth() - $this->pagesettings['leftMargin'] - $this->pagesettings['rightMargin'];
        $height = isset($band['height'])? $band['height'] : 0;
        $offsety=$this->pagesettings['topMargin'];
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
                
                if(str_contains($bandname,$this->groupbandprefix))
                {
                    $color1=100;
                    $color2=100;
                }
                else
                {
                    $color1=50;
                    $color2=0;
                }

                if(in_array($bandname,['columnHeader','columnFooter']) || str_contains($bandname,$this->groupbandprefix) || str_contains($bandname,'detail_'))
                {
                    $width = $this->columnWidth;
                }
                $this->printbandcount++;  
                $this->SetFontSize(8);
                // $this->SetDrawColor($color1,$color2 , 0, 0);
                
                $this->SetTextColor($color1, $color2, 0, 0);           
                // $linestyle = ['dash'=>'','width'=>1];
                
                $style= $this->getLineStyle('Solid',1,'#cccccc');
                // $this->SetLineStyle($style); 
                $this->Rect($offsetx,$offsety,$width ,$height,'',['TBLR'=>$style]);    
                $this->lastBandEndY=$offsety+$height;; 
                $this->Cell($width,10,$bandname."--$this->printbandcount",0);    
            }
            
        }
        $this->lastBandEndY=$offsety+$height;;
        $this->bands[$bandname]['endY']=$this->lastBandEndY;
        $pageno=$this->PageNo();
        // echo "\n Print band($pageno) --$this->printbandcount $bandname, column: $this->columnno, $offsetx:$offsety, height:$height = endY = $this->lastBandEndY \n";
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
        // $offsety = $this->lastBandEndY;
        if($this->PageNo() == 1)
        {
            $offsety = $this->pagesettings['topMargin'] + $this->getBandHeight('title') +  $this->getBandHeight('pageHeader');
        }
        else
        {
            $offsety = $this->pagesettings['topMargin'] + $this->getBandHeight('pageHeader');
        }
        $offset = ['x'=>$this->getColumnBeginingX(),'y'=>$offsety];
        //$this->drawBand($bandname,$offset);
        return $offset;
    }

    protected function getLastGroupName():string
    {
        
        if($this->groupCount()>0)
        {
            return array_key_last($this->groups);
        }
        else
        {
            return '';
        }
    }
    protected function getFirstGroupName():string
    {
        
        if($this->groupCount()>0)
        {
            return array_key_first($this->groups);
        }
        else
        {
            return '';
        }
    }
    public function setDetailNextPage(string $detailname)
    {

    }
    public function draw_detail(string $detailbandname,mixed $callback=null)
    {
        $detailno = (int)str_replace('detail_','',$detailbandname);
        $totaldetailheight = 0;
        
        $offsety = $this->lastBandEndY;       
        $estimateY=$offsety+$this->getBandHeight($detailbandname);
        if($this->isEndDetailSpace($estimateY) && gettype($callback)=='object')
        {            
            $callback();
            $offsety = $this->bands['columnHeader']['endY'];    
        }
        $offsetx = $this->getColumnBeginingX();
        // echo "\nprintdetail at column: $$offsetx\n";
        
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
        $offset = ['x'=>$this->getColumnBeginingX() ,'y'=>$offsety];
        
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
        if($this->groupCount()>0)
        {            
            $endgroupname = $this->getHashKeyFromIndex($this->groups,0);
            $lastband = $this->groupbandprefix. $endgroupname.'_footer';
            
            $offsety = $this->bands[$lastband]['endY'];
        }
        else
        {
            $offsety = $this->bands[$this->lastdetailband]['endY'];
        }
        
        $estimateY=$offsety+$this->getBandHeight('summary');
        if(($this->columnno >0 || $this->isEndDetailSpace($estimateY) ) && gettype($callback)=='object')
        {            
            // echo "summary add page";
            $offsety = $callback();//$this->bands['columnHeader']['endY'];    
        }
        // else
        // {
        //     $offsety = $this->lastBandEndY;
        // }
        
        // $estimateY=$offsety+$this->getBandHeight('summary');
        // if($this->isEndDetailSpace($estimateY) && gettype($callback)=='object')
        // {            
        //     $callback();
        //     $offsety = $this->bands['columnHeader']['endY'];    
        // }
        $offset = ['x'=>$this->pagesettings['leftMargin'],'y'=>$offsety];        
        return $offset;
    }
    public function draw_noData()
    {
        
        $offsety = $this->pagesettings['topMargin'];
        $offset = ['x'=>$this->pagesettings['leftMargin'],'y'=>$offsety];
        return $offset;
    }

    public function draw_groupHeader(string $bandname,mixed $callback=null) : array
    {
        $offsetx=$this->getColumnBeginingX();//$this->pagesettings['leftMargin'];
        // $offsety=$this->pagesettings['leftMargin'];
        $offsety = $this->lastBandEndY;
        $estimateY=$offsety+$this->getBandHeight($bandname);
        if($this->isEndDetailSpace($estimateY) && gettype($callback)=='object')
        {            
            $callback();
            $offsety = $this->bands['columnHeader']['endY'];    
        }
        /*
            if parent == previousheader
            else parent == this group footer

         */
        $offset=['x'=>$offsetx,'y'=>$offsety];
        return $offset;
    }

    public function draw_groupFooter(string $bandname,mixed $callback=null) : array
    {
        
        $offsetx=$this->getColumnBeginingX();//$this->pagesettings['leftMargin'];
        // $offsety=$this->pagesettings['leftMargin'];
        $offsety = $this->lastBandEndY;
        $estimateY=$offsety+$this->getBandHeight($bandname);
        if($this->isEndDetailSpace($estimateY) && gettype($callback)=='object')
        {            
            $callback();
            $offsety = $this->bands['columnHeader']['endY'];    
        }
        // echo "\n draw_groupFooter: $offsetx\n";
        $offset=['x'=>$offsetx,'y'=>$offsety];
        return $offset;
    }


    /*************** misc function ****************/
    
    public function setPosition(int $x,int $y)
    {
        $this->SetXY($x,$y);
    }

    protected function getLineStyle(string $lineStyle,float $lineWidth=8,string $lineColor='')
    {
        $forecolor = $this->convertColorStrToRGB($lineColor);        
        switch($lineStyle)
        {
            case "Dotted":
                $dash=sprintf("%d,%d",$lineWidth,$lineWidth);
            break;
            case "Dashed":
                $dash=sprintf("%d,%d",$lineWidth*4,$lineWidth*2);
                break;
            default:
                $dash="";
            break;
        }
        
        $style=[
            'width'=> $lineWidth,
            'color'=>$forecolor,
            'dash'=>$dash,
            'cap'=>'butt',
            'join'=>'miter',
        ];
        return $style;
    }
    // public function __call($methodname,$args)
    // {
    //     if(!method_exists($this,$methodname))
    //     {
    //         echo "\n$methodname() does not exists\n";
    //     }
    // }

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
        return array("r"=>hexdec(substr($colorstr,1,2)),"g"=>hexdec(substr($colorstr,3,2)),"b"=>hexdec(substr($colorstr,5,2)));
    }
    public function groupCount(): int
    {
        return $groupcount = count($this->groups);
    }

    protected function formatValue(mixed $value, string $pattern) : string
    {
        // scientific
        $data = $value;
        if(str_contains($pattern,'E0'))
        {
            
        }
        //date
        else if(str_contains($pattern,'d') || str_contains($pattern,'h')|| str_contains($pattern,'H') || str_contains($pattern,'M')) //date
        {

        }
        //number
        else if(str_contains($pattern,'#') ) 
        {
            $fmt = numfmt_create( 'en_US', \NumberFormatter::DECIMAL );
            numfmt_set_pattern($fmt,$pattern);
            $data = numfmt_format($fmt,$value);

        }
        return $data;
    }
    protected function convertToLink(string $text='',string $link='')
    {
        if(!empty($link))
        {
            return '<a href="'.$link.'">'.$text.'</a>';
        }
        else
        {
            return $text;
        }
    }
    
}