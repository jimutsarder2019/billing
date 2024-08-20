@extends('layouts/layoutMaster')
@php $route = 'sms-report' @endphp
@section('title')
{{$route}}
@endsection

@section('content')

<style>
  .highlight {
    background-color: yellow;
    font-weight: bold;
  }
</style>
<div class="card">
  @if(count($get_files) >0)
  @foreach(array_reverse($get_files) as $file)
  <div class="d-flex float-start">
    <?php $file_path = 'storage/backup/sms/' . $file['name'] ?>
    <?php $download_file_path = 'public/backup/sms/' . $file['name'] ?>
    <a onclick="return confirm('Are you sure to Download it')" class="badge bg-secondary mb-1 p-2" href="{{route('activity-log',['file'=>$file_path,'action'=>'download'])}}">{{$file['name']}} <b class="text-success">{{$file['size']}}</b></a>
    <a onclick="return confirm('Are you sure to delete it')" class="btn btn-sm btn-danger py-0" href="{{route('activity-log',['file'=>$download_file_path,'action'=>'delete'])}}"> <i class="bi bi-trash"></i></a>
  </div>
  @endforeach
  @endif
  <div class="card-header p-1">
    <h4 class="text-capitalize">{{ str_replace('-',' ', $route) }} ({{$data->total()}})</h4>
    <form class="m-0 p-0" action='{{route("$route.index")}}'>
      <div class="row">
        <div class="col-md-1">
          <select class="form-control" name="item" onchange="this.form.submit()" id="">
            <option @if ($data->count() == '10') selected @endif value="10">10</option>
            <option @if ($data->count() == '50') selected @endif value="50">50</option>
            <option @if ($data->count() == '100' ) selected @endif value="100">100</option>
            <option @if ($data->count() == $data->total()) selected @endif value="{{$data->total()}}">All</option>
          </select>
        </div>
        <div class="col-md-9">
          <div class="input-group">
            <input type="search" name="search_query" class="form-control" value="{{request()->search_query}}" placeholder="Search" id="">
            <button type="submit" class="btn btn-outline-primary">Search</button>
          </div>
        </div>
        <div class="col-md-2">
          <a href='{{route("$route.index")}}' class="btn btn-outline-warning">Clear</a>
        </div>
      </div>
    </form>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th>#ID</th>
          <th>message id</th>
          <th>number</th>
          <th>message</th>
          <th>status</th>
          <th>created_at</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $item)
        <tr>
          <td>{{$item->id}}</td>
          <td>{{$item->message_id}}</td>
          <td>{!! str_replace(request('search_query'), '<span class="highlight">' . request('search_query') . '</span>', $item->number) !!}</td>
          <td>{!! str_replace(request('search_query'), '<span class="highlight">' . request('search_query') . '</span>', $item->message) !!}</td>
          <td>{{$item->status}}</td>
          <td><span class="badge text-secondary">{{$item->created_at->format('d-M-Y H:i:s A')}} </span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="ml-4  pb-4 data_table_pagination">{{ $data->appends(['search_query'=>request('search_query') ])->links() }}</div>
  </div>
</div>
@endsection