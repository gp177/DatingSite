<?php

if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}



$app->map('/admin/panel/invite', function() use ($app, $log) {
    
    if ($app->request()->isGet()) {
        $app->render('/admin/admin_invite.html.twig');
        return;
    }
    // in post - receiving submission
    $email = $app->request()->post('email');
    $user = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
    if ($user) {
        
       $secretToken = generateRandomString(50);
       DB::insertUpdate('passresets', array('userId' => $user['id'], 'secretToken' => $secretToken, 'expiryDateTime' => date("y-m-d H:i:s", strtotime("+1 day"))));
        
         $url = 'http://' . $_SERVER['SERVER_NAME'] . '/admin/register/' . $secretToken;
        $emailBody = $app->view()->render('admin/admin_invite_form.html.twig', array('name' => $user['name'], 'url' => $url));
         
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html\r\n";
        $headers .= "From: Noreply <noreply@ipd10.com>\r\n";
        $toEmail .= sprintf("%s <%s>\r\n", htmlentities($user['name']), $user['email']);
        
        mail($toEmail, "Admin invitation for " . $_SERVER['SERVER_NAME'], $emailBody, $headers);
        
        $app->render('/admin/admin_panel.html.twig');
 
    }
    else { // failed request
        $app->render('/admin/admin_invite.html.twig', array('error' => true));
    }
    
})->via('GET', 'POST');


$app->map('/admin/panel/invite/:secretToken', function($secretToken) use ($app, $log) {
    
    $row = DB::queryFirstRow('SELECT * FROM passresets WHERE secretToken=%s', $secretToken);
    
    if (!$row) {
        $app->render('not_found.html.twig');
        return;
    }
    if (strtotime($row['expiryDateTime']) < time()) {
        // row found but expired
        $app->render('not_found.html.twig');
        return;
    }
    
     $app->render('admin/panel/register.html.twig');
    
})->via('GET', 'POST');
