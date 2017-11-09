<?php

if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}


$app->get('/chat', function() use ($app) {
   
    $chatfetch = DB::queryFirstRow('SELECT c.userId1, c.userId2, u1.username as u1username, u2.username as u2username,
FROM users u1, chats c, users u2
WHERE c.userId1 = u1.id AND c.userId2 = u2.id');
    if (!$chatfetch) {
        $app->render('not_found.html.twig');
        return;
    }
    $app->render('/chat.html.twig', array('p' => $chatfetch));
});