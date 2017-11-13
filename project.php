<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

session_start();

require_once 'vendor/autoload.php';


DB::$dbName = 'cp4809_dating';
DB::$user = 'cp4809_dating';
DB::$encoding = 'utf8';
DB::$password = 'OHXS.~!Skx~,';

// ======================================= ERROR handlers
DB::$error_handler = 'sql_error_handler';
DB::$nonsql_error_handler = 'nonsql_error_handler';

function sql_error_handler($params) {
    global $app, $log;
    $log->err("SQL ERROR: " . $params['error']);
    $log->err("in query: " . $params['query']);
    http_response_code(500);
    $app->render('error_internal.html.twig');
    die;
}

function nonsql_error_handler($params) {
    global $app, $log;
    $log->err("SQL ERROR: " . $params['error']);
    http_response_code(500);
    $app->render('error_internal.html.twig');
    die;
}


// Slim creation and setup
$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig()
        ));

$view = $app->view();
$view->parserOptions = array(   
    'debug' => true,
    'cache' => dirname(__FILE__) . '/cache'
);
$view->setTemplatesDirectory(dirname(__FILE__) . '/templates');

// create a log channel
$log = new Logger('main');
$log->pushHandler(new StreamHandler('logs/everything.log', Logger::DEBUG));
$log->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));


$twig = $app->view()->getEnvironment();
$twig->addGlobal('userSession', $_SESSION['user']);

$admin=DB::query('SELECT * From admins where id=%s and  username=%s',$_SESSION['user']['id'], $_SESSION['user']['username'] );
$twig->addGlobal('adminSession',$admin);

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = array();
}

// ============================================================= INDEX ================================================================
$app->get('/', function() use ($app) {
  
    
   $productList = DB::query('SELECT *, YEAR(CURRENT_TIMESTAMP) - YEAR(birthDate) - (RIGHT(CURRENT_TIMESTAMP, 5) < RIGHT(birthDate, 5)) as age FROM users');
    
  
    
    $age = date_diff(date_create($productList['birthDate']), date_create('now'))->y;
    
    
    
   $app->render('/index.html.twig', array('list' => $productList, 'age' => $age));
});





// ============================================================= PASSWORD REQUEST ================================================================


require_once 'password_request.php';



// ============================================================= ADMINS TABLE ================================================================

require_once 'admin_user_warn.php';

require_once 'admin_panel.php';
    
require_once 'admin_login.php';

require_once 'admin_register.php';


// ============================================================= USERS TABLE ================================================================

require_once 'user_ticket.php';

require_once 'chat.php';

require_once 'login_logout.php';

require_once 'register.php';

require_once 'profile.php';

$app->run();
