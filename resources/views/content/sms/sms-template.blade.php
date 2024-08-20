@extends('layouts/layoutMaster')
@php $route = 'sms_templates' @endphp

@section('title') SMS Template @endsection
@section('content')

<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-3">SMS Tempate</h5>
    @can('SMS Template Add')
    <a href='{{route("$route.create")}}' class="ml-4 btn btn-primary">Add item</a>
    @endcan
  </div>

  <div class="card-datatable table-responsive">
    <table class="datatables-users table">
      <thead>
        <tr>
          <th>SL No.</th>
          <th>Name</th>
          <th>template_for</th>
          <th>SMS Api</th>
          <th>Template</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($sms_templates as $sms_template)
        <tr>
          <td>{{$sms_template->id}}</td>
          <td>{{$sms_template->name}}</td>
          <td>{{$sms_template->type}}</td>
          <td>{{$sms_template->sms_api->name}}</td>
          <td>{{$sms_template->template}}</td>
          <td>{{$sms_template->status}}</td>
          <td class="">
            @can('SMS Template Edit')
            <a class="btn btn-sm btn-primary mb-1" href='{{route("$route.edit", $sms_template->id)}}'> <i class="bi bi-pencil-square"></i></a>
            @endcan
            @can('SMS Template Delete')
            <form action='{{ route("$route.destroy", $sms_template->id)}}' method="post">
              @csrf
              @method('DELETE')
              <button onclick="return confirm('Are you sure to delete')" type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
            </form>
            @endcan
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection