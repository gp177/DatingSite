<?php
// fake $app, $log so that Netbeans can provide suggestions while typing code
if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}
$app->get('/profile', function() use ($app, $log) {
    
    if (!$_SESSION['user']) {
        $app->render('access_denied.html.twig');
        return;
    }else{

  $profileList = DB::queryFirstRow('SELECT * FROM users WHERE id=%i', $_SESSION['user']['id']);
 
       if (!$profileList) {
           
//        print_r($profileList); 
     $app->render('not_found.html.twig');
        return;
    }else{
    
    $app->render('profile.html.twig', array('pl' => $profileList));
    
    }
    
    }
});
