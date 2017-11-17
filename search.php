<?php

if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}


$app->get('/search', function() use ($app) {
      
   $productList = DB::query('SELECT *, YEAR(CURRENT_TIMESTAMP) - YEAR(birthDate) - (RIGHT(CURRENT_TIMESTAMP, 5) < RIGHT(birthDate, 5)) as age FROM users');
   $age = date_diff(date_create($productList['birthDate']), date_create('now'))->y;
   $app->render('/search.html.twig', array('list' => $productList, 'age' => $age));
});

$app->post('/search', function() use ($app) {
   $newList=array();
   $gender = $app->request()->post('gender');
   $fromAge1 = $app->request()->post('ageFrom');
   $toAge=$app->request()->post('ageTo');
   $photo = $app->request()->post('withPhoto');
   
  print_r($photo);
  if($photo!='photo'){
   $newList=DB::query('SELECT *,YEAR(CURRENT_TIMESTAMP) - YEAR(birthDate) - (RIGHT(CURRENT_TIMESTAMP, 5) < RIGHT(birthDate, 5)) as age FROM users
                        WHERE
                        (YEAR(CURRENT_TIMESTAMP) - YEAR(birthDate) - (RIGHT(CURRENT_TIMESTAMP, 5) < RIGHT(birthDate, 5)))>=%s and
                        (YEAR(CURRENT_TIMESTAMP) - YEAR(birthDate) - (RIGHT(CURRENT_TIMESTAMP, 5) < RIGHT(birthDate, 5)))<=%s and 
                        gender=%s',$fromAge,$toAge,$gender);
   }else{
       $newList=DB::query('SELECT *,YEAR(CURRENT_TIMESTAMP) - YEAR(birthDate) - (RIGHT(CURRENT_TIMESTAMP, 5) < RIGHT(birthDate, 5)) as age FROM users
                        WHERE
                        (YEAR(CURRENT_TIMESTAMP) - YEAR(birthDate) - (RIGHT(CURRENT_TIMESTAMP, 5) < RIGHT(birthDate, 5)))>=%s and
                        (YEAR(CURRENT_TIMESTAMP) - YEAR(birthDate) - (RIGHT(CURRENT_TIMESTAMP, 5) < RIGHT(birthDate, 5)))<=%s and 
                        gender=%s AND profilePicPath !="/images/nopic.png"',$fromAge,$toAge,$gender);
   }
    $app->render('/search.html.twig', array('list' => $newList, 'age' => $age));
});
