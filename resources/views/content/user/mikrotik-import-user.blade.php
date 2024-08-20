@extends('layouts/layoutMaster')

@section('title')
{{ str_replace(['/','-'],' ', Request::path())}}
@endsection
@section('content')
<div class="card pb-3">
  <div class="card-body">
    <h5 class="card-title mb-3">Mikrotik Import Users</h5>
    <form action='{{route("mikrotik_import_users")}}'>
      <div class="row">
        <div class="col-sm-2 col-md-1">
          <select class="form-control mr-4" name="item" onchange="this.form.submit()" id="">
            <option @if ($mikrotik_users->count() == '10') selected @endif value="10">10</option>
            <option @if ($mikrotik_users->count() == '50') selected @endif value="50">50</option>
            <option @if ($mikrotik_users->count() == '100' ) selected @endif value="100">100</option>
            <option @if ($mikrotik_users->count() == $mikrotik_users->total() ) selected @endif value="{{$mikrotik_users->total()}}">All</option>
          </select>
        </div>
        @if(auth()->user()->type == APP_MANAGER )
        <?php
        $mikrotiks = App\Models\Mikrotik::select('id', 'host', 'identity')->get();
        ?>
        <div class="col-sm-8 col-md-8">
          <select name="mikrotik_id" class="form-control" onchange="this.form.submit()">
            <option value="">Select</option>
            @foreach($mikrotiks as $mkt)
            <option {{request('mikrotik_id') == $mkt->id ?'selected' :''}} value="{{$mkt->id}}">{{$mkt->identity}}</option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-4 col-md-2">
          <div class="d-flex">
            <a href="{{route('mikrotik_import_users')}}" class="btn btn-xs btn-warning">Reset</a>
            @if(request('mikrotik_id'))
            <a href="{{route('user-export',['mikrotik_id'=>request('mikrotik_id')])}}" class="btn btn-xs btn-primary">Download Csv</a>
            @endif
          </div>
        </div>
        @endif
      </div>
    </form>
    <form action="{{route('mkt_pendingcustomer_assign_franchise')}}" method="post">
      @csrf
      <div class="row">
        @if(auth()->user()->type == APP_MANAGER)
        <div class="col-sm-12 col-md-9">
          <div class="row mt-2">
            @if($mikrotik_users->count() > 0)
            <div class="col-8" id="frinchise_managers">
              @if($errors->has('franchise_manager'))<span class="text-danger"> {{$errors->first('franchise_manager')}}</span> @endif
              <div class="d-flex">
                <label for="">Assign Franchise</label>
                <?php
                $frinchise  = App\Models\Manager::select('id', 'name', 'type', 'mikrotik_id')->where(['type' => 'franchise', 'mikrotik_id' => request('mikrotik_id')])->get();
                ?>
                <select class="pl-2 form-control" name="franchise_manager">
                  <option value="" selected>Select One</option>
                  @foreach($frinchise as $f_item)
                  <option value="{{$f_item->id}}">{{$f_item->name}}</option>
                  @endforeach
                </select>
                <input type="submit" value="Save" class="btn btn-xs btn-outline-primary">
              </div>
            </div>
            @elseif($mikrotik_users->count() <= 0 && request('mikrotik_id') ) <div class="col-sm-12 col-md-12">
              <div class="alert alert-warning  mx-2">No Uesr Found</div>
          </div>
          @endif
        </div>
      </div>
      @endif
  </div>
  @if($errors->has('selected_customers'))<span class="text-danger"> {{$errors->first('selected_customers')}}</span> @endif
  <div class="card-datatable table-responsive">
    <table class="datatables-users table">
      <thead>
        <tr>
          <td>
            SL No.
            @if(auth()->user()->type == APP_MANAGER)
            <input type="checkbox" class="form-check-input" id="check-all">
            @endif    
          </td>
          <th>Added As Customer</th>
          <th>Manager Name</th>
          <th>Package</th>
          <th>Mikrotik</th>
          <th>Username</th>
          <th>Password</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="mikrotik_added_users">
        @foreach($mikrotik_users as $index=> $ppp_user)
        <tr>
          <td>
            <span class="d-flex">
              @if(!$ppp_user->manager)
              <input type="checkbox" name="selected_customers[]" value="{{$ppp_user->id}}" class="form-check-input check-item">
              @endif
              {{$index+1}}
            </span>
          </td>
          <td>
            {{$ppp_user->added_in_customers_table == 1 ? 'Yes' : 'No'}}
          </td>
          <td>{{$ppp_user->manager ? $ppp_user->manager->name : ''}}</td>
          <td>{{$ppp_user->profile}}</td>
          <td>{{$ppp_user->mikrotik->identity}}</td>
          <td>{{$ppp_user->name}}</td>
          <td>{{$ppp_user->password}}</td>
          <td>{{$ppp_user->status}}</td>
          <td>
            @if($ppp_user->added_in_customers_table == 0)
            <a class="btn btn-xs btn-primary" href="{{route('user-edit-mikrotik-customer', $ppp_user->id)}}">
              <div class="cursor-pointer">
                <i class="bi bi-pencil-square"></i>
              </div>
            </a>
            @endif
            <!-- <div class="btn btn-xs btn-danger"><i class="bi bi-trash"></i></div> -->
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="ml-4 data_table_pagination">{{ $mikrotik_users->appends(['mikrotik_id' => request('mikrotik_id') ])->links() }}</div>
  </div>
  </form>
</div>
</div>
@endsection

@push('pricing-script')
<script>
  $(document).ready(function() {
    // Check/uncheck all items and groups
    $('#check-all').change(function() {
      $('.check-item').prop('checked', $(this).prop('checked'));
    });
  });
</script>
@endpush