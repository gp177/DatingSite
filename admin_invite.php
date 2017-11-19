<?php

if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}




$app->map('/admin/panel/invite', function() use ($app, $log) {
    
    $adminvalid = DB::query('SELECT * FROM admins WHERE id=%i', $_SESSION['user']['id']);
    
    if (!$adminvalid) {
         $app->render('access_denied.html.twig');
        return;
    }
    
    
    if ($app->request()->isGet()) {
         $users = DB::query("SELECT * FROM users");
        $app->render('/admin/admin_invite.html.twig', array('list' => $users));
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


$app->map('/admin/register/:secretToken', function($secretToken) use ($app, $log) {
    
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
    $username = $app->request()->post('username');
    $email = $app->request()->post('email');
    $pass1 = $app->request()->post('pass1');
    $pass2 = $app->request()->post('pass2');
        
    
   $passEnc = password_hash($pass1, PASSWORD_BCRYPT);
     $values = array('username'=> $username, 'email' => $email, 'password' => $passEnc);
     
    $errorList = array();
    //
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL) == FALSE) {
        $values['email'] = '';
        array_push($errorList, "Email must look like a valid email");
    } 
    
    else {
        $row = DB::queryFirstRow("SELECT * FROM admins WHERE email=%s", $email);
        if ($row) {
            $values['email'] = '';
            array_push($errorList, "Email already in use");
        }
    }   
    
   
    
    if (strlen($username) < 5 || strlen($username) > 50) {
          $values['username'] = '';
        array_push($errorList, "Username must be between 5 and 50 characters");
    }
    else {
         $row = DB::queryFirstRow("SELECT * FROM admins WHERE username=%s", $username);
         if ($row) {
            $values['username'] = '';
            array_push($errorList, "username already Exists");
        }
    }

    // password validation
    if ($pass1 != $pass2) {
        array_push($errorList, "Passwords don't match");
    } else {
        if (strlen($pass1) < 2 || strlen($pass1) > 100) {
            array_push($errorList, "Password must be between 2 and 100 characters ");
        }
        if (!preg_match('/[A-Z]/', $pass1) || !preg_match('/[a-z]/', $pass1) || !preg_match('/[0-9' . preg_quote("!@#\$%^&*()_-+={}[],.<>;:'\"~`") . ']/', $pass1)) {
            array_push($errorList, "Password must include at least one of each: "
                    . "uppercase letter, lowercase letter, digit or special character");
        }
    }
    
  
    
    if ($errorList) {
        //3. failed submission
        $app->render('/admin/admin_register.html.twig', array('errorList' => $errorList, 'v' => $values));
    }
    else {
        //4. Successful submission
       
      DB::insert('admins', $values);
   $app->render('register_success.html.twig');
   
    }
    
   
    
})->via('GET', 'POST');
