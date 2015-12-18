[Tasklist](https://github.com/rhildred/tasklist)
=====

This is the first union of php/slim framework back end and angular front end. We can't have  back end that allows a user to edit, without security. In order to have a back end that allows us to have security, we need a new dependency:

```

{
    "require": {
        "slim/slim": "^2.6",
	"rhildred/oauth2": "dev-master"
    }
}

```

rhildred/oauth2 depends on google's oauth2 service to create mutual trust between a user that may not already trust our site, but trusts google and our site, which certainly can't trust any user. This trust is established with a secret key that is contained in creds/google.json.

```

{"ClientID":"<Your client ID here>", "ClientSecret":"<Your client secret here>",
"Users":["<email of trusted user>"]}

```

You can get a [clientid and client secret here.](https://console.developers.google.com) You will need a google login to use this. I login by going to the /login endpoint. Once logged in I can access the other endpoints in the index.php file which are protected by a `new \Auth()`.

```

<?php

require "../vendor/autoload.php";

$oApp = new \Slim\Slim(array('templates.path' => __DIR__ . "/../views"));
// open databaase
$oDb = new PDO("sqlite:" . __DIR__ . "/../tasks.sqlite");

// the form
$oApp->get("/", function() use($oApp){
    $oApp->render("tasks.phtml");
});

// read ... complete example at https://github.com/rhildred/tasklist
$oApp->get("/tasks", new \Auth(), function() use($oApp, $oDb){
    $oStmt = $oDb->prepare("SELECT * FROM tasks");
    $oStmt->execute();
    $aTasks = $oStmt->fetchAll(PDO::FETCH_OBJ);
    echo json_encode($aTasks);
});

// create ... complete example at https://github.com/rhildred/tasklist
$oApp->post("/tasks", new \Auth(), function() use($oApp, $oDb){
    $oData = json_decode($oApp->request->getBody());
    $oStmt = $oDb->prepare("INSERT INTO tasks(description) VALUES(:task)");
    $oStmt->bindParam("task", $oData->currentTask);
    $oStmt->execute();
    echo json_encode(array("rows"=>$oStmt->rowCount()));
});

// update ... complete example at https://github.com/rhildred/tasklist
$oApp->post("/tasks/:id", new \Auth(), function($id) use($oApp, $oDb){
    $oData = json_decode($oApp->request->getBody());
    $oStmt = $oDb->prepare("UPDATE tasks SET finished = 1 WHERE id = :id");
    $oStmt->bindParam("id", $id);
    $oStmt->execute();
    echo json_encode(array("rows"=>$oStmt->rowCount()));
});

// delete ... complete example at https://github.com/rhildred/tasklist
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

```
