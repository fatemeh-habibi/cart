<?php

namespace App\Http\Controllers\Admin\V1;

use App\Helpers\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helpers\Grid;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Services\FilterQueryBuilder;
use Illuminate\Support\Facades\Auth;
use App\Models\AutomationRequestCommentUser;
use App\Models\AutomationRequestUser;
use App\Models\JobPosition;
use App\Models\Permission;
use App\Models\Purchase;
use App\Models\Role;
use App\Models\UserSection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    use ApiResponseTrait,Grid;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $default_limit;
    private $default_lang_id;
    private $type_array;
    private $type_category;
    private $user_id;

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        if(Auth::guard('api')->id()){
            $user = User::find(Auth::guard('api')->id());
            $this->user_id = $user ? $user->id : null;
        }
        $this->default_limit = config('settings.get_limit');
        $this->default_lang_id = (new Controller)->default_lang_id();
        $this->type_array = [
            "hourly_vacations" => "مرخصی ساعتی",
            "daily_vacations" => "مرخصی روزانه",
            "mission" => "ماموریت", 
            "mission_in_city" => "ماموریت درون شهری", 
            "imprest" => "مساعده", 
            "overtime" => "اضافه کاری در شرکت", 
            "sick_leave" => "استعلاجی", 
            "hourly_remote_work" => "دورکاری ساعتی", 
            "daily_remote_work" => "دورکاری روزانه", 
            "hourly_overtime_remote_work" => "اضافه کاری دورکاری"
        ];
        $this->type_category = ["request" => "درخواست","report" => "گزارش دهی", "correspondence" => "مکاتبات داخلی", "payment" => "اعلامیه پرداخت", "sent_letter" => "نامه های ارسالی", "received_letter" => "نامه های دریافتی"];
    }

    /**
     * @OA\Get(
     ** path="/api/admin/v1/user/list",
     *   tags={"user"},
     *   summary="user_list",
     *   operationId="user_list",
     *   security={ {"bearerAuth":{}}},
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
    public function index(FilterQueryBuilder $filters, Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'skip' => 'integer',
            'take' => 'integer',
            'name' => 'string|max:1000',
            'mobile' => 'string|max:1000',
            'username' => 'string|max:1000',
        ]);

        $users_grid_attach_data = array(
            'name' => 'users',
            'title' => 'users'
        );
        $users_grid_config = array();
        $alias = [];

        $keyword = $request->search;

        $users = $filters->buildQuery(User::loadRelations(), $alias);
        
        $users = $users->where(function($query) use ($keyword) {
            return $query->whereHas('roles', function ($query1) use ($keyword){
                $query1->where('name_fa', 'like', '%'.$keyword.'%');
            })
            ->orWhere('first_name', 'LIKE', '%' . $keyword . '%')
            ->orWhere('last_name', 'LIKE', '%' . $keyword . '%')
            ->orWhere('username', 'LIKE', '%' . $keyword . '%');
        });

        $name = $request->query('name');
        if(isset($name)){
            $users = $users->where(function($query) use ($name) {
                return $query->Where('first_name', 'LIKE', '%' . $name . '%')
                ->orWhere('last_name', 'LIKE', '%' . $name . '%');
            });
        }

        $username = $request->query('username');
        if(isset($username)){
            $users = $users->where('username', 'like', '%'.$username.'%');
        }

        $mobile = $request->query('mobile');
        if(isset($mobile)){
            $users = $users->where('mobile', 'like', '%'.$mobile.'%');
        }
                
        $total_users = ($keyword || $name || $username || $mobile) ? $users->count() : $users->count;
        $users = $users->get();

        $users_data = $users->map(function ($item, $key) {
            $item->permissions = $item->getPermissions2Attribute();
        });

        $data_only_items = array('id','permissions','roles','username','mobile','email','first_name','last_name','activated','created_at');
        $users_data = $users->map(function ($item, $key) use ($data_only_items) {
            return collect($item)->only($data_only_items)->toArray();
        });
        
        $users_data = $users_data->map(function ($item, $key) {
            return collect($item)
            ->toArray();
        });

        return $this->setGrid($users_data,$total_users,[],'users',$users_grid_attach_data,$users_grid_config);
    }

    /**
     * @OA\Post(
     ** path="/api/admin/v1/user",
     *   tags={"user"},
     *   summary="user_store",
     *   operationId="userStore",
     *   security={ {"bearerAuth":{}}},
     *
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *      @OA\Property(property="mobile", type="mobile", format="mobile", example="09123703808"),
     *      @OA\Property(property="email", type="email", format="email", example="user1@mail.com"),
     *      @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
     *      @OA\Property(property="password_confirmation", type="string", format="password", example="PassWord12345"),
     *      @OA\Property(property="username",type="string",example="name"),
     *      @OA\Property(property="first_name",type="string",example="name"),
     *      @OA\Property(property="last_name",type="string",example="family"),
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
     * @param UserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserRequest $request): \Illuminate\Http\JsonResponse
    {        
        $request = array_merge($request->all(), [
            'last_login' => null,
            'activated' => isset($request->activated) ? $request->activated : true ,
            'password' => $request->password ? bcrypt($request->password) : bcrypt(123456)
        ]);
        $user = User::create($request);

        if(!empty($request['roles'])){
            $roles = Role::whereIn('id',$request['roles'])->get()->pluck('id')->all();
            $user->roles()->attach($roles);
        }
        if(!empty($request['permissions'])){
            $permissions = Permission::whereIn('id',$request['permissions'])->get()->pluck('id')->all();
            $user->permissions()->attach($permissions);
        }

        $user->save();
        
        if($user) {
            $user = collect($user);
            $user = $user->put('company', 1)->put('group', 1)->put('online', true);
        }
        return $this->apiResponse(
            [
                'success' => true,
                'result' => $user,
                'message' => __('messages.user_created_success')
            ]
        );
    }

    /**
     * @OA\Put(
     *   path="/api/admin/v1/user/{user_id}",
     *   tags={"user"},
     *   summary="user_update",
     *   operationId="userUpdate",
     *   security={ {"bearerAuth":{}}},
     *
     *   @OA\Parameter(
     *      name="user_id",
     *      in="path",
     *      required=true,
     *      example=1,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *      @OA\Property(property="mobile", type="mobile", format="mobile", example="09123703808"),
     *      @OA\Property(property="email", type="email", format="email", example="user1@mail.com"),
     *      @OA\Property(property="first_name",type="string",example="name"),
     *      @OA\Property(property="last_name",type="string",example="family"),
     *      @OA\Property(property="change_password",type="boolean",example=true),
     *      @OA\Property(property="password", type="string", format="password", example="PassWord12345"),
     *      @OA\Property(property="password_confirmation", type="string", format="password", example="PassWord12345"),
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
     * @param UserRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UserRequest $request,$id): \Illuminate\Http\JsonResponse
    {
        $user = User::find($id);

        if(!User::find($id)){
            return $this->respondError(__('messages.user_not_found'));
        }

        if($user->mobile == '09123703808' || $id == 1){
            return $this->respondError(__('messages.user_do_not_have_permission'));
        }

        if($request->change_password){
            $request = array_merge($request->all(), [
                'password' => bcrypt($request->password)
            ]);
            $token = $user->tokens->each(function($token, $key) {
                $token->delete();
            });
        }else{
            $request = $request->all();
            unset($request['password'],$request['password_confirmation']);
        }

        $user_permissions = $user->getPermissions3Attribute()->all();
        if(!isset($user)){
            return $this->respondError(__('messages.user_not_found'));
        }
        
        $user->update($request);

        if(!empty($request['roles'])){
            $roles = Role::whereIn('id',$request['roles'])->get()->pluck('id')->all();
            $user->roles()->sync($roles);
        }

        if(!empty($request['permissions'])){
            $permissions = Permission::whereNotIn('id', $user_permissions)->whereIn('id',$request['permissions'])->get()->pluck('id')->all();
            $user->permissions()->sync($permissions);
        }

        return $this->apiResponse(
            [
                'success' => true,
                'result' => array(
                    'id' => $user->id,
                    'email' => $user->email,
                    'activated' => $user->activated,
                    'username' => $user->username,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'mobile' => $user->mobile,
                    'image' => $user->image && file_exists(public_path().'/files/user/'.$user->image) ? '/files/user/'.$user->image : '',
                    'created_at' => $user->created_at,
                    'permissions' => $user->permissions,
                    'roles' => $user->roles,
                ),
                'message' => __('messages.user_updated_success')
            ]
        );
    }

    /**
     * @OA\Delete(
     *   path="/api/admin/v1/user/{user_id}",
     *   tags={"user"},
     *   summary="user_delete",
     *   operationId="userDelete",
     *   security={ {"bearerAuth":{}}},
     *
     *   @OA\Parameter(
     *      name="user_id",
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
        $user = User::find($id);
        if(!isset($user)){
            return $this->respondError(__('messages.user_not_found'));
        }
        if($user->mobile == '09123703808' || $id == 1){
            return $this->respondError(__('messages.can_not_delete_admin_user'));
        }
        if(User::count() === 0){
            return $this->respondError(__('messages.can_not_delete'));
        }
        $user->delete();
        return $this->respondSuccess(__('messages.user_deleted_success'));

    }

    /**
     * @OA\Get(
     ** path="/api/admin/v1/user/profile",
     *   tags={"user_profile"},
     *   summary="profile_show",
     *   operationId="profileShow",
     *   security={ {"bearerAuth":{}}},
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile_show(): \Illuminate\Http\JsonResponse
    {
        $user = User::find(Auth::id());
        $permissions = $user->getPermissions1Attribute();
        $roles = $user->getRoles1Attribute();
        return $this->apiResponse(
            [
                'success' => true,
                'result' => array(
                    'id' => $user->id,
                    'email' => $user->email,
                    'activated' => $user->activated,
                    'username' => $user->username,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'mobile' => $user->mobile,
                    'image' => $user->image && file_exists(public_path().'/files/user/'.$user->image) ? '/files/user/'.$user->image : '',
                    'created_at' => $user->created_at,
                    'permissions' => $permissions,
                    'roles' => $roles
                ),
            ]
        );
    }

    /**
     * @OA\Put(
     *   path="/api/admin/v1/user/profile/update",
     *   tags={"user_profile"},
     *   summary="profile_update",
     *   operationId="profileUpdate",
     *   security={ {"bearerAuth":{}}},
     *
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *      @OA\Property(property="first_name",type="string",example="name"),
     *      @OA\Property(property="last_name",type="string",example="family"),
     *      @OA\Property(property="lang_id",type="integer",example="1"),
     *      @OA\Property(property="image",type="string",example="16272802467397.png")
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
     * @param UserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile_update(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'image' => 'nullable',
        ]);

        $user = User::find(Auth::id());
        $user->update([
            'image' => $request->image,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'mobile' => $request->mobile,
            'lang_id' => $request->lang_id,
            'password' => bcrypt($request->password)
        ]);

        return $this->apiResponse(
            [
                'success' => true,
                'result' => array(
                    'id' => $user->id,
                    'email' => $user->email,
                    'activated' => $user->activated,
                    'username' => $user->username,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'mobile' => $user->mobile,
                    'image' => $user->image && file_exists(public_path().'/files/user/'.$user->image) ? '/files/user/'.$user->image : '',
                    'created_at' => $user->created_at,
                    'permissions' => $user->permissions,
                    'roles' => $user->roles
                ),
                'message' => __('messages.user_updated_success')
            ]
        );
    }

    /**
     * @OA\Put(
     *   path="/api/admin/v1/user/profile/change_password",
     *   tags={"user_profile"},
     *   summary="profile_change_password",
     *   operationId="profileChangePassword",
     *   security={ {"bearerAuth":{}}},
     *
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *      @OA\Property(property="change_password",type="boolean",example=true),
     *      @OA\Property(property="password",type="password",example="pass@1234"),
     *      @OA\Property(property="password_confirmation",type="password",example="pass@1234"),
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
     * @param UserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile_change_password(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'password_confirmation' => 'required|string|min:6',
            'password' => 'required|string|confirmed|min:6',
        ]);
        $user = User::find(Auth::id());
        $user->password = bcrypt($request->password);
        $user->save();
        return $this->apiResponse(
            [
                'success' => true,
                'result' => [],
                'message' => __('messages.user_updated_success')
            ]
        );
    }

}
