<?php
require_once(dirname(__FILE__).'/../config.php');
require_once(dirname(__FILE__).'/../model.php');
require_once(dirname(__FILE__).'/../tools.php');
require_once(dirname(__FILE__).'/../controller.php');

$controller = new Controller();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $max_id = (isset($_POST['max_id']) && $_POST['max_id']) > 0?$_POST['max_id']:false;

    if(isset($_POST['tags']) && isset($_POST['q'])){
        $controller->processMentions($_POST['q']);
    }
    else if(isset($_POST['q'])){
        $controller->processQuery($_POST['q'], $max_id);
    }
    else if(isset($_POST['user'])){
        $controller->processUser($_POST['query'], $_POST['user'], $max_id);
    }
    else if(isset($_POST['config'])){
        $controller->processConfig($_POST['params']);
    }
    else if(isset($_POST['test'])){
        $controller->testAPITwitter($_POST['params']);
    }
}
else{
    if(isset($_GET['edit'])){
        $view = 'config';
    }
    else{
        $view = $controller->index();
    }

    require_once(dirname(__FILE__).'/../layout.php');
}
