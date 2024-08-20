<?php

namespace App\Http\Controllers\package;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mikrotik;
use App\Models\IPpool;
use App\Models\Manager;
use App\Models\ManagerAssignPackage;
use App\Models\Package as PackageModel;

class Package extends Controller
{
    public function addPackage()
    {
        if (!auth()->user()->can('Add Package')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $mikrotiks = Mikrotik::all();
        $ips = IPpool::all();
        return view('content.package.add-package', compact('mikrotiks', 'ips'));
    }

    public function storePackage(Request $request)
    {
        if (!auth()->user()->can('Add Package')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'mikrotik_id' => 'required',
            'name' => 'required',
            'synonym' => 'required',
            'ip_pool' => 'required',
            'price' => 'required',
            'franchise_price' => 'required',
            'duration' => 'required',
            'period' => 'required',
            'status' => 'required'
        ]);

        if ($request->status == 'on') {
            $status = true;
        } else {
            $status = false;
        }

        $package = PackageModel::create([
            'name' => $request->name,
            'type' => 'PPPOE',
            'synonym' => $request->synonym,
            'mikrotik_id' => $request->mikrotik_id,
            'pool_id' => $request->ip_pool,
            'price' => $request->price,
            'franchise_price' => $request->franchise_price,
            'status' => $status,
            'bandwidth' => $request->bandwidth
        ]);

        $expireafter = 0;
        switch ($request->duration) {
            case 'Minutes':
                $expireafter = $request->period * 60;
                break;
            case 'Hours':
                $expireafter = $request->period * 60 * 60;
                break;
            case 'Days':
                $expireafter = $request->period * 60 * 60 * 24;
                break;
            case 'Weeks':
                $expireafter = $request->period * 60 * 60 * 24 * 7;
                break;
            case 'Months':
                $expireafter = $request->period * 60 * 60 * 24 * 30;
                break;
            default:
                $expireafter = 0;
        }

        $package->validdays = $expireafter;
        $package->durationmeasure = $request->period . ' ' . $request->duration;

        if ($request->fixed_expiry != null) {
            $package->fixed_expire_time_status = true;
            $package->fixed_expire_time = $request->fixed_expiry_day;
        } else {
            $package->fixed_expire_time_status = false;
            $package->fixed_expire_time = null;
        }
        $package->save();
        return back();
    }

    public function viewPackage(Request $request)
    {
        if (!auth()->user()->can('View Package')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $packages = PackageModel::when(auth()->user(), function ($q) {
            if (auth()->user()->type == FRANCHISE_MANAGER) {
                $manager_package = ManagerAssignPackage::where('manager_id', auth()->user()->id)->get()->pluck('package_id');
                return $q->whereIn('id', $manager_package);
            }
        })
            ->when($request->mikrotik, function ($q) use ($request) {
                return $q->where('mikrotik_id', $request->mikrotik);
            })
            ->when($request->search_query, function ($q) use ($request) {
                $searchQuery = '%' . $request->search_query . '%';
                return $q->where('name', 'LIKE', '%' . $searchQuery . '%')
                    ->orWhere('synonym', 'LIKE', $searchQuery);
            })
            ->latest()->paginate($request->item ?? 10);
        return view('content.package.view-package', compact('packages'));
    }

    public function editPackage($id)
    {
        if (!auth()->user()->can('Packages Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $package = PackageModel::where('id', $id)->first();
        if ($package->durationmeasure != null) {
            $duration_measure = explode(' ', $package->durationmeasure);
        } else {
            $duration_measure = null;
        }
        $mikrotiks = Mikrotik::when(auth()->user(), function ($q) {
            if (auth()->user()->type == FRANCHISE_MANAGER) {
                return $q->where('id', auth()->user()->mikrotik_id);
            }
        })->get();
        $ips = IPpool::all();
        return view('content.package.edit-package', compact('package', 'duration_measure', 'mikrotiks', 'ips'));
    }

    public function updatePackage(Request $request, $id)
    {
        if (!auth()->user()->can('Packages Edit')) return error_message('You Have No Access Permission', 'Unauthorize permissons', 403);
        $request->validate([
            'mikrotik_id' => 'required',
            'name' => 'required',
            'synonym' => 'required',
            'ip_pool' => 'required',
            'price' => 'required',
            'franchise_price' => 'required',
            'duration' => 'required',
            'period' => 'required',
            'status' => 'required'
        ]);

        if ($request->status == 'on') {
            $status = true;
        } else {
            $status = false;
        }
        if ($request->fixed_expiry == 'on') {
            $request->validate([
                'fixed_expiry_day' => 'required'
            ]);
        }

        $package = PackageModel::find($id);
        $package->name = $request->name;
        $package->synonym = $request->synonym;
        $package->mikrotik_id = $request->mikrotik_id;
        $package->pool_id = $request->ip_pool;
        $package->price = $request->price;
        $package->franchise_price = $request->franchise_price;
        $package->status = $status;
        $package->bandwidth = $request->bandwidth;

        $expireafter = 0;
        switch ($request->duration) {
            case 'Minutes':
                $expireafter = $request->period * 60;
                break;
            case 'Hours':
                $expireafter = $request->period * 60 * 60;
                break;
            case 'Days':
                $expireafter = $request->period * 60 * 60 * 24;
                break;
            case 'Weeks':
                $expireafter = $request->period * 60 * 60 * 24 * 7;
                break;
            case 'Months':
                $expireafter = $request->period * 60 * 60 * 24 * 30;
                break;
            default:
                $expireafter = 0;
        }

        $package->validdays = $expireafter;
        $package->durationmeasure = $request->period . ' ' . $request->duration;
        if ($request->fixed_expiry != null) {
            $package->fixed_expire_time_status = true;
            $package->fixed_expire_time = $request->fixed_expiry_day;
        } else {
            $package->fixed_expire_time_status = false;
            $package->fixed_expire_time = null;
        }
        $package->save();
        notify()->success('Update successfully');
        return redirect()->route('packages-view-package');
    }


    //get package mikrotik id 
    public function mikroTikPackage($id)
    {
        try {
            $packages = PackageModel::select('id', 'name', 'synonym', 'mikrotik_id')->where('mikrotik_id', $id)->get();
            return response()->json(['package' => $packages]);
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }
    //package delete 
    public function package_delete($id)
    {
        try {
            $package = PackageModel::with('customers', 'managerAssignPackage')->find($id);
            if (!$package) return abort(404);
            if ($package->customers->count() > 0 || $package->managerAssignPackage->count() > 0) {
                notify()->warning('Please Delete Relevant Manager or User');
                return back();
            }
            $package->delete();
            notify()->success('Delete successfully');
            return back();
        } catch (\Throwable $th) {
            notify()->warning($th->getMessage());
            return back();
        }
    }
}
