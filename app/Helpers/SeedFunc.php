<?php 

namespace App\Helpers;

use Form;
use Barryvdh\Snappy\Facades\SnappyImage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use App\Models\ApiKey;
use App\Models\ApiKeyProvider;
use App\Models\Profile;
use App\Models\CtlpPartner;

class SeedFunc {

    public static function loadData() {
        $image_folders = [
            [ 
                'id'        => 1,
                'name'      => 'category-image',
                'extension' => 'png',
            ],
        ];
        $insert_image_folders = \App\Models\ImageFolder::insert($image_folders);
        $image_sizes = [
            [ 
                'id'        => 1,
                'parent_id' => 1,
                'code'      => 'normal',
                'type'      => 'resize',
                'width'     => 800,
                'height'    => 560,
            ],
            [ 
                'id'        => 2,
                'parent_id' => 1,
                'code'      => 'original',
                'type'      => 'original',
                'width'     => null,
                'height'    => null,
            ]
        ];
        $insert_image_sizes = \App\Models\ImageSize::insert($image_sizes);
        $files = scandir(public_path('assets/seed'));
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach( $files as $file ) {
            $rp = preg_replace("#\?.*#", "", pathinfo($file, PATHINFO_EXTENSION));
            if( $rp == 'json' ) {
                $fileNameArray = explode('.', $file);
                $items = json_decode( file_get_contents( public_path('assets/seed/' . $file) ), true );
                $model = sprintf('\App\Models\%s', $fileNameArray[1]);
                $node = strtolower( $fileNameArray[1] );
                collect($items)->each(function ($item) use( $model, $node) { 
                    if( isset( $item['image'] ) ) {
                        $item['image'] = \Asset::upload_image($item['image'], strtolower($node) . '-image' );
                    }
                    $model::create($item); 
                });
            }
        }
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');        
    }

    public static function loadTestKeys() {
    }

    public static function loadPartners() {
    }
}