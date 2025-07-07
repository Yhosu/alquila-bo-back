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
        $tempReferences = [];
        $image_folders = [
            [
                'id'        => 1,
                'name'      => 'category-image',
                'extension' => 'png',
            ],
        ];
        \App\Models\ImageFolder::insert($image_folders);
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
                //$model = sprintf('\App\Models\%s', $fileNameArray[1]);
                $modelName = $fileNameArray[1];
                $modelClass = sprintf('\App\Models\%s', $modelName);
                //$node = strtolower( $fileNameArray[1] );
                collect($items)->each(function ($item) use($modelClass, $modelName, &$tempReferences) {
                    if( isset( $item['image'] ) ) {
                        $item['image'] = \Asset::upload_image($item['image'], strtolower($modelName) . '-image' );
                    }
                    $tempId = $item['temp_id'] ?? null;
                    unset($item['temp_id']);
                    foreach ($item as $key => $value) {
                        if (str_ends_with($key, '_temp_id') && isset($tempReferences[$value])) {
                            $realField = str_replace('_temp_id', '_id', $key);
                            $item[$realField] = $tempReferences[$value];
                            unset($item[$key]);
                        }
                    }

                    $model = $modelClass::create($item);

                    if ($model->getKey() && $model->exists) {
                        try {
                            $model->refresh();
                        } catch (\Exception $e) {
                            \Log::warning('No se pudo hacer refresh del modelo', [
                                'model' => $modelClass,
                                'id' => $model->getKey(),
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                    if ($tempId) {
                        $tempReferences[$tempId] = $model->id;
                    }
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
