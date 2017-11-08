<?php

if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}


$app->get('/chat/:chatId', function($id) use ($app) {
   
    $chatfetch = DB::queryFirstRow('SELECT * FROM chats WHERE id=%i', $id);
    if (!$chatfetch) {
        $app->render('not_found.html.twig');
        return;
    }
    $app->render('/chat.html.twig', array('p' => $chatfetch));
});