@extends('layouts/layoutMaster')
@section('title') {{'Account Category'}} @endsection
@section('content')
<!-- Users List Table -->
<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-3">Category</h5>
    <div>
      @can('Account-Category Add')
      <button data-bs-toggle="modal" data-bs-target="#addCategoryModal" class="btn btn-primary">Add Category</button>
      @include('content/account/category/add-category-modal')
      @endcan
    </div>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-users table border-top">
      <thead>
        <th>SL No.</th>
        <th>Name</th>
        <th>Type</th>
        <th>Status</th>
        <th>Date</th>
        <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($categories as $index=>$category)
        <tr>
          <td>{{$index+1}}</td>
          <td>{{$category->name}} <span class="text-success">({{$category->type =='Income' ? $category->daily_income_count: $category->daily_expanse_count}})</span></td>
          <td>{{$category->type}}</td>
          <td>
            @if($category->status == true)
            True
            @else
            False
            @endif
          </td>
          <td>{{$category->created_at}}</td>
          <td>
            @can('Account-Category Edit')
            <div class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal_{{$category->id}}"><i class="bi bi-pencil-square"></i> </div>
            @include('content/account/category/edit-category-modal', ['category' => $category])
            @endcan
            @can('Account-Category Delete')
            <a onclick="return confirm('are you sure to delete')" class="btn btn-sm btn-danger" href="{{route('accountCategoryDelete', $category->id)}}"><i class="bi bi-trash"></i></a>
            @endcan
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection