<?php

if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}

$app->map('/admin/panel/warn/', function() use ($app, $log) {
    
    if ($app->request()->isGet()) {
        $app->render('user_warn.html.twig');
        return;
    }
    // in post - receiving submission
    $email = $app->request()->post('email');
    $user = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
    if ($user) {
        
     
        
        $emailBody = $app->view()->render('passreset_email.html.twig', array('name' => $user['name']));
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html\r\n";
        $headers .= "From: Noreply <noreply@ipd10.com>\r\n";
        $toEmail .= sprintf("%s <%s>\r\n", htmlentities($user['name']), $user['email']);
        
        mail($toEmail, "Your pasword reset for " . $_SERVER['SERVER_NAME'], $emailBody, $headers);
        
        $app->render('passreset_request_success.html.twig');
 
    }
    else { // failed request
        $app->render('passreset_request.html.twig', array('error' => true));
    }
    
})->via('GET', 'POST');

