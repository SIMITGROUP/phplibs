<?php
namespace Simitsdk\phpjasperxml;
trait PHPJasperXML_load
{
    protected array $pageproperties=[];
    protected array $variables=[];
    protected array $parameters=[];
    protected array $fields=[];
    protected array $groups=[];
    protected int $groupcount = 0;
    protected array $bands=[];
    protected array $elements = [];
    protected string $querystring = '';
    protected array $subdatasets=[];
    protected string $groupbandprefix = 'report_group_';
    /**
     * read jrxml file and load into memeory
     * @param string $filename
     * @return self
     */
    public function load_xml_file(string $file ): self
    {        
        $xml =  file_get_contents($file);                
        $this->load_xml_string($xml);      
        // print_r($this->bandelements);
        return $this;
    }

    /**
     * distribute jrxml contents into different attributes categories
     * @param string $jrxml content
     * @return self
     */
    public function load_xml_string(string $jrxml): self
    {
        $obj = simplexml_load_string($jrxml);
        $this->pageproperties = $this->prop($obj);

        foreach ($obj as $k=>$out)
        {            
            $setting = $this->prop($out);
            $name= isset($setting['name']) ? $setting['name'] : '';
            
            switch($k)
            {
                case 'property':
                    $this->pageproperties[$name]=$setting;
                    break;
                case 'field':
                case 'parameter':
                case 'variable':
                    $attributename = $k.'s';                    
                    
                    foreach($out as $key=>$value)
                    {
                        $setting[$key]=(string)$value;
                    }
                    $this->$attributename[$name]=$setting;                    
                    break;                    
                case 'queryString':
                    $this->setQueryString($out);
                    break;
                case "subDataset":
                    $this->addSubDataSets($name,$out);
                    break;
                case 'group':                                        
                    $this->addGroup($out);             
                    break;
                //all bands
                case 'background':
                case 'title':
                case 'pageHeader':
                case 'columnHeader':
                case 'detail':
                case 'columnFooter':
                case 'pageFooter':
                case 'lastPageFooter':
                case 'summary':
                case 'noData':
                    $this->addBand($k,$out);
                    break;
                default:
                    echo "$k is not supported, rendering stop\n";
                    die;
                    break;
            }
        }                   
        return $this;
    }

    /**
     * define / override jrxml querystring into memory
     * @param string $sql
     * @return $this
     */
    public function setQueryString($sql):self
    {
        $this->querystring=$sql;
        return $this;
    }

    /**
     * register different band into band array
     * @param string $bandname
     * @param array $elements
     */
    protected function addBand(string $bandname,object $elements,bool $isgroup = false)
    {               
        
        $offsety=0;               
        $count=0;
        foreach($elements->band as $bandobj)
        {            
            if($bandname == 'detail')
            {
                $newbandname = $bandname.'_'.$count;
            }
            else
            {
                $newbandname = $bandname;
            }
            
            $this->bands[$newbandname]  = $this->prop($bandobj);
            $this->elements[$newbandname] = $this->getBandChildren($bandobj);
            $count++;
        }            

    }

    /**
     * register groups and groupband
     * 
     */
    protected function addGroup($obj)
    {
        $name = (string)$obj->attributes()['name'];
        $groupExpression = (string)$obj->groupExpression;
        $bandname = 'report_group_'.$name;
        $groupExpression = $obj->groupExpression;
        
        $this->groups[$name]=[ 'value'=>'NOVALUE','count'=>0,'groupExpression'=>$groupExpression, 'groupno'=>$this->groupcount];
        $this->addBand($bandname.'_header',$obj->groupHeader,true);
        $this->addBand($bandname.'_footer',$obj->groupFooter,true);
        $this->groupcount++;
    }

    protected function addSubDataSets(string $name, string $sql)
    {        
        $this->subdatasets[$name]=$sql;
    }
  
    


    
    protected function toValue(mixed $data): mixed
    {
        return json_decode(json_encode($data),true);
    }

    

    protected function getBandChildren($els)
    {
        $data=[];
        // foreach($obj->band as $k => $els )
        // {
            foreach($els as $elementtype => $objvalue)
            {
                
                if(isset($objvalue->reportElement))
                {
                    
                    $setting = $this->prop($objvalue->reportElement);
                    $uuid = $setting['uuid'];
                    $objvalue->type = $elementtype;
                    $methodname = 'element_'.$elementtype; //prepare elements setting
                    if(method_exists($this,$methodname))
                    {
                        $prop = $this->prop($objvalue);
                        $prop['type']=$elementtype;
                        foreach($objvalue as $k=>$values)
                        {
                            $subprops = $this->prop($values);
                            foreach($subprops as $key=>$val)
                            {
                                $prop[$key]=$val;
                            }
                        }
                        $data[$uuid] = call_user_func([$this,$methodname],$prop,$objvalue);      
                    }
                    else
                    {
                        echo "\nElement $elementtype is not supported due to $methodname() is not exists\n";
                    }
                    
                    
                }
                else
                {
                    //elementGroup, tmp not supported
                }
            }            
        // }
        return $data;
    }








    /************** misc functions *******************/
    /************** misc functions *******************/
    /************** misc functions *******************/

     /** 
     * get property of simplexml ofbject
     * @param SimpleXMLElement $obj
     * @return array $attributes
     */
    protected function prop(\SimpleXMLElement $obj):array
    {
        $attributes=[];
        if(!is_null($obj->attributes()))
        {            
            foreach($obj->attributes() as $k=>$v)
            {
                $attributes[$k]=json_decode(json_encode($v),true)[0];
            }        
        }
        return $attributes;
    }

    protected function appendprop(array $prop, object $obj, string $prefix=''):array
    {
        $subprop = $this->prop($obj);
        foreach($subprop as $k=>$v)
        {
            $key = $prefix.$k;

            $prop[$key]=$v;
        }
        return $prop;
    }

}