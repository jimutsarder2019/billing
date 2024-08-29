@extends('layouts/layoutMaster')
@php $route = 'ticketcategory' @endphp
@section('title', 'ticketcategory')
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
      @can('Thana Add')
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
          <th>Priority</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $item)
        <tr>
          <td>{{$item->id}}</td>
          <td>{{$item->name}}</td>
          <td>{{$item->priority}}</td>
          <td class="d-flex">
            @can('Ticket Category Edit')
            <a class="btn btn-sm btn-primary" href='{{route("$route.edit", $item->id)}}'><i class="bi bi-pencil-square"></i></a>
            @endcan
            @can('Ticket Category Delete')
            <button type="submit" class="btn btn-sm btn-danger" onclick='openConfirmation("{{ url("ticketcategory/$item->id") }}")'><i class="bi bi-trash"></i></button>
            @endcan
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="ml-4 data_table_pagination">{{ $data->links() }}</div>
  </div>
</div>
@endsection