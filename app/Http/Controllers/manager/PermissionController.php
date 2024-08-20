<?php

namespace App\Http\Controllers\manager;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public $model;
    public $modelName;
    public $routename;
    public $table;
    public $tamplate;
    public function __construct()
    {
        $this->model = new Permission();
        $this->modelName = "District";
        $this->routename = "permission.index";  // must be lowercase 
        $this->table = " permissions"; // must be lowercase 
        $this->tamplate = "content.permission";
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $role = Role::with('permissions')->find($id);
            $permission = Permission::select('id', 'name', 'group_name')->get();
            return view('content.role.permissions', compact('role', 'permission'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $role = Role::findById($id);
            if ($role) {
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');
                Artisan::call('optimize:clear');
                DB::table('role_has_permissions')->where('role_id', $id)->delete();
                $role->syncPermissions($request->permission_id);
                notify()->success("Permission Update Successfully");
                DB::commit();
                return redirect()->route('managers-role-list');
            } else {
                notify()->warning('Data not found');
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            notify()->warning($th->getMessage());
            return back();
        }
    }
}
