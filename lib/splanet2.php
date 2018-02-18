<?php
function saveJSON($iksm_session, $type){
    $headers = [
        "Accept-language: ja",
        "Cookie: iksm_session=".$iksm_session
    ];
    $options['http'] = [
        'method' => 'GET',
        'header' => implode("\r\n", $headers),
    ];
    $context = stream_context_create($options);

    $url = "https://app.splatoon2.nintendo.net/api/results";
    $res = json_decode(@file_get_contents($url,false,$context),false);

    if(is_null($res)){
        echo("Your iksm_session is expired or wrong!\nCheck whether iksm_session is valid.\n");
        exit(1);
    }else{
        $info = [
            "id" => $res->results[0]->player_result->player->principal_id,
            "num" => $res->results[0]->battle_number 
        ];
    }

    // Save JSON
    switch($type){
    case "-r":
        // save user record
        $path = dirname(getcwd())."/json/records/";
        check_exists($path);
        $filename = $info["id"].".json";
        $url = "https://app.splatoon2.nintendo.net/api/records";
        break;
    case "-a":
        // save all battle results
        $path = dirname(getcwd())."/json/results/";
        check_exists($path);
        $filename = $info["id"].".json";
        $url = "https://app.splatoon2.nintendo.net/api/results";
        break;
    case "-d":
        // save a each battle detail
        $path = dirname(getcwd())."/json/results/".$info["id"]."/";
        check_exists($path);
        for($i=0; $i<50; $i++){
            if(file_exists($path.$filename)){
                break;
            }
            $filename = ($info["num"]-$i).".json";
            $url = "https://app.splatoon2.nintendo.net/api/results/".($info["num"]-$i);
            $json = fopen($path.$filename, "w+b");
            fwrite($json, @file_get_contents($url, false, $context));
            fclose($json);
            echo("Save ".$filename."(".$i."/50)\n");
        }
        echo("Done.\n");
        exit(0);
        break;
    case "-i":
        // save image
        $headers = [
            "Accept-language: ja",
            "x-requested-with: XMLHttpRequest",
            "Cookie: iksm_session=".$iksm_session
        ];
        $options['http'] = [
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
        ];
        $context = stream_context_create($options);
        
        $path = dirname(getcwd())."/share/".$info["id"]."/";
        check_exists($path);
        for($i=0; $i<50; $i++){
            $filename = ($info["num"]-$i).".png";
            if(file_exists($path.$filename)){
                break;
            }
            $url = "https://app.splatoon2.nintendo.net/api/share/results/".($info["num"]-$i);
            $res = json_decode(@file_get_contents($url, false, $context),false);
            $png = fopen($path.$filename, "w+b");
            fwrite($png, file_get_contents($res->url));
            fclose($png);
            echo("Save ".$filename."(".($i+1)."/50)\n");
        }
        echo("Done.\n");
        exit(0);
        break;
    case "-t":
        // save a timeline
        $path = dirname(getcwd())."/json/timeline/".$info["id"]."/";
        check_exists($path);
        $filename = $info["id"].".json";
        $url = "https://app.splatoon2.nintendo.net/api/timeline";
        break;
    case "-h":
        echo("Usage: php splanet2.php [option] [iksm_session]\n");
        echo("[option]\n");
        echo("-a : save results[your weapon, skill, result(win or lose), ...etc] for recent 50 games.\n");
        echo("-d : save detailed results[include other weapon, skill, ...etc] for recent 50 games.\n");
        echo("-r : save records[total paint point, league stat, stage stat, ...etc].\n");
        echo("-t : save timeline[information of sarmon run, splanet gear shop, ...and more].\n");
        exit(0);
        break;
    default:
        echo("No such options.\nPlease read the help [-h].");
        exit(0);
        break;
    }
    echo("Saving JSON...\n");
    $json = fopen($path.$filename, "w+b");
    fwrite($json, @file_get_contents($url, false, $context));
    fclose($json);
    echo("Done!\n");
}

function check_exists($path){
    if(!file_exists($path)){
        echo("Such directory is not existed.\n");
        if(mkdir($path, 0777, True)){
            echo("Success to create directory.\n");
        }else{
            echo("Failed to create directory.\n");
        }
    }
}

function showhelp(){
    echo("Usage: php splanet2.php [option] [iksm_session]\n");
    echo("[option]\n");
    echo("-a : save results[your weapon, skill, result(win or lose), ...etc] for recent 50 games.\n");
    echo("-d : save detailed results[include other's weapon, skill, ...etc] for recent 50 games.\n");
    echo("-r : save records[total paint point, league stat, stage stat, ...etc].\n");
    echo("-t : save timeline[information of sarmon run, splanet gear shop, ...and more].\n");
}

// Main
if(sizeof($argv) === 3){
    saveJSON($argv[2], $argv[1]);
}else{
    showhelp();
}
?>
