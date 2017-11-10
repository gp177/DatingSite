<?php

if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}

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
        $app->render('/admin/admin_panel.html.twig', array('adminSession' => $_SESSION['user']));
        header("Refresh:0; url=/admin/panel");
    }
    
});
