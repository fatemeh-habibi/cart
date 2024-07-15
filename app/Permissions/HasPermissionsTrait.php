<?php

namespace App\Permissions;

use App\Models\Permission;
use App\Models\Role;

trait HasPermissionsTrait {

   public function givePermissionsTo(... $permissions) 
   {
    $permissions = $this->getAllPermissions($permissions);
    dd($permissions);
    if($permissions === null) {
      return $this;
    }
    $this->permissions()->saveMany($permissions);
    return $this;
  }

  public function withdrawPermissionsTo( ... $permissions ) 
  {
    $permissions = $this->getAllPermissions($permissions);
    $this->permissions()->detach($permissions);
    return $this;
  }

  public function refreshPermissions( ... $permissions )
  {
    $this->permissions()->detach();
    return $this->givePermissionsTo($permissions);
  }

  public function hasPermissionTo($permission) 
  {
    return $this->hasPermissionThroughRole($permission) || $this->hasPermission($permission);
  }

  public function hasPermissionThroughRole($permission) 
  {
    foreach ($permission->roles as $role){
      if($this->roles->contains($role)) {
        return true;
      }
    }
    return false;
  }

  public function hasRole($roles) 
  {
    foreach ($roles as $role) {
      if ($this->roles->contains('id', $role)) {
        return true;
      }
    }
    return false;
  }

  public function hasRolePermission($roles,$permission,$permission_all,$permission_self_list) 
  {            
    foreach ($roles as $role) {
      if ($this->roles->contains('id', $role->id) && ($permission || $permission_all || $permission_self_list)) {
        if($permission && $permission->id){
          return (bool) $role->permissions->where('id', $permission->id)->count() ;
        }elseif($permission_all && $permission_all->id){
          return (bool) $role->permissions->where('id', $permission_all->id)->count() ;
        }else{
          return false;
        }
      }
    }
    return false;
  }

  public function roles() 
  {
    return $this->belongsToMany(Role::class,'users_roles');
  }
  public function permissions()
  {
    return $this->belongsToMany(Permission::class,'users_permissions');
  }
  
  protected function hasPermission($permission) 
  {
    return (bool) $this->permissions->where('slug', $permission->slug)->count();
  }

  protected function getAllPermissions(array $permissions) 
  {
    return Permission::whereIn('slug',$permissions)->get();
  }
}