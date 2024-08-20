@extends('layouts/layoutMaster')
@php $route = str_replace('.index','', Route::currentRouteName()) @endphp
@section('content')
<div class="card pb-3">
  <div class="card-header">
    <div class="d-flex">
      <form action='{{route("$route.index")}}'>
        <select class="form-control" name="item" onchange="this.form.submit()" id="">
          <option @if ($data->count() == '10') selected @endif value="10">10</option>
          <option @if ($data->count() == '50') selected @endif value="50">50</option>
          <option @if ($data->count() == '100' ) selected @endif value="100">100</option>
          <option @if ($data->count() == $data->total() ) selected @endif value="{{$data->total()}}">All</option>
        </select>
      </form>
      <a href='{{route("$route.create")}}' class="ml-4 btn btn-primary">Add item</a>
    </div>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th>#ID</th>
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
    <div class="ml-4 data_table_pagination">{{ $data->links() }}</div>
  </div>
</div>
@endsection