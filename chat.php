<?php

if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}


$app->get('/chats', function() use ($app) {
    
    
    $chatList = DB::query( 'SELECT c.id, u1.name as user1,u1.profilePicPath as user1Pic, u2.name as user2, u2.profilePicPath as user2Pic  FROM chats as c '
            . 'INNER JOIN users as u1 on u1.Id= c.userId1 '
            . 'INNER JOIN users as u2 on u2.Id= c.userId2 WHERE c.userId1=%s or c.userId2=%s',$_SESSION['user']['id'],$_SESSION['user']['id']);
    
    if (!$chatList){
        echo'you don\'t havechats';
    }
 
    //print_r($chatList);
    $app->render('chat.html.twig', array('chatList'=>$chatList));
});

$app->get('/chats(/:chatId)', function($chatId) use ($app,$log){

$chat=DB::query('SELECT * FROM chatMessages WHERE chatId=%s ', $chatId);
//print_r($chat);
$app->render('messageBox.html.twig',array('mes'=>$chat));

});

//$app->get('/chats/:chatId/:message', function($chatId,$message) use ($app,$log){
//
//DB::insert('chatMessages' , array('authorId'=>$_SESSION['user']['id'],'chatId'=>$chatId,'messages'=>$message)); 
//echo json_encode(DB::affectedRows() == 1);
//});

$app->post('/ajax/addchatmsg/:chatId', function($chatId) use ($app,$log){
   $message=$app->request()->getBody();
   DB::insert('chatMessages' , array('authorId'=>$_SESSION['user']['id'],'chatId'=>$chatId,'messages'=>$message)); 
   echo 'true';
});
