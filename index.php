<?php

require "vendor\autoload.php";

$oApp = new \Slim\Slim(array('templates.path' => __DIR__));
// open databaase
$oDb = new PDO("sqlite:" . __DIR__ . "/tasks.sqlite");

$oApp->get("/", function() use($oApp){
    $oApp->render("tasks.phtml");
});

$oApp->post("/tasks", function() use($oApp, $oDb){
    $oData = json_decode($oApp->request->getBody());
    $oStmt = $oDb->prepare("INSERT INTO tasks(description) VALUES(:task)");
    $oStmt->bindParam("task", $oData->currentTask);
    $oStmt->execute();
    echo json_encode(array("rows"=>$oStmt->rowCount()));
});

$oApp->run();