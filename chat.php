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
    
    
    
   
//    $chatfetch = DB::query('SELECT c.userId1, c.userId2, u1.username as u1username, u2.username as u2username
//FROM users u1, chats c, users u2
//WHERE c.userId1 = u1.id AND c.userId2 = u2.id');
//    
   
    
//    if (!$chatfetch) {
//        $app->render('not_found.html.twig');
//        return;
//    }
    print_r($chatList);
    $app->render('chat.html.twig', array('chatList'=>$chatList));
});

$app->get('/chats(/:chatId)', function($chatId) use ($app){

$chat=DB::query('SELECT * FROM chatMessages WHERE chatId=%s ', $chatId);
print_r($chat);
$app->render('messageBox.html.twig',array('mes'=>$chat));

});
