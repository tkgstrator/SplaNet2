<?php
require("splanet2.php");

$sessions = array(
    // "YOUR IKSM_SESSION KEY IS HERE",
);

$types = array(
    "-a",
    "-c",
    "-d",
    "-m",
    "-r",
    "-t",
    "-s",
    "-i",
    "-j",
);
foreach($sessions as $key){
    $user = new Splanet($key);
    foreach($types as $type){
        $user->save($type);
    }
}
echo("Done.");
?>

