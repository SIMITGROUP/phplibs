<?php

function everything_in_tags($string, $tagname)
{
    $pattern = "#{\s*?$tagname\b[^}]*}(.*?){/$tagname\b[^}]*}#s";
    preg_match_all($pattern, $string, $matches);
    return $matches[1];
}


$string = '$P{myTag}Here is the string{/myTag} and {myTag}here is more $V{myTag}';
$tagname = 'myTag';
$var = everything_in_tags($string, $tagname);
print_r($var);
