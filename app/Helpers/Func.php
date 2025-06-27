<?php

namespace App\Helpers;

use Form;
use Barryvdh\Snappy\Facades\SnappyImage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use App\Events\PusherBroadcast;

class Func {

    public static function getModel( $node ) {
    	$className = 'App\\Models\\' . \Str::studly(\Str::singular($node));
        return $className;
    }

	public static function sendEmail( $email, $title, $content ) {
		
	}
}
