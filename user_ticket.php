<?php

if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}


$app->get('/tickets', function() use ($app) {
    
    
  $users = DB::query('SELECT * FROM users WHERE id=%i', $_SESSION['user']['id']);
    
    if (!$users) {
         $app->render('access_denied.html.twig');
        return;
    }
    
    $app->render('user_ticket.html.twig');
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