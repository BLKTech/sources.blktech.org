<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getClassPath($URI)
{
    $classPath = array();

    foreach (explode("/",
            str_replace("\\", "/", $URI)
    ) as $element) {

        $element = trim($element);

        if(empty($element))
            continue;

        if($element=='.')
            continue;

        if($element=='..')
            array_pop ($path);

        $classPath[] = $element;
    }    
    
    return $classPath;
}

function getURLs($classPath)
{
    $json = __DIR__ . '/../data/' . strtolower(implode('.', $classPath)) . '.json';
    
    if(!file_exists($json))
        return array();
        
    $json_data = file_get_contents($json);
    
    if($json_data===FALSE)
        return array();
    
    $json_object = json_decode($json_data, true);
    
    if($json_object===NULL)
        return array();   
    
    if(!is_array($json_object))
        return array();   
    
    return $json_object;   
}

$context  = stream_context_create(array('http' =>array('method'=>'HEAD')));
$classPath = getClassPath($_SERVER['REQUEST_URI']);


$className = array_pop($classPath);
$middlePath = [];

while (count($classPath) > 0) {

    foreach(getURLs($classPath) as $repository)
    {
        $repositoryFile = $repository.'/'.implode('/', array_reverse($middlePath)).'/'.$className.'.php';
        
        $fd = fopen($repositoryFile, 'rb', false, $context);
        var_dump(stream_get_meta_data($fd));
        fclose($fd);
        die($repositoryFile);
    }

    $middlePath[] = array_pop($classPath);
}

print_r($path);