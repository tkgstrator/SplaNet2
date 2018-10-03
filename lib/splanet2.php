<?php
class Splanet{
    function __construct($session){
        $this->session = $session;
        $this->isvalid();
    }

    // iksm_sessionが有効かどうかを調べる
    function isvalid(){
        $url = "https://app.splatoon2.nintendo.net/api/results";

        $headers = array(
            "Accept-language: ja",
            "Cookie: iksm_session=".$this->session
        );

        $options['http'] = array(
            'method' => 'GET',
            'header' => implode("\r\n", $headers)
        );

        $this->context = stream_context_create($options);

        $res = json_decode(@file_get_contents($url,false,$this->context),false);

        if(is_null($res)){
            echo("Your iksm_session is expired or wrong!\nCheck whether iksm_session is valid.\n");
            exit(1);
        }else{
            $this->uid = $res->results[0]->player_result->player->principal_id;
            $this->game = $res->results[0]->battle_number;
        }
    }


    // 該当するディレクトリがなければ作成する
    function makeDir($path){
        if(!file_exists($path)){
            echo("Such directory is not existed.\n");
            if(mkdir($path, 0777, True)){
                echo("Success to create directory.\n");
            }else{
                echo("Failed to create directory.\n");
            }
        }
    }


    // JSONファイルを保存する
    function save($type){
        switch($type){
        case "-a":
            // save all battle results
            $path = dirname(__FILE__)."/json/results/";
            $this->makeDir($path);
            $filename = $this->uid.".json";
            $url = "https://app.splatoon2.nintendo.net/api/results";
            $json = fopen($path.$filename, "w+b");
            fwrite($json, @file_get_contents($url, false, $this->context));
            fclose($json);
            break;
        case "-c":
            // save coop schedules
            $path = dirname(__FILE__)."/json/coop_schedules/";
            $this->makeDir($path);
            $filename = $this->uid.".json";
            $url = "https://app.splatoon2.nintendo.net/api/coop_schedules";
            $json = fopen($path.$filename, "w+b");
            fwrite($json, @file_get_contents($url, false, $this->context));
            fclose($json);
            break;
        case "-d":
            // save a each battle detail
            $path = dirname(__FILE__)."/json/results/".$this->game."/";
            $this->makeDir($path);
            for($i=0; $i<50; $i++){
                $filename = ($this->game - $i).".json";
                // 既にファイルがあれば取得済みということなのでループを終了
                if(file_exists($path.$filename)){
                    break;
                }
                $url = "https://app.splatoon2.nintendo.net/api/results/".($this->game - $i);
                $json = fopen($path.$filename, "w+b");
                fwrite($json, @file_get_contents($url, false, $this->context));
                fclose($json);
                echo("Save ".$filename."(".($i+1)."/50)\n");
            }
            break;
        case "-h":
            showhelp();
            exit(0);
            break;
        case "-i":
            // save images
            $headers = [
                "Accept-language: ja",
                "x-requested-with: XMLHttpRequest",
                "Cookie: iksm_session=".$this->session
            ];
            $options['http'] = [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
            ];
            $context = stream_context_create($options);

            $path = dirname(__FILE__)."/share/".$this->uid."/";
            $this->makeDir($path);
            for($i=0; $i<50; $i++){
                $filename = ($this->game - $i).".png";
                if(file_exists($path.$filename)){
                    break;
                }
                $url = "https://app.splatoon2.nintendo.net/api/share/results/".($this->game - $i);
                $res = json_decode(@file_get_contents($url, false, $context),false);
                $png = fopen($path.$filename, "w+b");
                fwrite($png, file_get_contents($res->url));
                fclose($png);
                echo("Save ".$filename."(".($i+1)."/50)\n");
            }
            break;
        case "-j":
            // save user coop results
            $path = dirname(__FILE__)."/json/coop_results/";
            $this->makeDir($path);
            $filename = $this->uid.".json";
            $url = "https://app.splatoon2.nintendo.net/api/coop_results";
            $json = fopen($path.$filename, "w+b");
            fwrite($json, @file_get_contents($url, false, $this->context));
            fclose($json);
            break;
        case "-l":
            // save a league match ranking
            $path = dirname(__FILE__)."/json/league_match_ranking/";
            $this->makeDir($path);
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
            $path = dirname(__FILE__)."/json/merchandises/";
            $this->makeDir($path);
            $filename = $this->uid.".json";
            $url = "https://app.splatoon2.nintendo.net/api/onlineshop/merchandises";
            $json = fopen($path.$filename, "w+b");
            fwrite($json, @file_get_contents($url, false, $this->context));
            fclose($json);
            break;
        case "-t":
            // save timeline
            $path = dirname(__FILE__)."/json/timeline/";
            $this->makeDir($path);
            $filename = $this->uid.".json";
            $url = "https://app.splatoon2.nintendo.net/api/timeline";
            $json = fopen($path.$filename, "w+b");
            fwrite($json, @file_get_contents($url, false, $this->context));
            fclose($json);
            break;
        case "-r":
            // save user record
            $path = dirname(__FILE__)."/json/records/";
            $this->makeDir($path);
            $filename = $this->uid.".json";
            $url = "https://app.splatoon2.nintendo.net/api/records";
            $json = fopen($path.$filename, "w+b");
            fwrite($json, @file_get_contents($url, false, $this->context));
            fclose($json);
            break;
        case "-s":
            // save hero mode results
            $path = dirname(__FILE__)."/json/records/hero/";
            $this->makeDir($path);
            $filename = $this->uid.".json";
            $url = "https://app.splatoon2.nintendo.net/api/records/hero";
            $json = fopen($path.$filename, "w+b");
            fwrite($json, @file_get_contents($url, false, $this->context));
            fclose($json);
            break;
        default:
            echo("No such options.\nPlease read the help [-h].\n");
            exit(0);
            break;
        }
    }
}
?>
