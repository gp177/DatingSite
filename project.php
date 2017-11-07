<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

session_start();

require_once 'vendor/autoload.php';


DB::$dbName = 'cp4809_dating';
DB::$user = 'cp4809_dating';
DB::$encoding = 'utf8';
DB::$password = 'OHXS.~!Skx~,';

// ======================================= ERROR handlers
DB::$error_handler = 'sql_error_handler';
DB::$nonsql_error_handler = 'nonsql_error_handler';

function sql_error_handler($params) {
    global $app, $log;
    $log->err("SQL ERROR: " . $params['error']);
    $log->err("in query: " . $params['query']);
    http_response_code(500);
    $app->render('error_internal.html.twig');
    die;
}

function nonsql_error_handler($params) {
    global $app, $log;
    $log->err("SQL ERROR: " . $params['error']);
    http_response_code(500);
    $app->render('error_internal.html.twig');
    die;
}


// Slim creation and setup
$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig()
        ));

$view = $app->view();
$view->parserOptions = array(   
    'debug' => true,
    'cache' => dirname(__FILE__) . '/cache'
);
$view->setTemplatesDirectory(dirname(__FILE__) . '/templates');

// create a log channel
$log = new Logger('main');
$log->pushHandler(new StreamHandler('logs/everything.log', Logger::DEBUG));
$log->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));


if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = array();
}


// ============================================================= INDEX ================================================================
$app->get('/', function() use ($app) {
    
  
   
   $app->render('/index.html.twig');
});
// ============================================================= ADMINS TABLE ================================================================




// ================================================== panel
$app->get('/admin/panel', function() use ($app) {
    $app->render('admin/admin_panel.html.twig');
});
    



// ================================================== login
$app->get('/admin/login', function() use ($app) {
    $app->render('admin/login.html.twig');
});

$app->post('/admin/login',function() use ($app) {
    $username = $app->request()->post('username');
    $pass = $app->request()->post('pass');
    $row = DB::queryFirstRow("SELECT * FROM admins WHERE username=%s", $username);
    $error = false;
    if (!$row) {
        $error = true; // user not found
    } else {
        if (password_verify($pass, $row['password']) == FALSE) {
            $error = true; // password invalid
        }
    }
    if ($error) {
        $app->render('admin/login.html.twig', array('error' => true));
    } else {
        unset($row['password']);
        $_SESSION['user'] = $row;
        $app->render('/admin/admin_panel.html.twig', array('userSession' => $_SESSION['user']));
    }
    
});

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

// ============================================== register

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


// ============================================================= USERS TABLE ================================================================

// ================================================== logout

$app->get('/logout', function() use ($app) {
    $_SESSION['user'] = array();
    $app->render('logout.html.twig', array('userSession' => $_SESSION['user']));
});





// ================================================== login
$app->get('/login', function() use ($app) {
    $app->render('login.html.twig');
});

$app->post('/login',function() use ($app) {
    $username = $app->request()->post('username');
    $pass = $app->request()->post('pass');
    $row = DB::queryFirstRow("SELECT * FROM users WHERE username=%s", $username);
    $error = false;
    if (!$row) {
        $error = true; // user not found
    } else {
        if (password_verify($pass, $row['password']) == FALSE) {
            $error = true; // password invalid
        }
    }
    if ($error) {
        $app->render('login.html.twig', array('error' => true));
    } else {
        unset($row['password']);
        $_SESSION['user'] = $row;
        $app->render('/login_success.html.twig', array('userSession' => $_SESSION['user']));
    }
    
});

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


// ================================================== register
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

require_once 'profile.php';
$app->run();
