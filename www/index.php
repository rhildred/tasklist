<?php

require "../vendor/autoload.php";

$oApp = new \Slim\Slim(array('templates.path' => __DIR__ . "/../views"));
// open databaase
$oDb = new PDO("sqlite:" . __DIR__ . "/../tasks.sqlite");

// the form
$oApp->get("/", function() use($oApp){
    $oApp->render("tasks.phtml");
});

// read
$oApp->get("/tasks", new \Auth(), function() use($oApp, $oDb){
    $oStmt = $oDb->prepare("SELECT * FROM tasks");
    $oStmt->execute();
    $aTasks = $oStmt->fetchAll(PDO::FETCH_OBJ);
    echo json_encode($aTasks);
});

// create
$oApp->post("/tasks", new \Auth(), function() use($oApp, $oDb){
    $oData = json_decode($oApp->request->getBody());
    $oStmt = $oDb->prepare("INSERT INTO tasks(description) VALUES(:task)");
    $oStmt->bindParam("task", $oData->currentTask);
    $oStmt->execute();
    echo json_encode(array("rows"=>$oStmt->rowCount()));
});

// update
$oApp->post("/tasks/:id", new \Auth(), function($id) use($oApp, $oDb){
    $oData = json_decode($oApp->request->getBody());
    $oStmt = $oDb->prepare("UPDATE tasks SET finished = 1 WHERE id = :id");
    $oStmt->bindParam("id", $id);
    $oStmt->execute();
    echo json_encode(array("rows"=>$oStmt->rowCount()));
});

// delete
$oApp->delete("/tasks/:id", new \Auth(), function($id) use($oApp, $oDb){
    $oStmt = $oDb->prepare("DELETE FROM tasks WHERE id = :id");
    $oStmt->bindParam("id", $id);
    $oStmt->execute();
    echo json_encode(array("rows"=>$oStmt->rowCount()));

});

// oauth2 code
$oApp->get("/login", function() use( $oApp){
    // see if this is the original redirect or if it's the callback
    $sCode = $oApp->request->params('code');
    // get the uri to redirect to
    $sUrl = "http";
    if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443)
    {
        $sUrl .= "s";
    }
    $sUrl .= "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

    $oAuth = new \Oauth2($sUrl);
    if($sCode == null){
        $oApp->response->redirect($oAuth->redirectUrl());
    }else{
        $oAuth->handleCode($sCode);
        $oApp->response->redirect("/");
    }
});

$oApp->get("/currentUser", new \Auth(), function() use($oApp){
    echo json_encode($_SESSION['CurrentUser']);
});

$oApp->get("/logout", function(){
    session_start();
    unset($_SESSION["CurrentUser"]);
});

$oApp->run();