<?php

if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}

// ================================================== users list

$app->get('/admin/panel/users', function() use ($app) {
    
        
    $adminvalid = DB::query('SELECT * FROM admins WHERE id=%i', $_SESSION['user']['id']);
    
    if (!$adminvalid) {
         $app->render('access_denied.html.twig');
        return;
    }

        
  
    $userCount = DB::query('SELECT id FROM users');
    
    $productList = DB::query("SELECT * FROM users");
    $app->render('/admin/admin_users.html.twig', array('list' => $productList, 'userCount' => count($userCount)));
});


// ================================================== panel
$app->get('/admin/panel', function() use ($app) {
    
     $adminvalid = DB::query('SELECT * FROM admins WHERE id=%i', $_SESSION['user']['id']);
    
    if (!$adminvalid) {
         $app->render('access_denied.html.twig');
        return;
    }
    
    $app->render('admin/admin_panel.html.twig');
});


// ======================================= remove user

$app->get('/admin/panel/delete/:id', function($id) use ($app) {
    
     $adminvalid = DB::query('SELECT * FROM admins WHERE id=%i', $_SESSION['user']['id']);
    
    if (!$adminvalid) {
         $app->render('access_denied.html.twig');
        return;
    }
   
    $usersel = DB::queryFirstRow('SELECT * FROM users WHERE id=%i', $id);
    if (!$usersel) {
        $app->render('not_found.html.twig');
        return;
    }
    $app->render('/admin/users_remove.html.twig', array('p' => $usersel));
});

$app->post('/admin/panel/delete/:id', function($id) use ($app) {
   
    $confirmed = $app->request()->post('confirmed');
    if ($confirmed != 'true') {
        $app->render('admin/not_found.html.twig');
        return;
    }
    DB::delete('users', "id=%i", $id);
    if (DB::affectedRows() == 0) {
        $app->render('admin/not_found.html.twig');
    } else {
        $app->render('/admin/admin_users.html.twig');
    }
});
