<?php

$path = array();

foreach (explode("/",
        str_replace("\\", "/", $_SERVER['REQUEST_URI'])
) as $element) {
    
    $element = trim($element);
    
    if(empty($element))
        continue;
    
    if($element=='.')
        continue;
    
    if($element=='..')
        array_pop ($path);
    
    $path[] = $element;
}


print_r($path);