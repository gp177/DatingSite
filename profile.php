<?php
// fake $app, $log so that Netbeans can provide suggestions while typing code
if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}
$app->get('/profile', function() use ($app) {
    $profileList = array();
    if ($_SESSION['user']) {
        $profileList = DB::query('SELECT * FROM users WHERE Id=%i', $_SESSION['user']['id']);
    }
    $app->render('profile.html.twig', array('profileList' => $profileList));
});
