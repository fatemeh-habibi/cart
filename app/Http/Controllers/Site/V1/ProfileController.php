<?php

namespace App\Http\Controllers\Site\V1;

use App\Helpers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Get(
     * path="/api/v1/profile",
     * tags={"site_profile"},
     * summary="profile",
     * operationId="profile",
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
     *    description="Returns when customer is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated"),
     *    )
     * )
     * )
     */
    /**
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        return $this->apiResponse([
            'success' => true,
            'result' => [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'mobile' => $customer->mobile,
                'telephone' => $customer->telephone,
                'image' => isset($customer->image) && file_exists(public_path().'/files/customer/'.$customer->image) ? '/files/customer/'.$customer->image : '',
                'fax' => $customer->fax,
            ]
        ]);
    }

    /**
     * @OA\Put(
     *   path="/api/v1/profile/update",
     *   tags={"site_profile"},
     *   summary="profile_update",
     *   operationId="profile_update",
     *   security={ {"bearerAuthSite":{}}},
     * 
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *      @OA\Property(property="email", type="email", format="email", example="customer@gmail.com"),
     *      @OA\Property(property="first_name",type="string",example="name"),
     *      @OA\Property(property="last_name",type="string",example="family"),
     *      @OA\Property(property="fax",type="string",example="25215425"),
     *      @OA\Property(property="gender",type="integer",example=0),
     *      @OA\Property(property="image",type="string",example="16272802467397.png"),
     *      @OA\Property(property="telephone",type="string",example="25215425"),
     *      @OA\Property(property="mobile",type="string",example="058454147"),
     *      )
     *   ),
     *
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *    response=401,
     *    description="Returns when customer is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example=""),
     *    )
     *   ),
     *)
     **/
    /**
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $id = Auth::guard('customer')->id();
        $customer = Customer::find($id);

        $request->validate([
            'email' => 'nullable|email|unique:customers,email,'.$customer->id.',id',
            'mobile' => 'nullable|string|unique:customers,mobile,'.$customer->id.',id',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'image' => 'nullable',
        ]);

        $customer->first_name = $request->first_name;
        $customer->last_name = $request->last_name;
        $customer->fax = $request->fax;
        $customer->email = $request->email;
        $customer->telephone = $request->telephone;
        $customer->mobile = $request->mobile;
        $customer->gender = $request->gender;
        $customer->save();
        $customer_image = (isset($customer->image) && file_exists(public_path().'/files/customer/'.$customer->image)) ? '/files/customer/'.$customer->image : '';
        $customer_data = [
            'id' => $customer->id,
            'name' => $customer->first_name." ".$customer->last_name,
            'expert_name' => $customer->expert ?? 'فاطمه حبیبی',
            'expert_mobile' => '09123703808',
            'image' => $customer_image,
            'group_name' => $customer->customer_group ? $customer->customer_group->name_fa : '',
        ];

        return $this->apiResponse([
            'success' => true,
            'result' => [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'mobile' => $customer->mobile,
                'telephone' => $customer->telephone,
                'image' => $customer_image,
                'fax' => $customer->fax,
                'customer' => $customer_data,
            ],
            'message' => __('messages.customer_updated_success')
        ]);
    }
}
