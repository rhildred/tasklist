<?php

require "vendor\autoload.php";

$oApp = new \Slim\Slim(array('templates.path' => __DIR__));
// open databaase
$oDb = new PDO("sqlite:" . __DIR__ . "/amounts.sqlite");

$oApp->get("/", function() use($oApp){
    $oApp->render("addingMachine.phtml");
});

$oApp->post("/", function() use($oApp, $oDb){
    $oData = json_decode($oApp->request->getBody());
    print_r($oData);
});

$oApp->run();