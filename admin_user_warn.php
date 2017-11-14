<?php

if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}

$app->map('/admin/panel/warn/:id', function($id) use ($app, $log) {
    $user = DB::queryFirstRow("SELECT * FROM users WHERE id=%s", $id);
       
    if ($app->request()->isGet()) {
        $app->render('admin/user_warn.html.twig', array('p' => $user));
        return;
    }
    // in post - receiving submission
    $reason = $app->request()->post('reason');
 
    if ($user) {
        
     
        
        $emailBody = $app->view()->render('admin/admin_invite_form.html.twig', array('user' => $user, 'reason'=>$reason ));
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html\r\n";
        $headers .= "From: Noreply <noreply@ipd10.com>\r\n";
        $toEmail .= sprintf("%s <%s>\r\n", htmlentities($user['name']), $user['email']);
        
        mail($toEmail, "User Warning Message " . $_SERVER['SERVER_NAME'], $emailBody, $headers);
        
        echo "Warning Sent";
        
 
    }
    else { // failed request
        $app->render('passreset_request.html.twig', array('error' => true));
    }
    
})->via('GET', 'POST');

