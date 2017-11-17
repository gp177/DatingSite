<?php

if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}

$app->get('/search', function() use ($app, $log) {

    if (!$_SESSION['user']) {
        $app->render('access_denied.html.twig');
        return;
    } else {
        
        
        $profileList = DB::queryFirstRow('SELECT *, YEAR(CURRENT_TIMESTAMP) - YEAR(birthDate) - (RIGHT(CURRENT_TIMESTAMP, 5) < RIGHT(birthDate, 5)) as age FROM users');
         $userCount = DB::query('SELECT id FROM users');
        
        
        if (!$profileList) {

//     print_r($profileList); 
            $app->render('not_found.html.twig');
            return;
        } else {
  
            $app->render('search_results.html.twig',array('list' => $profileList, 'userCount' => count($userCount)));
           
        }
    }
});

