<?php


namespace App\Helpers;

class External {

    public static function reduceName($name) {
        $first_name = $last_name = null;
        $arr = explode(' ', $name);
        $num = count($arr);
        $count = floor($num/2);
        foreach($arr as $key => $arr_item){
            if($key==0||$key==1&&$num>3||$key==2&&$num>5){
                if($first_name){
                    $first_name .= ' ';
                }
                $first_name .= $arr_item;
            } else {
                if($last_name){
                    $last_name .= ' ';
                }
                $last_name .= $arr_item;
            }
        }
        return ['first_name'=>$first_name, 'last_name'=>$last_name];
    }

    public static function post( $url, $action, $parameters, $headers= [] ) {
        $url      .= '/'.$action;
        $rand_code = rand(100000, 999999);
        \Log::info('PostQuery '.$rand_code.': '.$url.' - '.json_encode($parameters));
        $result = \Http::withoutVerifying()->withOptions(["verify"=>false])->withHeaders($headers)->post($url, $parameters)->json();
        \Log::info('PostResponse '.$rand_code.': - '.json_encode($result));
        return $result;
    }

    public static function get( $url, $action, $parameters, $headers= [] ) {
        $url      .= '/'.$action;
        $rand_code = rand(100000, 999999);
        \Log::info('GetQuery '.$rand_code.': '.$url.' - '.json_encode($parameters));
        $result = \Http::withHeaders($headers)->get($url, $parameters)->json();
        \Log::info('GetResponse '.$rand_code.': - '.json_encode($result));
        return $result;
    }

    public static function postWithQueryParameters( $url, $action, $parameters, $headers= [] ) {
        $url      .= '/'.$action;
        $rand_code = rand(100000, 999999);
        \Log::info('PostWithQueryParameters '.$rand_code.': '.$url.' - '.json_encode($parameters));
        $result = \Http::withHeaders($headers)->withQueryParameters($parameters)->post($url)->json();
        \Log::info('PostResponseWithQueryParameters '.$rand_code.': - '.json_encode($result));
        return $result;
    }
}
