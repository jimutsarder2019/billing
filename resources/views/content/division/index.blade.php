@extends('layouts/layoutMaster')
@php $page = "division"; $route = 'division' @endphp
@section('title', $page)
@section('content')
<div class="card">
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
      @can('Divisions Add')
      <a href='{{route("$route.create")}}' class="ml-4 btn btn-primary">Add item</a>
      @endcan
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
            @can('Divisions Edit')
            <a class="btn btn-sm btn-primary" href='{{route("$route.edit", $item->id)}}'><i class="bi bi-pencil-square me-1"></i> Edit</a>
            @endcan
            @can('Divisions Delete')
            @php $url = "network/division/$item->id" @endphp
            <button type="submit" class="btn btn-sm ms-2 btn-danger"
                onclick='openConfirmation("{{ url($url) }}")'><i class="bi bi-trash me-1"></i>
                Delete</button>
            @endcan
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection