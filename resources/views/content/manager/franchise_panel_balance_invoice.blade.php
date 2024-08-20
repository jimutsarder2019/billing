@extends('layouts/layoutMaster')
@section('title', 'franchise_panel_balance_invoice')

@section('content')
<div class="card">
  <div class="card-header">
    <h5 class="card-title text-capitalize">franchise invoice</h5>
    <div class="row">
      <div class="col-sm-12 col-md-10">
        <form action='{{route("managers-manager-list")}}'>
          <div class="row">
            <div class="col-md-1">
              <select class="form-control" name="item" onchange="this.form.submit()" id="">
                <option @if ($data->count() == '10') selected @endif value="10">10</option>
                <option @if ($data->count() == '50') selected @endif value="50">50</option>
                <option @if ($data->count() == '100') selected @endif value="100">100</option>
                <option @if ($data->count() == $data->total()) selected @endif value="{{$data->total()}}">All {{$data->total()}}</option>
              </select>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="card-datatable table-responsive">
    <table class="datatables-users table">
      <thead>
        <tr>
          <th>SL No.</th>
          <th><i class="fa fa-cogs"></i></th>
          <th>inv</th>
          <th>Franchise</th>
          <th>Amount</th>
          <th>received Amount</th>
          <th>Due Amount</th>
          <th>Status</th>
          <th>Method</th>
          <th>Manager</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $in_item)
        <tr>
          <td>{{$in_item->id}}</td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
              <div class="dropdown-menu">
                @can('Invoice Details')
                <a href="{{route('invoice.show', $in_item->id)}}" title="View Profile" class="dropdown-item cursor-pointer"><i class="bi bi-pencil-square"></i> View Invoice </a>
                @endcan
              </div>
            </td>
            <td><span class="text-{{$in_item->status == 'paid'?'success':'warning'}} text-capitalize">{{$in_item->invoice_no}} </span></td>
          <td>{{$in_item->franchise_manager ? $in_item->franchise_manager->name:''}}</td>
          <td>{{$in_item->amount}}</td>
          <td>{{$in_item->received_amount}}</td>
          <td>{{$in_item->due_amount}}</td>
          <td><span class="badge bg-label-{{$in_item->status == 'paid'?'success':'warning'}} text-capitalize">{{$in_item->status}}</span></td>
          <td>{{$in_item->paid_by}}</td>
          <td>{{$in_item->manager->name}}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="ml-4 data_table_pagination">{{ $data->links() }}</div>
  </div>
</div>
@endsection