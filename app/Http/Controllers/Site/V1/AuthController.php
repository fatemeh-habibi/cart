<?php

namespace App\Http\Controllers\Site\V1;

use App\Helpers\ApiResponseTrait;
// use App\Helpers\SmsPanelV2;
use App\Http\Controllers\Controller;
use App\Models\CartProduct;
use App\Models\Customer;
use Illuminate\Http\Request;
use Laravel\Passport\Client as OClient; 

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Post(
     * path="/api/v1/auth/send_otp",
     * tags={"site_auth"},
     * summary="send_otp",
     * operationId="send_otp",
     *
     * @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *      @OA\Property(property="mobile", type="mobile", format="mobile", example="09123703808"),
     *      )
     * ),
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
     * send_otp api
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function send_otp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|regex:/[0]{1}[0-9]{10}/',
        ]);
        $customer = Customer::where('mobile' , $request->mobile)->first();
        if($customer){
            $code = 12345;
            // $code = random_int(00000, 99999);
            // $send = SmsPanelV2::verify($customer['mobile'],$code);

            $customer->activation_code = $code;
            $customer->activated_at = Customer::MOBILE_IS_NOT_VERIFIED;
            $customer->save();
            return $this->respondSuccess(__('messages.sms_send_success'));
        }else{
            return $this->respondError(__('messages.customer_not_found'));
        }
        return $this->respondUnAuthorized(__('messages.Unauthorized'));
    }

    /**
     * @OA\Post(
     * path="/api/v1/auth/verify_otp",
     * tags={"site_auth"},
     * summary="verify_otp",
     * operationId="verify_otp",
     *
     * @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *      @OA\Property(property="mobile", type="mobile", format="mobile", example="09123703808"),
     *      @OA\Property(property="code", type="string", format="string", example="12345")
     *      )
     * ),
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
     * verify_otp api
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify_otp(Request $request)
    {
        $request->validate([            
            'mobile' => 'required|regex:/[0]{1}[0-9]{10}/',
            'code' => 'required|string',
        ]);
        $customer = Customer::where('mobile' , $request->mobile)->first();
        if($customer){
            if($customer->activation_code == $request->code){
                $customer->activated_at = now();
                $customer->save();
                $oClient = OClient::where('password_client', 1)->first();
                $token = $customer->createToken('authToken')->accessToken;
                // $token_result = $this->getTokenAndRefreshToken($oClient, request('username'), request('password'));
        
                $customer_data = [
                        'id' => $customer->id,
                        'name' => $customer->first_name." ".$customer->last_name,
                        'expert_name' => $customer->expert ?? 'فاطمه حبیبی',
                        'expert_mobile' => '09123703808',
                    ];

                $total_cart = CartProduct::with(['cart' => function($query) use ($customer) {
                    $query->where('customer_id', $customer->id);
                }])->groupBy('product_id')->count();
                
                return $this->apiResponse(
                    [
                        'success' => true,
                        'message' => __('messages.logged_in'),
                        'result' => [
                            'token' => $token,
                            // 'token' => $token_result['access_token'],
                            // 'refresh_token' => $token_result['refresh_token'],
                            'customer' => $customer_data,
                            'total_carts' => $total_cart ?? 0,
                        ]
                    ]
                );
    
            }else{
                $customer->otp_time = (is_null($customer->otp_time) ? 1 : $customer->otp_time + 1);
                if($customer->otp_time > 3){
                    $customer->activation_code = null;
                    $customer->otp_time = null;
                    $customer->activated_at = Customer::MOBILE_IS_NOT_VERIFIED;
                }
                $customer->save();
                return $this->respondError(__('messages.code_is_invalid'));
            }
        }else{
            return $this->respondError(__('messages.customer_not_found'));
        }
        return $this->respondUnAuthorized(__('messages.Unauthorized'));
    }


    /**
     * @OA\Post(
     * path="/api/v1/auth/logout",
     * tags={"site_auth"},
     * summary="Logout",
     * operationId="authLogout",
     * security={ {"bearerAuthSite":{}}},
     *
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
        $token = $request->customer()->token();
        $token->revoke();
        return $this->respondSuccess();
    }

}
