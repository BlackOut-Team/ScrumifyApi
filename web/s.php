<?php
$allowedExts = array("gif", "jpeg", "jpg", "png");
$temp = explode(".", $_FILES["file"]["name"]);
$extension = end($temp);

if ((($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/jpg") || ($_FILES["file"]["type"] == "image/pjpeg") || ($_FILES["file"]["type"] == "image/x-png") || ($_FILES["file"]["type"] == "image/png")) && ($_FILES["file"]["size"] < 5000000) && in_array($extension, $allowedExts)) {
    if ($_FILES["file"]["error"] > 0) {
        $named_array = array("Response" => array(array("Status" => "error")));
        echo json_encode($named_array);
    } else {
        move_uploaded_file($_FILES["file"]["tmp_name"], "C:\wamp64\www\Scrumify\web\uploads\images/" . $_FILES["file"]["name"]);
        $objInputStream = fopen("C:\wamp64\www\Scrumify\web\uploads\images/" . $_FILES["file"]["name"], "rb");
        $objTempStream = fopen("C:\Users\Amira Doghri\Documents/3A-2S\JavaFX\ScrumifyD\src\scrumifyd\uploads\images/" . $_FILES["file"]["name"], "w+b");
        stream_copy_to_stream($objInputStream,$objTempStream );
        $Path = $_FILES["file"]["name"];
        $named_array = array("Response" => array(array("Status" => "ok")));
        echo json_encode($named_array);
    }
} else {
    $named_array = array("Response" => array(array("Status" => "invalid")));
    echo json_encode($named_array);
}
?>