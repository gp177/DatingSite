<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

session_start();

require_once 'vendor/autoload.php';

DB::$dbName = 'cp4809_dating';
DB::$user = 'cp4809_dating';
DB::$encoding = 'utf8';
DB::$password = 'OHXS.~!Skx~,';


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

$app->get('/', function() use ($app) {
    
   echo "this is a dating website"; 
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
        $app->render('login_success.html.twig', array('userSession' => $_SESSION['user']));
    }
    
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
    $city = $app->request()->post('city');
    $country = $app->request()->post('country');
    
    
   

    $passEnc = password_hash($pass1, PASSWORD_BCRYPT);
    $values = array('name' => $name, 'username'=> $username, 'email' => $email, 'password' => $passEnc, 
        'birthDate' => $birthDate, 'gender' => $gender, 'city' => $city, 'country' => $country);
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
    
    

    // ======================================================= file upload validation
    $userimage = array();
    if ($_FILES['userimage']['error'] != UPLOAD_ERR_NO_FILE) {

        $userimage = $_FILES['userimage'];
        if ($userimage['error'] != 0) {
            array_push($errorList, "Error uploading file");
            $log->err("Error uploading file: " . print_r($userimage, true));
        } else {
            if (strstr($userimage['name'], '..')) {
                array_push($errorList, "Invalid file name");
                $log->warn("Uploaded file name with... in it (possible attack): " . print_r($userimage, true));
            }


            $info = getimagesize($userimage["tmp_name"]);
            if ($info == FALSE) {
                array_push($errorList, "File doesn't look like a valid Photo");
            }
            if ($_FILES["userimage"]["size"] > 200000) {
                array_push($errorList, "Photo is too large");
            } else {
                if ($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/gif' || $info['mime'] == 'image/png') {
                    // image type is valid 
                } else {
                    array_push($errorList, "Photo must be a JPG, GIF or PNG");
                }
            }
        }
    } else { // no file uploaded
      
    }
    //
    if ($errorList) { // 3. failed submission
        $app->render('/register.html.twig', array(
            'errorList' => $errorList,
            'v' => $values));
    } else { // 2. successful submission
        if ($userimage) {
            $sanitizedFileName = preg_replace('[^a-zA-Z0-9_\.-]', '_', $userimage['name']);
            $photopath = 'uploads/' . $sanitizedFileName;
            if (!move_uploaded_file($userimage['tmp_name'], $photopath)) {
                $log->err("Error moving uploaded file: " . print_r($userimage, true));
                $app->render('internal_error.html.twig');
                return;
            }

            $values['photopath'] = "/" . $photopath;
        }
        DB::insert('users', $values);
        $app->render('/register_success.html.twig');
    }
});


$app->run();
