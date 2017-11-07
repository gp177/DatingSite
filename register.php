<?php
if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}

// ================================================= check if email is taken
$app->get('/isemailregistered/:email', function($email) use ($app) {

    $row = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
    echo!$row ? "" : '<span style="font-size: 16px; background-color: lightblue; color: red; font-weight: bold;">Email already Registered<?span>';
});

// ================================================= check if username is taken
$app->get('/isusernameregistered/:username', function($username) use ($app) {

    $row = DB::queryFirstRow("SELECT * FROM users WHERE username=%s", $username);
    echo!$row ? "" : '<span style="font-size: 16px; background-color: lightblue; color: red; font-weight: bold;">Username already Taken<?span>';
});


// =============================================== register

$app->get('/register', function() use ($app) {
    $app->render('register.html.twig');
});

$app->post('/register', function() use ($app) {


    $name = $app->request()->post('name');
    $username = $app->request()->post('username');
    $email = $app->request()->post('email');
    $pass1 = $app->request()->post('pass1');
    $pass2 = $app->request()->post('pass2');
    $birthDate = $app->request()->post('birthDate');
    $gender = $app->request()->post('gender');
    $postalCode = $app->request()->post('postalCode');
    $city = $app->request()->post('city');
    $country = $app->request()->post('country');
    
    
   $passEnc = password_hash($pass1, PASSWORD_BCRYPT);
     $values = array('name' => $name, 'username'=> $username, 'email' => $email, 'password' => $passEnc, 
        'birthDate' => $birthDate, 'gender' => $gender, 'postalCode' => $postalCode, 'city' => $city, 'country' => $country);
     
    $errorList = array();
    //
    if (strlen($name) < 3 || strlen($name) > 60) {
        $values['name'] = '';
        array_push($errorList, "Name must be between 3 and 60 characters");
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) == FALSE) {
        $values['email'] = '';
        array_push($errorList, "Email must look like a valid email");
    } 
    
    else {
        $row = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $email);
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
         $row = DB::queryFirstRow("SELECT * FROM users WHERE username=%s", $username);
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
        $app->render('/register.html.twig', array('errorList' => $errorList, 'v' => $values));
    }
    else {
        //4. Successful submission
       
      DB::insert('users', $values);
   $app->render('register_success.html.twig');
   
    }
     
});
