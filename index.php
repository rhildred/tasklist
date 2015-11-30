<?php

require "vendor\autoload.php";

$oApp = new \Slim\Slim(array('templates.path' => __DIR__));
// open databaase
$oDb = new PDO("sqlite:" . __DIR__ . "/tasks.sqlite");

$oApp->get("/", function() use($oApp){
    $oApp->render("tasks.phtml");
});

$oApp->get("/tasks", function() use($oApp, $oDb){
    $oStmt = $oDb->prepare("SELECT * FROM tasks");
    $oStmt->execute();
    $aTasks = $oStmt->fetchAll(PDO::FETCH_OBJ);
    echo json_encode($aTasks);
});

$oApp->post("/tasks", function() use($oApp, $oDb){
    $oData = json_decode($oApp->request->getBody());
    $oStmt = $oDb->prepare("INSERT INTO tasks(description) VALUES(:task)");
    $oStmt->bindParam("task", $oData->currentTask);
    $oStmt->execute();
    echo json_encode(array("rows"=>$oStmt->rowCount()));
});

$oApp->post("/tasks/:id", function($id) use($oApp, $oDb){
    $oData = json_decode($oApp->request->getBody());
    $oStmt = $oDb->prepare("UPDATE tasks SET finished = 1 WHERE id = :id");
    $oStmt->bindParam("id", $id);
    $oStmt->execute();
    echo json_encode(array("rows"=>$oStmt->rowCount()));
});

$oApp->delete("/tasks/:id", function($id) use($oApp, $oDb){
    $oStmt = $oDb->prepare("DELETE FROM tasks WHERE id = :id");
    $oStmt->bindParam("id", $id);
    $oStmt->execute();
    echo json_encode(array("rows"=>$oStmt->rowCount()));

});

$oApp->run();