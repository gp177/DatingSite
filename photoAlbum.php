<?php
if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}
$app->get('/photos', function() use ($app, $log) {
    
    $picList=DB::query('SELECT * FROM pictures WHERE userId=%s',$_SESSION['user']['id']);
    $app->render('photoAlbum.html.twig', array('picList'=>$picList));
});

$app->post('/photos', function() use ($app, $log) {
    
    foreach($_FILES["files"]["tmp_name"] as $key=>$tmp_name){
    $temp = $_FILES["files"]["tmp_name"][$key];
    $name = $_FILES["files"]["name"][$key];
//    $file_basename = substr($name, 0, strripos($name, '.')); // get file extention
    $file_ext = substr($name, strripos($name, '.')); // get file name
    $newfilename = date("Y-m-d")."_".md5(time()) . $file_ext[$key];
    if(empty($temp))
    {
        break;
    }
    print_r($key);
    DB::insert('pictures', array('userId'=>$_SESSION['user']['id'],'picPath'=>"profileimages/".$newfilename)); 
    move_uploaded_file($temp,"profileimages/".$newfilename);
}

  $app->render('photoAlbum.html.twig');
});