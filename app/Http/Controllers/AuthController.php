<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Closure;
use App\Http\Requests\Account\CreateAccountRequest;
use App\Http\Requests\Account\CreateProfileRequest;
use App\Services\ApiResponseService;

class AuthController extends Controller {
    public function __construct(){
    }

    public function getAccessToken( Request $request ) {
        $customClaims = ['josue' => 'gutierrez'];
        $apiKey = $request->apiKeyModel;
        $token = $apiKey->createToken('token-' . time(), ['all'])->plainTextToken;
        return response()->json([
            'status'       => true,
            'access_token' => $token
        ], 200);
    }

    public function getInformation( Request $request ) {
        return ApiResponseService::success('Información obtenida exitósamente.', auth()->user());
    }
}
