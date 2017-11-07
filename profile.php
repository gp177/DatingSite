<?php
// fake $app, $log so that Netbeans can provide suggestions while typing code
//if (false) {
//    $app = new \Slim\Slim();
//    $log = new Logger('main');
//}
$app->get('/profile', function() use ($app) {
    

    if (!$_SESSION['user']) {
        $app->render('access_denied.html.twig');
        return;
    }
   $profileList = array();
   $profileList = DB::query('SELECT * FROM users WHERE Id=%i', $_SESSION['user']['id']);
    if (!$profileList) {
        
       
       $app->render('not_found.html.twig');
        return;
    }
     
     $app->render('profile.html.twig', array('pl' => $profileList));
});
