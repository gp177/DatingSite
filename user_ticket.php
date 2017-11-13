<?php

if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}

// ========================================================== ticket response

$app->get('/tickets/view/:id', function($id) use ($app) {
    
     $users = DB::query('SELECT * FROM users WHERE id=%i', $_SESSION['user']['id']);
     
        if (!$users) {
         $app->render('access_denied.html.twig');
        return;
    }
    
   
      $ticketrespond = DB::query('SELECT * FROM ticketmessages INNER JOIN tickets ON ticketmessages.ticketId = tickets.id INNER JOIN admins ON ticketmessages.adminId = admins.id  WHERE tickets.id=%i', $id);
     
  
       $app->render('user_ticket_open.html.twig', array('list' => $ticketrespond));
});

$app->post('/tickets/view/:id', function($id) use ($app) {

    $users = DB::query('SELECT * FROM users WHERE id=%i', $_SESSION['user']['id']);
     if (!$users) {
         $app->render('access_denied.html.twig');
        return;
    }

    $messages = $app->request()->post('messages');
    $userId = $_SESSION['user']['id'];
    


    //

    $values = array('userId' => $userId, 'ticketId'=> $id, 'messages' => $messages);
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





// ================================================================ user tickets

$app->get('/tickets', function() use ($app) {
    
    
  $users = DB::query('SELECT * FROM users WHERE id=%i', $_SESSION['user']['id']);
  $userid = $_SESSION['user']['id'];
  
   $productList = DB::query("SELECT tickets.id AS tickid, tickets.userId, tickets.department, tickets.description, users.id, users.username, users.profilePicPath FROM tickets INNER JOIN users ON tickets.userId = users.id WHERE tickets.userId=%s", $userid);
    
    if (!$users) {
         $app->render('access_denied.html.twig');
        return;
    }
    
    $app->render('user_ticket.html.twig', array('list' => $productList));
});

$app->post('/tickets', function() use ($app) {


    $department = $app->request()->post('department');
    $description = $app->request()->post('description');
    $userId = $_SESSION['user']['id'];
    
    $errorList = array();
    
      if (strlen($description) < 10 || strlen($description) > 100) {
        array_push($errorList, "Description Must be in between 10 and 100 Characters");
    }
    
    if(!$department) {
           array_push($errorList, "A Department must be selected");
    }
    
    
    $values = array('userId' => $userId,  'department' => $department, 'description' => $description);
    
    if ($errorList) {
        //3. failed submission
        $app->render('/user_ticket.html.twig', array('errorList' => $errorList, 'v' => $values));
    }
    
    else {
        //4. Successful submission
      
        
    DB::insert('tickets', $values);
    
   $app->render('index.html.twig');
   header("Refresh:0; url=/");
   
    }
    
    });