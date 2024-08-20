@extends('layouts/layoutMaster')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />

@section('title') Package @endsection
@section('content')
<!-- Users List Table -->
<div class="card">
  <div class="card-header">
    <h5 class="card-title mb-3">Packages</h5>
    <div class="row">
      <form class="col-9" action='{{route("packages-view-package")}}'>
        <div class="row">
          <div class="col-2">
            <select class="form-control" name="item" onchange="this.form.submit()" id="">
              <option @if ($packages->count() == '10') selected @endif value="10">10</option>
              <option @if ($packages->count() == '50') selected @endif value="50">50</option>
              <option @if ($packages->count() == '100') selected @endif value="100">100</option>
              <option @if ($packages->count() == $packages->total()) selected @endif value="{{$packages->total()}}">All</option>
            </select>
          </div>
          <div class="col-5">
            <?php
            $mikrotiks =  App\Models\Mikrotik::select('id', 'host', 'identity')->get();
            ?>
            <select id="mikrotik" name="mikrotik" class="select2 form-select" onchange="this.form.submit()">
              <option value="">Please Select One</option>
              @foreach($mikrotiks as $mikrotik)
              <option {{request('mikrotik') == $mikrotik->id ? 'selected' : ''}} value="{{$mikrotik->id}}">{{$mikrotik->identity}}</option>
              @endforeach
            </select>
          </div>
          <div class="col-5">
            <div class="input-group">
              <input type="search" name="search_query" class="form-control" value="{{request()->search_query}}" placeholder="Search" id="">
              <button type="submit" class="btn btn-outline-primary">Search</button>
            </div>
          </div>
        </div>
      </form>
      <div class="col-3">
        <div class="d-flex">
          <a href="{{route('packages-view-package')}}" class="btn btn-outline-warning">Clear</a>
          @can('Add Package')
          <a href="{{route('packages-add-package')}}" class="btn btn-primary mx-3">Add New</a>
          @endcan
        </div>
      </div>
    </div>
  </div>

  <div class="card-datatable table-responsive">
    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th>SL No.</th>
          <th><i class="fa fa-cogs"></i></th>
          <th>Package Name</th>
          <th>Synonym</th>
          <th>Mikrotik</th>
          <th>Price</th>
          <th>Manager Price</th>
          <th>Type</th>
          <th>Fixed Expire Date</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($packages as $package)
        <tr>
          <td>{{$package->id}}</td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
              <div class="dropdown-menu">
                @can('Packages Edit')
                <a class="dropdown-item text-primary" href="{{route('packages-edit-package', $package->id)}}">
                  <i class="bi bi-pencil-square mr-3"></i> Edit
                </a>
                @endcan
                @can('Packages Delete')
                @php $url = "package-delete/$package->id?method=get" @endphp
                <div onclick='openConfirmation("{{ url($url) }}", "GET")' class="text-danger cursor-pointer dropdown-item"><i class="bi bi-trash  mr-3"></i>Delete</div>
                @endcan
              </div>
            </div>
          </td>
          <td>{{$package->name}}</td>
          <td>{{$package->synonym}}</td>
          <td>{{$package->mikrotik->identity}}</td>
          <td>{{$package->price}}</td>
          <td>{{$package->franchise_price}}</td>
          <td>{{$package->type}}</td>
          <td>{{$package->fixed_expire_time}}</td>
          <td>
            <div class="badge {{$package->status == 1 ? ' bg-success':' bg-warning'}}">{{$package->status == 1 ? 'Active':'Disabled'}}</div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="ml-4 data_table_pagination">{{ $packages->appends(['item'=>request('item'),'mikrotik' => request('mikrotik'),'search_query'=>request('search_query') ])->links() }}</div>
  </div>
</div>
@endsection