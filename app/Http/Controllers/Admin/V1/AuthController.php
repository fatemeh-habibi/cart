<?php

namespace App\Http\Controllers\Admin\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponseTrait;
// use App\Helpers\SmsPanelV2;
use App\Rules\ReCaptcha;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Client as OClient; 

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Post(
     *   path="/api/admin/v1/login",
     *   tags={"auth"},
     *   summary="Login",
     *   operationId="authLogin",
     *
     *   @OA\Parameter(
     *      name="username",
     *      in="query",
     *      required=true,
     *      example="admin",
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="password",
     *      in="query",
     *      required=true,
     *      example="123456",
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example=""),
     *    )
     *   ),
     *)
     **/
    /**
     * login api
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        unset($request['g_recaptcha_response']); //when OTP login => it will be coment
        if (Auth::attempt($request->toArray())) {
            $user = User::find(auth()->user()->id);
            if($user && $user->activated == 1){
                $oClient = OClient::where('password_client', 1)->first();
                $token = $user->createToken('authToken')->accessToken;
                // $token_result = $this->getTokenAndRefreshToken($oClient, request('username'), request('password'));
                $user->last_login = now();
                $user->save();
                if(!is_null($user->image)){
                    $user->image = ($user->image && file_exists(public_path().'/files/user/'.$user->image)) ? '/files/user/'.$user->image : '';
                }
                $permissions = $user->getPermissions1Attribute();
                unset($user->roles);
            }else{
                return $this->respondUnAuthorized(__('messages.Unauthorized'));
            }
    
            $user_id = $user->id;

            return $this->apiResponse(
                [
                    'success' => true,
                    'message' => __('messages.logged_in'),
                    'result' => [
                        'token' => $token,
                        'user' => array(
                            'id' => $user->id,
                            'email' => $user->email,
                            'activated' => $user->activated,
                            'username' => $user->username,
                            'first_name' => $user->first_name,
                            'last_name' => $user->last_name,
                            'mobile' => $user->mobile,
                            'image' => $user->image ?? null,
                            'created_at' => $user->created_at,
                        ),
                    ]
                ]
            );
        }else{
            return $this->respondUnAuthorized(__('messages.Unauthorized'));
        }
    }

    /**
     * @OA\Post(
     * path="/api/admin/v1/logout",
     * summary="Logout",
     * operationId="authLogout",
     * tags={"auth"},
     * security={ {"bearerAuth":{}}},
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\MediaType(
     *      mediaType="application/json",
     *    )
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated"),
     *    )
     * )
     * )
     */
    /**
     * logout api
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->delete();
        return $this->respondSuccess();
    }
    
    /**
     * @OA\Post(
     * path="/api/admin/v1/reset_password",
     * tags={"auth"},
     * summary="reset_password",
     * operationId="reset_password",
     *
     *  @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *      @OA\Property(property="mobile", type="mobile", format="mobile", example="09123703808"),
     *      @OA\Property(property="password",type="password",example="pass@1234"),
     *      @OA\Property(property="password_confirmation",type="password",example="pass@1234")
     *      )
     *   ),
     *
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    @OA\MediaType(
     *      mediaType="application/json",
     *    )
     * ),
     * )
     */
    /**
     * reset password api
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset_password(Request $request)
    {
        $request->validate([
            'mobile' => 'required|regex:/[0]{1}[0-9]{10}/',
            'password_confirmation' => 'required|string|min:6',
            'password' => 'required|string|confirmed|min:6',
            'code' => 'required|string'
        ]);
        $admin = User::where('mobile' , $request->mobile)->first();
        if($admin && $admin->activated == 1){
            if(($admin->otp_verified_at == User::MOBILE_IS_VERIFIED ) && ($admin->forgotten_password_code == $request->code)){
                $admin->password = bcrypt($request->password);
                $admin->forgotten_password_code = null;
                $admin->save();
                return $this->respondSuccess();
            }else{
                return $this->respondError(__('messages.user_not_verified'));
            }
        }else{
            return $this->respondError(__('messages.user_not_found'));
        }
    }
}

