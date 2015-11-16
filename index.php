<?php

require "vendor\autoload.php";

$oApp = new \Slim\Slim(array('templates.path' => __DIR__));

$oApp->get("/", function() use($oApp){
    $oApp->render("addingMachine.phtml");
});

$oApp->run();