<?php

// fake $app, $log so that Netbeans can provide suggestions while typing code
if (false) {
    $app = new \Slim\Slim();
    $log = new Logger('main');
}
$app->get('/profile', function() use ($app, $log) {

    if (!$_SESSION['user']) {
        $app->render('access_denied.html.twig');
        return;
    } else {
        $profileList = DB::queryFirstRow('SELECT * FROM users  WHERE users.id=%i', $_SESSION['user']['id']);

        if (!$profileList) {

//     print_r($profileList); 
            $app->render('not_found.html.twig');
            return;
        } else {

            $app->render('profile.html.twig', array('pl' => $profileList));
        }
    }
});


//================================EDIT===================================================================
$app->get('/editprofile', function() use ($app, $log) {


    if (!$_SESSION['user']) {
        $app->render('access_denied.html.twig');
        return;
    } else{
        $profileList = DB::queryFirstRow('SELECT * FROM users WHERE id=%i', $_SESSION['user']['id']);

    if (!$profileList) {
        print_r($profileList);
        $app->render('not_found.html.twig');
        return;
    } else {
        $app->render('editprofile.html.twig', array('pl' => $profileList));
    }
    
    }
});


$app->post('/editprofile', function() use ($app, $log) {
    //TODO: edit profile
    if (!$_SESSION['user']) {

        $app->render('access_denied.html.twig');
        return;
    }
    
    //upload checking 
    $profileImage = array();
    if (isset($_FILES['profileImage'])) {
        $profileImage = $_FILES['profileImage'];
        if ($profileImage['error'] != 0) {
            array_push($errorList, "Error uploading file");
            $log->err("Error uploading file: " . print_r($profileImage, true));
        } else {
            if (strstr($profileImage['name'], '..')) {
                array_push($errorList, "Invalid file name");
                $log->warn("Uploaded file name with .. in it (possible attack): " . print_r($profileImage, true));
            }
            // TODO: check if file already exists, check maximum size of the file, dimensions of the image etc.
            $info = getimagesize($profileImage["tmp_name"]);
            if ($info == FALSE) {
                array_push($errorList, "File doesn't look like a valid image");
            } else {
                if ($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/gif' || $info['mime'] == 'image/png') {
                    // image type is valid - all good
                } else {
                    array_push($errorList, "Image must be a JPG, GIF, or PNG only.");
                }
            }
        }
    } else { // no file uploaded
        if ($op == 'add') {
            array_push($errorList, "Image is required when creating new product");
        }
    }
    //===============================================================================
    //transfer file
        if ($profileImage) {
            $sanitizedFileName = preg_replace('[^a-zA-Z0-9_\.-]', '_', $profileImage['name']);
            $imagePath = 'profileimages/' . $sanitizedFileName;
            if (!move_uploaded_file($profileImage['tmp_name'], $imagePath)) {
                $log->err("Error moving uploaded file: " . print_r($profileImage, true));
                $app->render('internal_error.html.twig');
                return;
            }
            // TODO: if EDITING and new file is uploaded we should delete the old one in uploads
            $values['imagePath'] = "/" . $imagePath;
        }
        //end transfer file=====================================
//        print_r($profileImage);
        DB::update('users', array('profilePicPath'=>$imagePath),'id=%i', $_SESSION['user']['id']);
        $app->render('/todos_add_success.html.twig');
    
});
