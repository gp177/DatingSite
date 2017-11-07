<?php

if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}

// ================================================== users list

$app->get('/admin/panel/users', function() use ($app) {

   

    $productList = DB::query("SELECT * FROM users");
    $app->render('/admin/admin_users.html.twig', array('list' => $productList));
});


// ================================================== panel
$app->get('/admin/panel', function() use ($app) {
    $app->render('admin/admin_panel.html.twig');
});
