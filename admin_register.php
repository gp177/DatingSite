<?php


if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}



// ================================================= check if email is taken
$app->get('/isaemailregistered/:email', function($email) use ($app) {

    $row = DB::queryFirstRow("SELECT * FROM admins WHERE email=%s", $email);
    echo!$row ? "" : '<span style="font-size: 16px; background-color: lightblue; color: red; font-weight: bold;">Email already Registered<?span>';
});

// ================================================= check if username is taken
$app->get('/isausernameregistered/:username', function($username) use ($app) {

    $row = DB::queryFirstRow("SELECT * FROM admins WHERE username=%s", $username);
    echo!$row ? "" : '<span style="font-size: 16px; background-color: lightblue; color: red; font-weight: bold;">Username already Taken<?span>';
});

// ================================================= register
$app->get('/admin/register', function() use ($app) {
    $app->render('/admin/admin_register.html.twig');
});


$app->post('/admin/register', function() use ($app) {


    
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
     
});
