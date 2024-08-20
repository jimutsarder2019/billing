@extends('layouts/layoutMaster')
@section('title', 'manager')

@section('content')
<div class="card">
  <div class="card-header">
    <h5 class="card-title">Managers List</h5>
    <div class="row">
      <div class="col-sm-12 col-md-10">
        <form action='{{route("managers-manager-list")}}'>
          <div class="row">
            <div class="col-md-1">
              <select class="form-control" name="item" onchange="this.form.submit()" id="">
                <option @if ($managers->count() == '10') selected @endif value="10">10</option>
                <option @if ($managers->count() == '50') selected @endif value="50">50</option>
                <option @if ($managers->count() == '100') selected @endif value="100">100</option>
                <option @if ($managers->count() == $managers->total()) selected @endif value="{{$managers->total()}}">All</option>
              </select>
            </div>
            <div class="col-md-11">
              <div class="input-group">
                <select required id="manager_type" name="type" class="form-control" onchange="this.form.submit()">
                  <option value="">Please Select One</option>
                  <option @if ($type=='franchise' ) selected @endif value="franchise">Franchise</option>
                  <option @if ($type=='app_manager' ) selected @endif value="app_manager">App Manager</option>
                </select>
                <a class="btn btn-xs btn-warning" href="{{route('managers-manager-list')}}"> Reset</a>
              </div>
            </div>
          </div>
        </form>
      </div>
      @can('Managers Add')
      <div class="col-sm-12 col-md-2">
        <a href="{{route('manager.create')}}" class="btn btn-primary">Add Manager</a>
      </div>
      @endcan
    </div>
  </div>

  <div class="card-datatable table-responsive">
    <table class="datatables-users table">
      <thead>
        <tr>
          <th>SL No.</th>
          <th><i class="fa fa-cogs"></i></th>
          <th>Type</th>
          <th>Name</th>
          <th>Total Customers</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Zone</th>
          <th>Mikrotik</th>
          <th>Roles</th>
          <th>panel balance</th>
          <th>Wallet</th>
        </tr>
      </thead>
      <tbody>
        @foreach($managers as $manager)
        @if(!auth()->user()->hasRole(SUPER_ADMIN_ROLE) && auth()->user()->id == $manager->id)
        <tr>
          <td>{{$manager->id}}</td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
              <div class="dropdown-menu">
                @can('Managers View Profile')
                <a href="{{route('managerProfile', $manager->id)}}" title="View Profile" class="dropdown-item cursor-pointer"><i class="bi bi-pencil-square"></i> View Profile </a>
                @endcan
                @can('Managers Edit')
                <div title="Edit" class="dropdown-item cursor-pointer" data-bs-toggle="modal" data-bs-target="#editManagerModal_{{$manager->id}}"><i class="bi bi-pencil-square"></i> Edit </div>
                @endcan
                @can('Managers Assign Role')
                <div title="Permission" class="dropdown-item cursor-pointer" data-bs-toggle="modal" data-bs-target="#addRoleToManagerModal_{{$manager->id}}"><i class="bi bi-cash-coin"></i>Assign Role</div>
                <a class="dropdown-item text-info" href="{{route('olt.assignManager', ['id'=>$item->id, 'assign_from'=>'manager'])}}">
                  <i class="bi bi-cash-coin"></i> Assign OLT
                </a>
                @endcan
                @can('Managers Add Custom Balance')
                @if(auth()->user()->type !== FRANCHISE_MANAGER && $manager->type == FRANCHISE_MANAGER)
                <div title="Balance" class="dropdown-item cursor-pointer" data-bs-toggle="modal" data-bs-target="#managet_addbalance_{{$manager->id}}"><i class="bi bi-wallet2"></i> Update Balance</div>
                @endif
                @endcan
                @can('Managers Balance Transfer')
                @if(auth()->user()->id == $manager->id)
                <a href="{{route('manager-balance-transfer.get', $manager->id)}}" title="Balance" class="dropdown-item cursor-pointer"><i class="bi bi-wallet2"></i> Transfer Balance</a>
                @endif
                @endcan
                @can('managers_assign_olt')
                <a class="dropdown-item text-info" href="{{route('olt.assignManager', ['id'=>$manager->id, 'assign_from'=>'manager'])}}">
                  <i class="bi bi-cash-coin"></i> Assign OLT
                </a>
                @endcan
                @can('Managers Delete')
                <!-- <div onclick='openConfirmation("{{ url("manager-delete/$manager->id") }}")' title="Delete" class="dropdown-item cursor-pointer bg-danger"><i class="bi bi-trash"></i>Delete</div> -->
                @endcan
              </div>
          </td>
          <td>{{$manager->type}}</td>
          <td>{{$manager->name}}</td>
          <td>{{count($manager->customers)}}</td>
          <td>{{$manager->email}}</td>
          <td>{{$manager->phone}}</td>
          <td>{{$manager->zone ? $manager->zone->name : $manager->zones}}</td>
          <td>{{$manager->mikrotik ? $manager->mikrotik->identity :''}}</td>
          <td>
            @foreach($manager->roles as $r)
            {{ $r->name}}
            @endforeach
          </td>
          <td>{{$manager->type == FRANCHISE_MANAGER ? $manager->panel_balance : '0'}} TK</td>
          <td>{{$manager->wallet}} TK</td>
        </tr>
        @elseif(auth()->user()->hasRole(SUPER_ADMIN_ROLE))
        <tr>
          <td>{{$manager->id}}</td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
              <div class="dropdown-menu">
                @can('Managers View Profile')
                <a href="{{route('managerProfile', $manager->id)}}" title="View Profile" class="dropdown-item cursor-pointer"><i class="bi bi-pencil-square"></i> View Profile </a>
                @endcan
                @can('Managers Edit')
                <a href="{{route('editManager', $manager->id)}}" title="Edit" class="dropdown-item cursor-pointer"><i class="bi bi-pencil-square"></i> Edit </a>
                @endcan
                @can('Managers Assign Role')
                <div title="Permission" class="dropdown-item cursor-pointer" data-bs-toggle="modal" data-bs-target="#addRoleToManagerModal_{{$manager->id}}"><i class="bi bi-cash-coin"></i> Assign Role</div>
                @endcan
                @if($manager->type == FRANCHISE_MANAGER)
                @can('Managers Add Custom Balance')
                <div title="Balance" class="dropdown-item cursor-pointer" data-bs-toggle="modal" data-bs-target="#managet_addbalance_{{$manager->id}}"><i class="bi bi-wallet2"></i> Add panel Balance</div>
                @endcan
                @endif
                @can('Managers Balance Transfer')
                @if(auth()->user()->id == $manager->id)
                <a href="{{route('manager-balance-transfer.get', $manager->id)}}" title="Balance" class="dropdown-item cursor-pointer"><i class="bi bi-wallet2"></i> Transfer Balance</a>
                @endif
                @endcan
                @can('Managers Delete')
                <!-- href="{{route('manager_delete', $manager->id)}}" -->
                <?php $url = "manager-delete/$manager->id" ?>
                <div onclick="openConfirmation('{{ url($url) }}','get')" title="Delete" class="dropdown-item cursor-pointer bg-danger"><i class="bi bi-trash"></i>Delete</div>
                @endcan
              </div>
          </td>
          <td>{{$manager->type}}</td>
          <td>{{$manager->name}}</td>
          <td>{{count($manager->customers)}}</td>
          <td>{{$manager->email}}</td>
          <td>{{$manager->phone}}</td>
          <td>
            @if ($manager->zone)
            <span class="badge bg-label-success">{{$manager->zone->name}}</span>
            @else
            @foreach ($manager->assingZones as $key => $item)
            <span class="badge bg-label-success mt-1">{{$item->zone->name}}</span>
            @endforeach
            @endif
          </td>
          <td>{{$manager->mikrotik ? $manager->mikrotik->identity :''}}</td>
          <td>
            @foreach($manager->roles as $r)
            {{ $r->name}}
            @endforeach
          </td>
          <td>{{$manager->type == FRANCHISE_MANAGER ? $manager->panel_balance : '0'}} TK</td>
          <td>{{$manager->wallet}} TK</td>
        </tr>
        @endif
        @include('content/manager/manager-assigned-olt-modal', ['roles' => $roles])
        @include('content/manager/add-role-to-manager-modal', ['roles' => $roles])
        @include('content/manager/manager-add-balance-modal', ['manager' => $manager])
        @endforeach
      </tbody>
      @include('components/delete-model')
    </table>
    <div class="ml-4 data_table_pagination">{{ $managers->links() }}</div>

  </div>
</div>
@endsection