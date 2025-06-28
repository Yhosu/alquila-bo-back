<?php

namespace App\Repositories;

use App\Exceptions\BadRequestException;
use App\Repositories\Interfaces\AboutusInterface;
use App\Traits\CRUDOperations;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\Interfaces\HomeInterface;
use App\Services\ApiResponseService;
use Throwable;
use Illuminate\Http\Request;
use App\Helpers\Func;

class AboutusRepository implements AboutusInterface
{
    public function getAboutus() {
        try {
            $now = date('ymd') . '-getaboutus';
            $result = [];
            $information = \Cache::store('database')->remember($now, 43200, function() use( &$result ) {
                $result['banners']         = \App\Models\Gallery::where('type', 'banner')->limit(4)->get();
			    return $result;
            });
            return $information;
        } catch( Throwable $th) {
            throw $th;
        }
    }

    public function getOurteam() {
        try {
            $now = date('ymd') . '-ourteam';
            $result = [];
            $information = \Cache::store('database')->remember($now, 43200, function() use( &$result ) {
                $team = \App\Models\Ourteam::where('enabled', 1)->get();
			    return $team;
            });
                /** Enviar */
            // \Func::sendEmail( $email, $title, $content );
            return $information;
        } catch( Throwable $th) {
            throw $th;
        }        
    }
}
