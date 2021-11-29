<?php

namespace Simitsdk\phpjasperxml\datadrivers;

interface DataInterface
{
    
    public function fetchData(mixed $querypara):array;

}