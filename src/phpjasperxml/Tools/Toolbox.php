<?php

namespace Simitsdk\phpjasperxml\Tools;

use SimpleXMLElement;

trait Toolbox
{

   

    protected function left(string $str, int $length) : string
    {
        return substr($str, 0, $length);
    }

    protected function right(string $str, int $length) : string
    {
        return substr($str, -$length);
    }


}