<?php

if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}




// ================================================== admin list

$app->get('/admin/panel/admins', function() use ($app) {
    
        
    $adminvalid = DB::query('SELECT * FROM admins WHERE id=%i', $_SESSION['user']['id']);
    
    if (!$adminvalid) {
         $app->render('access_denied.html.twig');
        return;
    }

        
  
    $adminCount = DB::query('SELECT id FROM admins');
    
    $productList = DB::query("SELECT * FROM admins");
    $app->render('/admin/admin_list.html.twig', array('list' => $productList, 'userCount' => count($adminCount)));
});



// ========================================================== ticket response
$app->get('/admin/panel/statistics', function() use ($app) {
    
    $adminvalid = DB::query('SELECT * FROM admins WHERE id=%i', $_SESSION['user']['id']);
    
    if (!$adminvalid) {
         $app->render('access_denied.html.twig');
        return;
    }
    
     $app->render('/admin/admin_statistics.html.twig');
});



// ========================================================== ticket response

$app->get('/admin/panel/tickets/respond/:id', function($id) use ($app) {
    
     $adminvalid = DB::query('SELECT * FROM admins WHERE id=%i', $_SESSION['user']['id']);
    
    if (!$adminvalid) {
         $app->render('access_denied.html.twig');
        return;
    }
    
   // $tickets = DB::query('SELECT * FROM tickets WHERE tickets.id =%i', $id);
    
      $ticketrespond = DB::query('SELECT * FROM ticketmessages INNER JOIN tickets ON ticketmessages.ticketId = tickets.id INNER JOIN admins ON ticketmessages.adminId = admins.id  WHERE tickets.id=%i', $id);
     
  
       $app->render('/admin/admin_tickets_respond.html.twig', array('list' => $ticketrespond));
});

$app->post('/admin/panel/tickets/respond/:id', function($id) use ($app) {

   $adminvalid = DB::query('SELECT * FROM admins WHERE id=%i', $_SESSION['user']['id']);
    
    if (!$adminvalid) {
         $app->render('access_denied.html.twig');
        return;
    }

    $messages = $app->request()->post('messages');
    $adminId = $_SESSION['user']['id'];
    


    //

    $values = array('adminId' => $adminId, 'ticketId'=> $id, 'messages' => $messages);
    $errorList = array();
    //

  
    //
    if ($errorList) { // 3. failed submission
        $app->render('post_new.html.twig', array(
            'errorList' => $errorList,
            'v' => $values));
    } else { // 2. successful submission
        DB::insert('ticketmessages', $values);
        
   
    
    }
});




// ================================================== ticket list

$app->get('/admin/panel/tickets', function() use ($app) {
    
       
          
    $adminvalid = DB::query('SELECT * FROM admins WHERE id=%i', $_SESSION['user']['id']);
    
    if (!$adminvalid) {
         $app->render('access_denied.html.twig');
        return;
    }

        
    $productList = DB::query("SELECT tickets.id AS tickid, tickets.userId, tickets.department, tickets.description, users.id, users.username, users.profilePicPath FROM tickets INNER JOIN users ON tickets.userId = users.id");
    
    
    $app->render('/admin/admin_tickets.html.twig', array('list' => $productList));
});



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
