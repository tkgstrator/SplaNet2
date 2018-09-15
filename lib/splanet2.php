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
    case "-a":
        // save all battle results
        $path = dirname(dirname(__FILE__))."/api/results/";
        echo($path."\n");
        check_exists($path);
        $filename = $info["id"].".json";
        $url = "https://app.splatoon2.nintendo.net/api/results";
        echo("Saving all battle results json...\n");
        writeJSON($path, $filename, $url, $context);
        break;
    case "-c":
        // save coop schedules
        $path = dirname(dirname(__FILE__))."/api/coop_schedules/";
        check_exists($path);
        $filename = $info["id"].".json";
        $url = "https://app.splatoon2.nintendo.net/api/coop_schedules";
        echo("Saving coop schedules json...\n");
        writeJSON($path, $filename, $url, $context);
        break;
    case "-d":
        // save a each battle detail
        $path = dirname(dirname(__FILE__))."/api/results/".$info["id"]."/";
        check_exists($path);
        echo("Saving detailed battle results json...\n");
        for($i=0; $i<50; $i++){
            $filename = ($info["num"]-$i).".json";
            if(file_exists($path.$filename)){
                echo("Skip ".$filename."(".($i+1)."/50)\n");
                break;
            }else{
                $url = "https://app.splatoon2.nintendo.net/api/results/".($info["num"]-$i);
                $json = fopen($path.$filename, "w+b");
                fwrite($json, @file_get_contents($url, false, $context));
                fclose($json);
                echo("Save ".$filename."(".($i+1)."/50)\n");
            }
        }
        break;
    case "-h":
        showhelp();
        break;
    case "-i":
        // save images
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

        $path = dirname(dirname(__FILE__))."/share/".$info["id"]."/";
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
        break;
    case "-l":
        // save a league match ranking
        $path = dirname(dirname(__FILE__))."/api/league_match_ranking/";
        check_exists($path);
        $types = ["P", "T"];
        for($t=1500562800; $t<=time(); $t+=3600*24){
            for($i=0; $i<24; $i+=2){
                foreach($types as $type){
                    $mode = date("ymd", $t).sprintf("%02d", $i).$type;
                    $filename = $mode.".json";
                    echo($filename."\n");
                    if(file_exists($path.$filename)){
                        continue;
                    }
                    $url = "https://app.splatoon2.nintendo.net/api/league_match_ranking/".$mode."/ALL";
                    $json = fopen($path.$filename, "w+b");
                    fwrite($json, @file_get_contents($url, false, $context));
                    fclose($json);
                    echo("Save ".$filename."(".($i+1)."/50)\n");
                }
            }
        }
        break;
    case "-m":
        // save merchandises
        $path = dirname(dirname(__FILE__))."/api/onlineshop/merchandises/";
        check_exists($path);
        $filename = $info["id"].".json";
        $url = "https://app.splatoon2.nintendo.net/api/onlineshop/merchandises";
        echo("Saving marchandises json...\n");
        writeJSON($path, $filename, $url, $context);
        break;
    case "-r":
        // save user record
        $path = dirname(dirname(__FILE__))."/api/records/";
        check_exists($path);
        $filename = $info["id"].".json";
        $url = "https://app.splatoon2.nintendo.net/api/records";
        echo("Saving records json...\n");
        writeJSON($path, $filename, $url, $context);
        break;
    case "-s":
        // save hero mode results
        $path = dirname(dirname(__FILE__))."/api/records/hero/";
        check_exists($path);
        $filename = $info["id"].".json";
        $url = "https://app.splatoon2.nintendo.net/api/records/hero";
        echo("Saving hero mode json...\n");
        writeJSON($path, $filename, $url, $context);
        break;
    case "-t":
        // save timeline
        $path = dirname(dirname(__FILE__))."/api/timeline/";
        check_exists($path);
        $filename = $info["id"].".json";
        $url = "https://app.splatoon2.nintendo.net/api/timeline";
        echo("Saving timeline json...\n");
        writeJSON($path, $filename, $url, $context);
        break;
    default:
        echo("No such options.\nPlease read the help [-h].\n");
        break;
    }
}

function writeJSON($path, $filename, $url, $context){
    echo($path.$filename."\n");
    $json = fopen($path.$filename, "w+b");
    fwrite($json, @file_get_contents($url, false, $context));
    fclose($json);
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
    echo("-i : save the screen shot of results.\n");
    echo("-l : save the league match ranking for all time.\n");
    echo("-r : save records[total paint point, league stat, stage stat, ...etc].\n");
    echo("-t : save timeline[information of sarmon run, splanet gear shop, ...and more].\n");
    echo("-s : save records of hero mode.\n");
}
?>
