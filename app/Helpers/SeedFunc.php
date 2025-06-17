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

    public static function loadProviders() {
        $files = scandir(public_path('assets/seed'));
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach( $files as $file ) {
            $rp = preg_replace("#\?.*#", "", pathinfo($file, PATHINFO_EXTENSION));
            if( $rp == 'json' ) {
                $fileNameArray = explode('.', $file);
                $items = json_decode( file_get_contents( public_path('assets/seed/' . $file) ), true );
                $model = sprintf('\App\Models\%s', $fileNameArray[1]);
                collect($items)->each(function ($item) use( $model ) { $model::create($item); });
            }
        }
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');        
    }

    public static function loadTestKeys() {
        $id = \Str::uuid();
        $id2 = \Str::uuid();
        $apiKey = ApiKey::create([
            'id'           => $id,
            'name'         => 'KEY Admin',
            'description'  => 'KEY Testing',
            'api_key'      => '17bb7ba9-d405-48f8-8227-819a27286d2a',
            'secret_key'   => '74486e5c-554d-4250-8383-763248865b10',
            'environment'  => 'testing',
            'expires_at'   => '2025-12-25 00:00:00',
            'is_admin'     => 0,
            'active'       => 1,
        ]);
        $apiKeyAdmin = ApiKey::create([
            'id'           => $id2,
            'name'         => 'Main Admin Solunes',
            'description'  => 'KEY Admin Solunes',
            'api_key'      => 'e7533aab-786e-4fec-86bf-38503d5d82ef',
            'secret_key'   => 'b19e8d75-0b31-4622-97db-7bb26d0c08a2',
            'environment'  => 'testing',
            'expires_at'   => '2025-12-25 00:00:00',
            'is_admin'     => 1,
            'active'       => 1,
        ]);
        $providers = \App\Models\Provider::get();
        foreach( $providers as $provider ) {
            ApiKeyProvider::create([
                'id'          => \Str::uuid(),
                'api_key_id'  => $id,
                'provider_id' => $provider->id
            ]);
        }
    }

    public static function loadPartners() {
        CtlpPartner::create([
            'id'        => \Str::uuid(),
            'name'      => 'Rivera Ocampo Guillermo Roberto',
            'ci_number' => '13141516',
            'code'      => 'R-0192'
        ]);
    }
}