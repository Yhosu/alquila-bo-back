<?php

namespace App\Repositories;

use App\Exceptions\BadRequestException;
use App\Traits\CRUDOperations;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\Interfaces\NodeInterface;
use App\Services\ApiResponseService;
use Throwable;
use Illuminate\Http\Request;
use App\Helpers\Func;

class NodeRepository implements NodeInterface
{
    public function getListOfNode( string $node, bool $paginate, array $filters ) {
        try {
            $availableNodes = config('nodes.available_nodes');
            if( !in_array( $node, $availableNodes ) ) return [];
            $noRestricts = [];
		    $className = Func::getModel( $node );
		    $class 	   = with(new $className);
		    $table     = $class->getTable();
		    $items     = $className::query();
            foreach( $filters as $key => $value ) {
                $key = str_replace('_from', '', $key);
                if( !\Schema::hasColumn($table, trim( strtolower( $key ) ) ) && !in_array($key, $noRestricts) ) continue;
                if( empty( $value ) ) continue;
                $type = \Str::contains( $key, 'xt_') ? 'extra' : getTypeField($table, $key);
                $items = $items->when( \Str::contains( $key, 'xt_with_image'), function( $q ) use( $key, $value ) {
				        $boolImage = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    				    return $boolImage ? $q->whereNotNull('image') : $q->whereNull('image');
				    })->when( in_array( $type, ['int', 'bigint', 'tinyint', 'enum']), function( $q ) use( $key, $value ) {
                        return $q->where( $key, $value );
                    })->when( in_array($type, ['text', 'string', 'varchar'] ), function( $q ) use( $key, $value ) {
				    	$isUuid = preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $value);
				    	if( $isUuid ) return $q->where( $key, $value );
                        $query = "TRIM( REPLACE( REPLACE(  REPLACE( REPLACE( REPLACE( LOWER( `" . $key . "`), 'á', 'a' ), 'e', 'e' ), 'i', 'i' ), 'ó', 'o' ), 'u', 'u' ) ) LIKE '%" . clean4search( $value ) ."%'";
				    	return $q->whereRaw($query, []);
                    })->when( in_array( $type, ['datetime', 'date', 'timestamp'] ) && isset($filters[$key.'_from']) && isset( $filters[$key.'_to'] ) , function( $q ) use( $key, $value, $filters ) {
                        return $q->whereDate($key, '>=',$filters[$key.'_from'] . ' 00:00:00')->whereDate($key, '<=', $filters[$key.'_to'] . ' 23:59:00');
                    });
            }
            $items = $paginate
                ? $items->orderBy('created_at', 'DESC')->paginate( config('nodes.paginate') )->appends( $filters ) 
                : $items->orderBy('created_at', 'DESC')->get();
            return $items;
        } catch( Throwable $th) {
            throw $th;
        }
    }
}
