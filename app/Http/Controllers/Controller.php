<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponseTrait;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="API LIVE TEST",
 *      description="Test API V1",
 * )
 * @OA\SecurityScheme(
 *       type="http",
 *       description="Admin",
 *       name="Authorization",
 *       in="header",
 *       scheme="bearer",
 *       bearerFormat="Passport",
 *       securityScheme="bearerAuth",
 * )
 * @OA\SecurityScheme(
 *      type="http",
 *      description="Customer",
 *      name="AuthorizationSite",
 *      in="header",
 *      scheme="bearer",
 *      bearerFormat="Passport",
 *      securityScheme="bearerAuthSite",
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public static $allowed_tags = '<p><b><h2><h3><h4><i><a><strong><ol><ul><li><table><td><th><tr>'; 
    public function __construct()
    {
        // $this->middleware('auth:api');
    }

    protected function default_lang_id(){
        //if(auth('api')->check()){
        //    return auth('api')->user()->default_lang_id;
        //}else{
        //    return $this->respondUnAuthorized();
        //}
        // return @auth('api')->user()->default_lang_id;
        return 2;
    }

}
