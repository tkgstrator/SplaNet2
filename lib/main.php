<?php
require("splanet2.php");

$sessions = array(
    "YOUR IKSM_SESSION KEY IS HERE";
);

$types = array(
    "-a",
    "-c",
    "-d",
    "-m",
    "-r",
    "-t",
    "-s",
    "-i"
);
foreach($sessions as $key){
    foreach($types as $type){
        saveJSON($key, $type);
    }
}
echo("Done.");
?>

