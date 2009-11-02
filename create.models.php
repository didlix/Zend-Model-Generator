<?php
 
/**
 * Script for creating database models
 */
 
// Initialize the application path and autoloading
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../'));
set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/../library',
    get_include_path(),
)));
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();
 
// Define some CLI options
$getopt = new Zend_Console_Getopt(array(
    'table|t'       => 'Specify a table',
    'env|e-s'       => 'Application environment for which to create database (defaults to development)',
    'help|h'        => 'Help -- usage message',
));
try {
    $getopt->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    // Bad options passed: report usage
    echo $e->getUsageMessage();
    return false;
}
 
// If help requested, report usage message
if ($getopt->getOption('h')) {
    echo $getopt->getUsageMessage();
    return true;
}

$env      = $getopt->getOption('e');
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (null === $env) ? 'development' : $env);

// Initialize Zend_Application
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

// Initialize and retrieve DB resource
$bootstrap = $application->getBootstrap();
$bootstrap->bootstrap('db');
$dbAdapter = $bootstrap->getResource('db');


if(!$getopt->getOption('t')) {
    $results = $dbAdapter->fetchAll('show tables');
}

$dbConfig = $dbAdapter->getConfig();

foreach($results AS $i => $result) {
     $tables[] = $result['Tables_in_' . $dbConfig['dbname']];

}


foreach($tables AS $table) {


    // Passing configuration to the constructor:
    $file = new Zend_CodeGenerator_Php_File(array(
        'classes' => array(
            new Zend_CodeGenerator_Php_Class(array(
                'name'    => 'Default_Model_DbTable_' . $table,
                'properties' => array(
                    array(
                        'name'          => '_name',
                        'visibility'    => 'protected',
                        'defaultValue'  => $table
                        )
                ),

            )),
        )
    ));

    // or write it to a file:
    file_put_contents(APPLICATION_PATH . '/models/DbTable/' . $table . '.php', $file->generate());

}