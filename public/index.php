<?php

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

function isURLValid($url)
{
    $context = stream_context_create([
        "http" => [
            "method"        => 'HEAD',
            "ignore_errors" => true,
        ],
    ]);

    $fd = fopen($url, 'rb', false, $context);   
    $meta = stream_get_meta_data($fd);
    fclose($fd);
    
    if(!isset($meta['wrapper_data']))
        return false;
    
    if(!isset($meta['wrapper_data'][0]))
        return false;
    
    $header = explode(' ', $meta['wrapper_data'][0]);

    if(!isset($header[1]))
        return false;

    if(!is_numeric($header[1]))
        return false;
    
    return intval($header[1]) < 400;    
}


try
{
    $classPath = getClassPath($_SERVER['REQUEST_URI']);

    $className = array_pop($classPath);
    $middlePath = [];

    while (count($classPath) > 0) {

        foreach(getURLs($classPath) as $repository)
        {
            $repositoryFile = implode('/', array_merge(array($repository), array_reverse($middlePath), array($className.'.php')));

            header('X-URL: ' . $repositoryFile);
            if(isURLValid($repositoryFile))
            {
                header('Location: ' . $repositoryFile);
                exit();            
            }
        }

        $middlePath[] = array_pop($classPath);
    }

    http_response_code(404);
    exit();
}
 catch (\Exception $e)
 {
    http_response_code(500);
    header('X-Error: ' . $e->getMessage());
    exit();     
 }