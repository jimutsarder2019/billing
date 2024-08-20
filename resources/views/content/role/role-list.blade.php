@extends('layouts/layoutMaster')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />

@section('title') Roles @endsection
@section('content')

<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-3">Roles</h5>
    @can('Role Add')
    <button class="btn btn-primary me-sm-3 me-1" data-bs-toggle="modal" data-bs-target="#addRoleModal">Add Role</button>
    @include('content/role/add-role-modal')
    @endcan
  </div>
  <!-- can('Role View') -->
  <div class="card-datatable table-responsive">
    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th>SL No.</th>
          <th>Name</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($roles as $role)
        <tr>
          <td>{{$role->id}}</td>
          <td>{{$role->name}}</td>
          <td>
            <div class="d-flex">
              @can('Role Edit')
              <div class="cursor-pointer btn btn-xs btn-primary" data-bs-toggle="modal" data-bs-target="#editRoleModal_{{$role->id}}"><i class="bi bi-pencil-square"></i> </div>
              @include('content/role/edit-role-modal', ['role' => $role])
              @endcan
              @can('Role Assign Permission')
              <div><a href="{{route('permission.show', $role->id)}}" class="btn btn-xs btn-success mx-2"> <i class="bi bi-cash-coin"></i> Assign Permission</a></div>
              @endcan
              @can('Role Delete')
              <!-- <a onclick="return confirm('Are You Sure To Delete')" href="{{route('role_delete', $role->id)}}" class="cursor-pointer btn btn-xs btn-danger"><i class="bi bi-trash"></i></a> -->
              @php $url = "role_delete/$role->id?method=delete" @endphp
              <button onclick='openConfirmation("{{ url($url) }}", "GET")' type="button" class="cursor-pointer btn btn-xs btn-danger"><i class="bi bi-trash me-2"></i>Delete</button>
              @endcan
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <!-- endcan -->
</div>

@endsection