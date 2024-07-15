<?php

namespace App\Http\Controllers\Admin\V1;

use App\Helpers\ApiResponseTrait;
use App\Helpers\Grid;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductLang;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Services\FilterQueryBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\isEmpty;

class ProductController extends Controller
{
    use ApiResponseTrait,Grid;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $default_lang_id;
    private $default_sizes;

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->default_lang_id = (new Controller)->default_lang_id();
        $this->default_sizes = array(
            'product' => [1000,400]
        );
    }

    /**
     * @OA\Get(
     ** path="/api/admin/v1/product/list",
     *   tags={"product"},
     *   summary="product_list",
     *   operationId="product_list",
     *   security={ {"bearerAuth":{}}},
     * 
     *   @OA\Parameter(
     *      name="category_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="skip",
     *      in="query",
     *      required=false,
     *      example=0
     *     ,
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
    public function index(FilterQueryBuilder $filters,Request $request)
    {
        $request->validate([
            'skip' => 'integer',
            'take' => 'integer',
            'name' => 'string|max:1000',
        ]);

        $products_list_attach_data = array(
            'name' => 'products',
            'title' => 'products'
        );
        $products_list_config = array();
        $alias = [];
        $products = $filters->buildQuery(Product::loadRelations(), $alias);
        if(!empty($request->category_id)){
            $products->where('category_id' , $request->category_id);
        }
        
        $keyword = $request->search;
        $products = $products->where(function($query) use ($keyword) {
            return $query->whereHas('langs', function ($query1) use ($keyword){
                $query1->where('name', 'like', '%'.$keyword.'%');
            });
        });

        $name = $request->query('name');
        if(isset($name)){
            $products = $products->where(function($query) use ($name) {
                return $query->whereHas('langs', function ($query1) use ($name){
                    $query1->where('name', 'like', '%'.$name.'%');
                });
            });
        }

        $total_products = ($keyword || $name) ? $products->count() : $products->count;

        $products = $products->get();
        $products_data = $products->map(function ($item) {
            $lang = collect($item->langs)->where('lang_id' , $this->default_lang_id)->first();                
            $img = collect($item->images)->where('cover' , 1)->first();
            if(isset($img)){
                $image =  url('images/product/'.str_replace('.' , '_t100.' , $img->image));
            }
            return [
                'id'=> $item->id,
                'category_id'=> $item->category_id,
                'category_name'=> ($item->category ? @collect($item->category->langs)->where('lang_id' , $this->default_lang_id)->first()->title : null),
                'name'=> $lang ? $lang->name : '',
                'quantity'=> $item->quantity,
                'cost'=> $item->cost,
                'viewed'=> $item->viewed,
                'activated'=> $item->activated,
                'created_at'=> verta($item->created_at)->format('H:i Y/m/d'),
                'created_by'=> ($item->created_user ? $item->created_user->first_name.' '.$item->created_user->last_name : ''),
                'status_id' => $item->status_id,
            ];
        });
        return $this->setGrid($products_data,$total_products,[],'products',$products_list_attach_data,$products_list_config);
    }

    /**
     * @OA\Post(
     ** path="/api/admin/v1/product",
     *   tags={"product"},
     *   summary="product_store",
     *   operationId="productStore",
     *   security={ {"bearerAuth":{}}},
     *
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *      @OA\Property(property="name", type="string", format="string", example="name"),
     *      @OA\Property(property="category_id", type="integer", example=1),
     *      @OA\Property(property="quantity", type="integer", example=1),
     *      @OA\Property(property="cost", type="integer", example=1),
     *      @OA\Property(property="activated", type="boolean", example=true),
     *     )
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
     * @param ProductRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProductRequest $request)
    {
        $request = $request->all();
        $request['status_id'] = 1;
        $request['activated'] = 1;
        $request['stock'] = $request['quantity'] ?? 0;

        // DB::beginTransaction();
        // try
        // {
            $product = Product::create($request);
            $request_product = array_merge($request, [
                'product_id' => $product->id,
                'lang_id' => $this->default_lang_id,
            ]);
            ProductLang::create($request_product);

            // DB::commit();
            return $this->apiResponse(
                [
                    'success' => true,
                    'result' => array(
                        'id'=> $product->id,
                    ),
                    'message' => __('messages.product_created_success')
                ]
            );
        // }catch (\Exception $e){
        //     DB::rollBack();
        //     return $this->respondError(__('messages.product_created_unsuccess'));
        // }
    }

    /**
     * @OA\Put(
     ** path="/api/admin/v1/product/{product_id}",
     *   tags={"product"},
     *   summary="product_update",
     *   operationId="product_update",
     *   security={ {"bearerAuth":{}}},
     *
     *   @OA\Parameter(
     *      name="product_id",
     *      in="path",
     *      required=true,
     *      example=1,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *      @OA\Property(property="name", type="string", format="string", example="name"),
     *      @OA\Property(property="category_id", type="integer", example=1),
     *      @OA\Property(property="quantity", type="integer", example=1),
     *      @OA\Property(property="cost", type="integer", example=1),
     *      @OA\Property(property="activated", type="boolean", example=true),
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
     * @param ProductRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ProductRequest $request,$id)
    {
        $product = Product::find($id);
        if($product){
            $request = array_merge($request->all(), [
                'lang_id' => $this->default_lang_id
            ]);
            DB::beginTransaction();
            try
            {
                $product->update($request);

                ProductLang::updateOrCreate(['product_id' =>  $id, 'lang_id' => $this->default_lang_id , 'name' => $request['name']]);
                DB::commit();
                        
                return $this->apiResponse(
                    [
                        'success' => true,
                        'result' => array(
                            'id'=> $product->id,
                            'name'=> ($product->langs ? $product->langs[0]->name : ''),
                            'quantity'=> $product->quantity,
                            'cost'=> $product->cost,                            
                            'category_id'=> $product->category_id,
                            'category_name'=> ($product->category ? @collect($product->category->langs)->where('lang_id' , $this->default_lang_id)->first()->title : null),
                            'activated'=> $product->activated,
                        ),
                        'message' => __('messages.product_updated_success')
                    ]
                );    
            }catch (\Exception $e){
                DB::rollBack();
                // return $e;
                return $this->respondError(__('messages.product_updated_unsuccess'));
            }
        }else{
            return $this->respondError(__('messages.product_not_found'));
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/admin/v1/product/{product_id}",
     *   tags={"product"},
     *   summary="product_delete",
     *   operationId="productDelete",
     *   security={ {"bearerAuth":{}}},
     * 
     *   @OA\Parameter(
     *      name="product_id",
     *      in="path",
     *      required=true,
     *      example=1,
     *      @OA\Schema(
     *           type="integer"
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
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id,Request $request): \Illuminate\Http\JsonResponse
    {
        if(Product::count() === 0){
            return $this->respondError(__('messages.can_not_delete'));
        }
        $product = Product::find($id);
        if(!isset($product)){
            return $this->respondError(__('messages.product_not_found'));
        }
        $product->delete();
        return $this->respondSuccess( __('messages.product_deleted_success'));
    }

    /**
     * @OA\Patch(
     *   path="/api/admin/v1/product/{product_id}",
     *   tags={"product"},
     *   summary="product_restore",
     *   operationId="productRestore",
     *   security={ {"bearerAuth":{}}},
     * 
     *   @OA\Parameter(
     *      name="product_id",
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
    public function restore($id,Request $request): \Illuminate\Http\JsonResponse
    {
        $product = Product::withTrashed()->find($id)->restore();
        return $this->respondSuccess();
    }
}
