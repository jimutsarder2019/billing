@extends('layouts/layoutMaster')
@php $route = 'log-history' @endphp
@section('title', 'Logs')
@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/forms-pickers.js')}}"></script>
@endsection



@section('content')
<div class="card">
  @can('Activity Log All')
  @if(count($get_files)>0)
  <div class="card-header pb-3" style="display:none">
    @foreach(array_reverse($get_files) as $file)
    <div class="d-flex float-start p-1">
      <?php $file_path = 'storage/backup/activity/' . $file['name'] ?>
      <?php $download_file_path = 'public/backup/activity/' . $file['name'] ?>
      <a onclick="return confirm('Are you sure to Download it')" class="badge bg-secondary mb-1 p-2" href="{{route('activity-log',['file'=>$file_path,'action'=>'download'])}}">{{$file['name']}} <b class="text-success">{{$file['size']}}</b></a>
      <a onclick="return confirm('Are you sure to delete it')" class="btn btn-sm btn-danger py-0" href="{{route('activity-log',['file'=>$download_file_path,'action'=>'delete'])}}"> <i class="bi bi-trash"></i></a>
    </div>
    @endforeach
  </div>
  @endif
  @endcan
  <div class="card-header">
    <div class="row">
      <div class="col-sm-12 col-md-6">
        <form action='{{route("$route.index")}}'>
          <div class="row">
            <div class="col-sm-12 col-md-2">
              <select class="form-control" name="item" onchange="this.form.submit()" id="">
                <option @if ($data->count() == '10') selected @endif value="10">10</option>
                <option @if ($data->count() == '50') selected @endif value="50">50</option>
                <option @if ($data->count() == '100' ) selected @endif value="100">100</option>
                <option @if ($data->count() == $data->total() ) selected @endif value="{{$data->total()}}">All</option>
              </select>
            </div>
            <!-- <div class="col-sm-12 col-md-4">
              <input type="text" name="date_range" value="{{request('date_range')}}" class="form-control w-100" style="min-width: 318px !important;" placeholder="YYYY-MM-DD to YYYY-MM-DD" id="flatpickr-range" />
            </div> -->
            @if(auth()->user()->type == APP_MANAGER)
            <div class="col-sm-12 col-md-8">
              <?php
              $managers = App\Models\Manager::select('id', 'name', 'type')->get();
              ?>
              <select class="select2 form-select" name="manager" onchange="this.form.submit()" id="">
                <option value="">------Select-----</option>
                @foreach($managers as $m_item)
                <option @if (request('manager')==$m_item->id) selected @endif value="{{$m_item->id}}">{{$m_item->name}} | {{$m_item->type}}</option>
                @endforeach
              </select>
            </div>
            <div class="col-sm-12 col-md-2">
              <a href='{{route("$route.index")}}' class="btn btn-warning">Clear</a>
            </div>
            @endif
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th>#ID</th>
          <th>User</th>
          <th>IP Address</th>
          <th>Description</th>
          <th>Date</th>
          <!-- <th>Actions</th> -->
        </tr>
      </thead>
      <tbody>
        @foreach($data as $item)
        <tr>
          <td>{{$item->id}}</td>
          <td>{{$item->causer->name}}</td>
          <td>{{$item->batch_uuid}}</td>
          <td>{{$item->description}}</td>
          <td>{{$item->created_at->format('d-F-Y h:i A')}}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="ml-4 data_table_pagination">{{ $data->links() }}</div>
  </div>
</div>
@endsection