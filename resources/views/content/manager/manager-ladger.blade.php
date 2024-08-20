@extends('layouts/layoutMaster')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
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

@section('title','Managers Ledger')
@section('content')
<div class="card">
  <div class="card-header border-bottom py-2">
    <h5 class="card-title mb-3">Managers Ledger</h5>
    <form action='{{route("managers-ledger")}}'>
      <div class="row">
        <div class="col-md-1">
          <select class="form-control" name="item" onchange="this.form.submit()" id="">
            <option @if ($data->count() == '10') selected @endif value="10">10</option>
            <option @if ($data->count() == '50') selected @endif value="50">50</option>
            <option @if ($data->count() == '100' ) selected @endif value="100">100</option>
            <option @if ($data->count() == $data->total() ) selected @endif value="{{$data->total()}}">All</option>
          </select>
        </div>
        <div class="col-md-5">
          @if(auth()->user()->type !== FRANCHISE_MANAGER)
          <?php
          $managers = App\Models\Manager::select('id', 'name', 'type')->get();
          ?>
          <select class="select2 form-select" name="manager" onchange="this.form.submit()" id="">
            <option>------Select Manager-----</option>
            @foreach($managers as $m_item)
            <option @if (request('manager')==$m_item->id) selected @endif value="{{$m_item->id}}">{{$m_item->name}} | {{ucwords(str_replace('_',' ',$m_item->type))}} </option>
            @endforeach
          </select>
          @endif
        </div>
        <!-- <div class="col-md-3">
          </div> -->
        <div class="col-md-5">
          <div class="d-flex">
            <input type="text" name="date_range" value="{{request('date_range')}}" class="form-control w-100" placeholder="YYYY-MM-DD to YYYY-MM-DD" id="flatpickr-range" />
            <div class="input-group">
              <!-- <input type="search" name="search_query" class="form-control" value="{{request()->search_query}}" placeholder="Search" id=""> -->
              <button type="submit" class="btn btn-outline-primary">Search</button>
            </div>
            <a href="{{route('managers-ledger')}}" class="btn btn-outline-warning">Clear</a>
          </div>
        </div>
      </div>
  </div>
  </form>

  @if(auth()->user()->hasRole(SUPER_ADMIN_ROLE))
  <div class="card-datatable table-responsive">
    <table class="datatables-users table">
      <thead>
        <tr>
          <th>SL No.</th>
          <th>Date & Time </th>
          <th>Invoice</th>
          <th>Manager</th>
          <th>Amount</th>
          <th>Method</th>
          <th>Current Balance</th>
          <th>User Name</th>
          <th>Status</th>
          <th>Reject By</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $index=>$item)
        <tr>
          <td>{{$index+1}}</td>
          <td>{{$item->created_at->format('d-F-Y H:i s')}}</td>
          <td>{{$item->invoice ? $item->invoice->invoice_no :'' }}</td>
          <td>{{$item->manager->name}}</td>
          <td>{{$item->balance}} TK</td>
          <td>{{$item->invoice ? $item->invoice->paid_by :'' }}</td>
          <td>{{$item->franchise_panel_balance ? $item->franchise_panel_balance. 'TK':''}} </td>
          <td>{{$item->invoice && $item->invoice->customer ? $item->invoice->customer->username :'N/A'}} </td>
          @if($item->invoice)
          <td>
            @php
            $statusClass = '';
            switch ($item->invoice->status) {
            case STATUS_PAID:
            $statusClass = 'success';
            break;
            case STATUS_DUE:
            case STATUS_PENDING:
            $statusClass = 'warning';
            break;
            case STATUS_ACCEPTED:
            case STATUS_REJECTED:
            $statusClass = 'success';
            break;
            // Add more cases for other statuses if needed
            default:
            // If none of the above cases match, leave $statusClass empty
            }
            @endphp
            <span class="badge bg-label-{{ $statusClass }} text-capitalize">{{ $item->invoice->status }}</span>
          </td>
          @else
          <td></td>
          @endif
          <td>{{$item->app_manager ? $item->app_manager->name :'' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="ml-4 data_table_pagination">{{ $data->appends(['manager'=>request('manager'),'date_range'=>request('date_range')])->links() }}</div>
  </div>
  @endif
</div>
</div>
@endsection