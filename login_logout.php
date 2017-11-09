<?php

if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}

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
        $app->render('index.html.twig', array('userSession' => $_SESSION['user']));
        header("Refresh:0; url=/");
        
    }
    
});