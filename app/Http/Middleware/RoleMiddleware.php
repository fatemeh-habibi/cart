<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\ApiResponseTrait;
use App\Models\Permission;

class RoleMiddleware
{
    use ApiResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, $role = null, $permission = null)
    {        
        $prefix = $request->route()->getPrefix();
        $route_name = $request->route()->getName();

        $permission_name = str_replace("store", "create", $route_name);
        $permission_name = str_replace("edit", "update", $permission_name);
        $prefix = substr($prefix, 13); //'api/admin/v1/'
        $permission_all = $prefix.'_all';
        $permission_self_list = $prefix.'_self_list';

        $permission = Permission::with('roles')->where('slug', $permission_name)->first();
        $permission_all = Permission::with('roles')->where('slug', $permission_all)->first();
        $permission_self_list = Permission::with('roles')->where('slug', $permission_self_list)->first();
        $roles = $request->user()->roles;

        if(!$permission && !$permission_all){
            return $this->respondError(__('messages.user_do_not_have_permission'));
        }
        if($roles && $request->user()->hasRolePermission($roles,$permission,$permission_all,$permission_self_list)) 
        {
            return $next($request);
        }
        if(($permission && $request->user()->hasPermissionTo($permission)) || ($permission_all && $request->user()->hasPermissionTo($permission_all)) || ($permission_self_list && $request->user()->hasPermissionTo($permission_self_list))) 
        {
            return $next($request);
        }

        return $this->respondError(__('messages.user_do_not_have_permission'));
    }
}
