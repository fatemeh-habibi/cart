<?php

namespace App\Http\Controllers\Site\V1;

use App\Helpers\ApiResponseTrait;
use App\Helpers\Grid;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductLang;
use Illuminate\Http\Request;
use App\Services\FilterQueryBuilder as FilterQueryBuilder;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    use ApiResponseTrait,Grid;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $default_limit;
    private $default_lang_id;
    private $customer_id;

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
     ** path="/api/v1/product/list",
     *   tags={"site_product"},
     *   summary="site_product_list",
     *   operationId="site_product_list",
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
    public function index(FilterQueryBuilder $filters,Request $request)
    {
        $products_grid_attach_data = array(
            'name' => 'products',
            'title' => 'products'
        );

        $keyword = $request->search;

        $products_grid_config = array();
        $alias = [];

        $products = $filters->buildQuery(Product::loadRelations(), $alias);
        $products = $products->where('activated' , 1);
        $products = $products->with(['carts' => function($query) {
            $query->with(['cart' => function($query) {
                $query->where('customer_id', $this->customer_id);
            }]);
        }]);
        $products = $products->where(function($query) use ($keyword) {
            return $query->whereHas('langs', function ($query1) use ($keyword){
                $query1->where('lang_id' , $this->default_lang_id)
                ->where('name', 'like', '%'.$keyword.'%');
                // ->where('description', 'like', '%'.$keyword.'%');
            })
            ->orWhereHas('category',function($category_query) use ($keyword)  {
                $category_query->whereHas('langs', function ($category_lang_query) use ($keyword){
                    $category_lang_query->where('title', 'like', '%'.$keyword.'%')
                    ->where('url', 'like', '%'.$keyword.'%');
                });
            });
        });
        
        $total_products = $products->count;
        $products = $products->get();

        $products_data = $products->map(function($item){
                $lang = collect($item->langs)->where('lang_id' , $this->default_lang_id)->first();
                $category_lang = collect($item->category->langs)->where('lang_id' , $this->default_lang_id)->first();
                $image = collect($item->images)->where('cover' , 1)->first();
                return [
                    'id' => $item->id,
                    'image' => ($image ? url('images/product/'.str_replace('.' , '_t100.' , $image->image)) : ''),
                    'name' => ($lang ? $lang->name : ''),
                    'category_name' => $category_lang ? $category_lang->title : '',
                    'price' => floatval($item->price),
                    'cart_quantity'=> count($item->carts) > 0 ? $item->carts->first()->quantity : 0,
                    'url' => ($lang ? $lang->url : '')
                ];
        });
        return $this->setGrid($products_data,$total_products,[],'products',$products_grid_attach_data,$products_grid_config);
    }

}
