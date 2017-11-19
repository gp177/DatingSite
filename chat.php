<?php

if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}
$app->get('/chats/new/:opId', function($opId) use ($app) {
    
    $chat=DB::queryFirstRow('SELECT * from chats WHERE (userId1=%s and UserId2=%s) or (userId1=%s and UserId2=%s)',$_SESSION['user']['id'],$opId,$opId,$_SESSION['user']['id']);
    
    if(!$chat){
        DB::insert('chats',array('userId1'=>$_SESSION['user']['id'],'userId2'=>$opId));
    }
    
    $chatList = DB::query( 'SELECT c.id, u1.name as user1,u1.profilePicPath as user1Pic, u2.name as user2,c.userId1 as u1Id, c.userId2 as u2Id, u2.profilePicPath as user2Pic  FROM chats as c '
            . 'INNER JOIN users as u1 on u1.Id= c.userId1 '
            . 'INNER JOIN users as u2 on u2.Id= c.userId2 WHERE c.userId1=%s or c.userId2=%s',$_SESSION['user']['id'],$_SESSION['user']['id']);
    
   $app->render('chat.html.twig', array('chatList'=>$chatList));
});


$app->get('/chats', function() use ($app) {
    
    
    $chatList = DB::query( 'SELECT c.id, u1.name as user1,u1.profilePicPath as user1Pic, u2.name as user2,c.userId1 as u1Id, c.userId2 as u2Id, u2.profilePicPath as user2Pic  FROM chats as c '
            . 'INNER JOIN users as u1 on u1.Id= c.userId1 '
            . 'INNER JOIN users as u2 on u2.Id= c.userId2 WHERE c.userId1=%s or c.userId2=%s',$_SESSION['user']['id'],$_SESSION['user']['id']);
    
    if (!$chatList){
        echo'you don\'t havechats';
    }
 
    //print_r($chatList);
    $app->render('chat.html.twig', array('chatList'=>$chatList));
});

$app->get('/chats(/:chatId)', function($chatId) use ($app,$log){

$chat=DB::query('SELECT * FROM chatMessages WHERE chatId=%s ORDER by date DESC LIMIT 7 ', $chatId);

$app->render('messageBox.html.twig',array('mes'=>$chat, 'ses'=>$_SESSION['user']));

});
$app->get('/chats/history(/:chatId)', function($chatId) use ($app,$log){

$chat=DB::query('SELECT * FROM chatMessages WHERE chatId=%s', $chatId);

$app->render('chatHistory.html.twig',array('mes'=>$chat,'ses'=>$_SESSION['user']));

});

$app->post('/ajax/addchatmsg/:chatId', function($chatId) use ($app,$log){
   $message=$app->request()->getBody();
   DB::insert('chatMessages' , array('authorId'=>$_SESSION['user']['id'],'chatId'=>$chatId,'messages'=>$message)); 
   echo 'true';
});
