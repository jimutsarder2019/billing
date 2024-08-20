@extends('layouts/layoutMaster')
@php $page = "division"; $route = 'division' @endphp
@section('content')
<div class="card">
  <div class="card-header">
    <div><a href='{{route("$route.create")}}' class="btn btn-primary">Add item</a></div>
  </div>
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
        @foreach($data as $item)
        <tr>
          <td>{{$item->id}}</td>
          <td>{{$item->name}}</td>
          <td class="d-flex">
            <a class="btn btn-sm btn-primary" href='{{route("$route.edit", $item->id)}}'><i class="bi bi-pencil-square"></i></a>
            <form action='{{ route("$route.destroy",$item->id)}}' method="post">
              @csrf
              @method('DELETE')
              <button onclick="return confirm('Are you sure to delete the Category')" type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection