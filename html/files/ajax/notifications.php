<?php
    header('Content-Type: application/json');
    require_once('init.php');

    $userId = $app->user->uid;
    $result = array('status'=>true);

    if (isset($_GET['events'])) {
        $result['items'] = $app->notifications->getEvents();
    } else {
        $last = isset($_POST['last'])?$_POST['last']:0;
        $result['feed'] = $app->feed->get($last);
        $result['counts'] = $app->notifications->getCounts();
    }

    $json = json_encode($result);
    // Remove null entries
    echo preg_replace('/,\s*"[^"]+":null|"[^"]+":null,?/', '', $json);
?>