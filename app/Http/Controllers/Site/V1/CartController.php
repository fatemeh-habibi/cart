<?php
namespace App\Http\Controllers\Site\V1;

use App\Helpers\ApiResponseTrait;
use App\Helpers\Grid;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Product;
use App\Services\FilterQueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    use ApiResponseTrait,Grid;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $default_limit;
    private $customer_id;
    private $default_lang_id;

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $customer = Auth::guard('customer')->user();

        $this->customer_id = $customer ? $customer->id : null;
        $this->default_limit = config('settings.get_limit');
        $this->default_lang_id = (new Controller)->default_lang_id();
    }

    /**
     * @OA\Get(
     ** path="/api/v1/cart/list",
     *   tags={"site_cart"},
     *   summary="site_cart_list",
     *   operationId="site_cart_list",
     *   security={ {"bearerAuthSite":{}}},
     *
     *   @OA\Parameter(
     *      name="skip",
     *      in="query",
     *      required=false,
     *      example=0,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="take",
     *      in="query",
     *      required=false,
     *      example=10,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="sort",
     *      in="query",
     *      required=false,
     *      @OA\Items(
     *          type="array",
     *          @OA\Items()
     *      ),
     *   ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *)
     **/
    public function index(): \Illuminate\Http\JsonResponse
    {
        $cart = Cart::with(['products' => function ($query) {
            $query->select(DB::raw('*, sum(quantity) as totalquantity'))->groupBy('product_id')->get();
        }]);
        
        if($this->customer_id) {
            $cart = $cart->where('customer_id', $this->customer_id)->first();
        }

        if($cart && !empty($cart)){
            $cart_products = $cart->products ? $cart->products->map(function ($item) {
                $lang = $item->product->langs ? $item->product->langs->where('lang_id' , $this->default_lang_id)->first() : null;
                $price = $item->product->cost;
                return array(
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => ($lang ? $lang->name : null),
                    'quantity' =>  (int)$item->totalquantity,
                    'discount' => (int)$item->discount,
                    'product_price' => intval($price),
                    'product_total_price' => $price * $item->totalquantity,
                );
            }) : [];

            $total_price = (count($cart_products) > 0 ? collect($cart_products)->sum('product_total_price') : 0);
            $billing = $cart->addresses ? $cart->addresses->where('address_type' , 'billing')->first() : null;
            $only = array('customer_address_id' , 'first_name' , 'last_name' , 'telephone' , 'fax' , 'company' , 'address_1' , 'address_2' , 'location_name' , 'location_info' , 'location_extra' , 'city' , 'postcode' , 'country_id' , 'state_id' , 'special_instruction');
            if($billing){
                $billing = $billing->only($only);
            }
            $shipping = $cart->addresses ? $cart->addresses->where('address_type' , 'shipping')->first() : null;
            if($shipping){
                $shipping = $shipping->only($only);
                $shipping['delivery_date'] = $cart->delivery_date;
                $shipping['delivery_id'] = $cart->delivery_id;
                $shipping['same_address'] = ($cart->same_address ? true : false);
            }
            return $this->apiResponse(
                [
                    'success' => true,
                    'result' => array(
                        'id' => $cart->id,
                        'total_products' => count($cart_products),
                        'products' => $cart_products,
                        // 'payment_method' => $cart->payment_method,
                        // 'delivery_id' => $cart->delivery_id,
                        // 'total_shipment_weight' => $cart->total_shipment_weight,
                        // 'billing' => $billing,
                        // 'shipping' => $shipping,
                        'total_price' => $total_price,
                    ),
                    'message' => 'Cart information'
                ]
            );
        }else{
            return $this->respondError(__('messages.item_not_found'));
        }
    }

    /**
     * @OA\Post(
     ** path="/api/v1/cart",
     *   tags={"site_cart"},
     *   summary="site_cart_store",
     *   operationId="site_cart_store",
     *   security={ {"bearerAuthSite":{}}},
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *      @OA\Property(property="product_id",type="integer",example=1),
     *      @OA\Property(property="quantity",type="integer",example=1),
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
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *)
     **/
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $product = Product::find($request->product_id);
        if(!$product){
            return $this->respondError(__('messages.product_not_found'));
        }
        if($product->stock <= 0 || $product->stock < $request->quantity ){
            return $this->respondError(__('messages.product_unavailable'));
        }

        $cart = Cart::query();
        if ($this->customer_id) {
            $cart = $cart->where('customer_id', $this->customer_id)->first();
        }
        if(!$cart){
            $cart_request = [];
            if ($this->customer_id) {
                $cart_request['customer_id'] = $this->customer_id;
                $cart_request['invoice_type'] = $product->invoice_type ?? 'B';
            }
            $cart = Cart::create($cart_request);
            CartProduct::create([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'cart_id' => $cart->id,
            ]);
        }
        else{
            $cart_product = CartProduct::where('product_id',$product->id)->where('cart_id',$cart->id)->first();
            if($cart_product){
                return $this->respondError(__('messages.add_dupplicate_product'));

                // $cart_product->quantity = $request->quantity + $cart_product->quantity;
                // $cart_product->save();
            }else{
                CartProduct::create([
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                    'cart_id' => $cart->id,
                ]);      
            }
        }

        $total_cart = CartProduct::with(['cart' => function($query) {
            $query->where('customer_id', $this->customer_id);
        }])->groupBy('product_id')->count();

        return $this->apiResponse(
            [
                'success' => true,
                'result' => array(
                    'total_carts' => $total_cart ?? 0,
                ),
                'message' => __('messages.request_added_success')
            ]
        );
    }

    /**
     * @OA\Put(
     *   path="/api/v1/cart/update",
     *   tags={"site_cart"},
     *   summary="cart_update",
     *   operationId="cart_update",
     *   security={ {"bearerAuthSite":{}}},
     * 
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *      @OA\Property(property="product_id",type="integer",example=1),
     *      @OA\Property(property="quantity",type="integer",example=1),
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
        $product = Product::find($request->product_id);
        if(!$product){
            return $this->respondError(__('messages.product_not_found'));
        }
        $cart = Cart::query();
        if ($this->customer_id) {
            $cart = $cart->where('customer_id', $this->customer_id)->first();
        }        
        if(!$cart){
            $cart_request = [];
            if ($this->customer_id) {
                $cart_request['customer_id'] = $this->customer_id;
                $cart_request['invoice_type'] = $product->invoice_type ?? 'B';
            }
            $cart = Cart::create($cart_request);
            CartProduct::create([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'cart_id' => $cart->id,
            ]);
        }
        else{
            if($request->quantity  === 0){
                CartProduct::where('product_id',$product->id)->where('cart_id',$cart->id)->delete();
            }else{
                $cart_product = CartProduct::where('product_id',$product->id)->where('cart_id',$cart->id)->first();
                if($cart_product){
                    $cart_product->quantity = $request->quantity;
                    $cart_product->save();
                }else{
                    CartProduct::create([
                        'product_id' => $product->id,
                        'quantity' => $request->quantity,
                        'cart_id' => $cart->id,
                    ]);    
                }
            }
        }
        
        $total_cart = CartProduct::with(['cart' => function($query) {
            $query->where('customer_id', $this->customer_id);
        }])->groupBy('product_id')->count();

        // $total_cart = CartProduct::with(['cart' => function($query) {
        //     $query->where('customer_id', $this->customer_id);
        // }])->groupBy('product_id')->select(DB::raw('sum(quantity) as totalquantity'))->first();

        return $this->apiResponse(
            [
                'success' => true,
                'result' => array(
                    'total_carts' => $total_cart ?? 0,
                    'product' => [
                        'id' => $product->id,
                        'quantity' => $request->quantity
                    ],
                ),
                'message' =>  __('messages.request_updated_success')
            ]
        );
    }

    /**
     * @OA\Delete(
     *   path="/api/v1/cart/{cart_product_id}",
     *   tags={"site_cart"},
     *   summary="site_cart_delete",
     *   operationId="site_cart_delete",
     *   security={ {"bearerAuthSite":{}}},
     *
     *   @OA\Parameter(
     *      name="cart_product_id",
     *      in="path",
     *      required=true,
     *      example=1,
     *      @OA\Schema(
     *           type="integer"
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
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *)
     **/
    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id,Request $request): \Illuminate\Http\JsonResponse
    {
        $cart_product = CartProduct::find($id);
        if(!isset($cart_product)){
            return $this->respondError(__('messages.item_not_found'));
        }
        $cart_product->delete();

        $total_cart = CartProduct::with(['cart' => function($query) {
            $query->where('customer_id', $this->customer_id);
        }])->groupBy('product_id')->count();


        return $this->apiResponse(
            [
                'success' => true,
                'result' => array(
                    'total_carts' => $total_cart ?? 0,
                ),
                'message' => __('messages.request_deleted_success')
            ]
        );
    }
}
