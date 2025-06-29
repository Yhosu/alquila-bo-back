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
                $result['about_us'] = \App\Models\AboutUs::where('enabled', 1)->get();
                $result['our_team'] = \App\Models\OurTeam::where('enabled', 1)->get();
			    return $result;
            });
            return $information;
        } catch( Throwable $th) {
            throw $th;
        }
    }
}
