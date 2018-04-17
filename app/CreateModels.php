<?php
$modelsPath = "Models/";
$controllersPath = "Http/Controllers/";

$sqlFile = file_get_contents($modelsPath . 'afrobeat_new.sql');
$templateModelFile = file_get_contents($modelsPath . 'Template.php');
$templateModel = "TemplateModel";
$templateTable = "template_table";
$tablePrefix = "afrobt_";
$tableNameQuotedChar = "`";
$tableNameSeparatorChar = "_";

$createTableQueries = explode(";" , trim($sqlFile));


$templateControllerFile = file_get_contents($controllersPath . 'TemplateController.php');
$templateController = "TemplateController";


foreach($createTableQueries as $query){
    if(empty($query)){
        continue;
    }
    
    $queryStringArray = explode(" " , $query);
    $tableName =  str_replace($tableNameQuotedChar , "", $queryStringArray[2]);
    
    echo "Creating Model for table " . $tableName . "<br>";
    
    $modelName = str_replace($tableNameQuotedChar , "", $tableName );
    $modelName = ucfirst(substr($modelName , strlen($tablePrefix)));
    $modelNameArray = explode($tableNameSeparatorChar , $modelName);
    $modelName = "";
    foreach($modelNameArray as $mna){
        $modelName .= ucfirst($mna);

    }
    $controllerName = $modelName . "Controller";
    $modelFile = $templateModelFile;
    $controllerFile = $templateControllerFile;
    
    $modelFile = str_replace($templateModel , $modelName, $modelFile );
    $modelFile = str_replace($templateTable , $tableName, $modelFile );
    
    $controllerFile = str_replace($templateModel , $modelName, $controllerFile );
    $controllerFile = str_replace($templateController , $controllerName, $controllerFile );
    
    $modelFileName = $modelName .".php";
    $controllerFileName = $controllerName .".php";
    
    //Create Model
    if(!file_exists($modelsPath . $modelFileName)){
        $fp = fopen($modelsPath . $modelFileName, "w");
        fwrite($fp, $modelFile);
        fclose($fp);
        echo "Created Model " .$modelFileName . "<br><br>";
    }
    else{
        echo "Skipping ".$modelFileName . ".Model already exists<br><br>";
    }
    
    echo "Creating Controller for table " . $tableName . "<br>";
    
    //Create Controller
    if(!file_exists($controllersPath . $controllerFileName)){
        $fp = fopen($controllersPath . $controllerFileName, "w");
        fwrite($fp, $controllerFile);
        fclose($fp);
        echo "Created Controller " .$controllerFileName . " in " . $controllersPath. "<br><br>";
    }
    else{
        echo "Skipping ".$controllerFileName . ".Controller already exists<br><br>";
    }

    
    
}

