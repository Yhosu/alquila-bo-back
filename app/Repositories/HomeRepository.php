<?php

namespace App\Repositories;

use App\Exceptions\BadRequestException;
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

class HomeRepository implements HomeInterface
{
    public function getHome() {
        try {
            $now = date('ymd');
            $result = [];
            $information = \Cache::store('database')->remember($now, 43200, function() use( &$result ) {
                $result['banners']         = \App\Models\Gallery::where('type', 'banner')->limit(4)->get();
                $result['top_products']    = \App\Models\Product::where('enabled', 1)->where('top', 1)->orderBy('order', 'ASC')->get();
                $result['categories']      = \App\Models\Category::where('enabled')->withWhereHas('products', function($q) { $q->with(['product_characteristics', 'product_filters'])->whereHas('company', function($qq){ $qq->where('enabled', 1); }); })->get();
                $result['characteristics'] = \App\Models\Characteristic::where('enabled', 1)->get();
                $result['reviews']         = \App\Models\Review::where('enabled', 1)->get();
			    return $result;
            });
            return $information;
        } catch( Throwable $th) {
            throw $th;
        }
    }
}
