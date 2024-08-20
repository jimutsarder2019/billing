@extends('layouts/layoutMaster')
@section('title')
{{'Send SMS'}}
@endsection

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endsection

@section('vendor-script')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

@endsection

@section('page-script')
<script>
  new DataTable('#b5-dataTable', {
    pagingType: 'full_numbers'
  });
  new DataTable('#b5-dataTable_offline', {
    pagingType: 'full_numbers'
  });
</script>
@endsection

<style>
  #b5-dataTable_wrapper .dataTables_info,
  #b5-dataTable_wrapper .dataTables_paginate,
  #b5-dataTable_wrapper .dataTables_length {
    display: block !important;
  }

  #b5-dataTable_offline .dataTables_info,
  #b5-dataTable_offline .dataTables_paginate,
  #b5-dataTable_offline .dataTables_length {
    display: block !important;
  }

  .paginate_button .page-item .first {
    display: none;
  }

  #b5-dataTable_wrapper .dt-row {
    margin-bottom: 20px !important;
  }

  div.dataTables_wrapper div.dataTables_paginate ul.pagination {
    justify-content: center !important;
  }

  #b5-dataTable_info {
    font-size: 13px;
    width: 1000px;
    margin-top: 38px;
  }
</style>
@section('content')
<?php
$user_list = session('user_list');
?>
<style>
  .f-left {
    float: left;
  }
</style>
<!-- Users List Table -->
<div class="card">
  <div class="card-body">
    <h3>Send SMS</h3>
    @can('SMS Send sms')
    <form action="{{route('send-sms')}}" method="POST">
      @csrf
      <div class="row">
        <div class="col-sm-12 col-md-7">
          @if($user_list)
          <div class="mb-3">
            <input class="form-check-input" type="checkbox" id="individual_sms" name="individual_sms">
            <label for="individual_sms" class="form-label">Individual Sms</label>
          </div>
          <div class="mb-3">
            <label class="form-label" for="api">Select API</label>
            <select id="api" name="api" class="select2 form-select">
              <option value="">Please Select One</option>
              @foreach($sms_apis as $template)
              <option @if (old('api')==$template->id) selected @endif value="{{$template->id}}">{{$template->name}}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="message">Message</label>
            <textarea name="message" id="message" cols="30" rows="10" class="form-control">{{ old('message') }}</textarea>
          </div>
          @endif
          <div class="d-flex">
            <div>
              <div class="ml-4 f-left">
                <input @if(is_array(old('user_type')) && in_array('home_user', old('user_type'))) checked @endif type="checkbox" class="form-check-input" id="home_user" name="user_type[]" value="home_user">
                <label class="form-label" for="home_user">home user</label>
              </div>
              <div class="ml-4 f-left">
                <input @if(is_array(old('user_type')) && in_array('corporate_user', old('user_type'))) checked @endif type="checkbox" class="form-check-input" id="corporate_user" name="user_type[]" value="corporate_user">
                <label class="form-label" for="corporate_user">corporate user</label>
              </div>
              <div class="ml-4 f-left">
                <input @if(is_array(old('user_type')) && in_array('dashboard_user', old('user_type'))) checked @endif type="checkbox" class="form-check-input" id="dashboard_user" name="user_type[]" value="dashboard_user">
                <label class="form-label" for="dashboard_user">dashboard user</label>
              </div>
              <div class="ml-4 f-left">
                <input @if(is_array(old('user_type')) && in_array('active_customer', old('user_type'))) checked @endif type="checkbox" class="form-check-input" id="active_customer" name="user_type[]" value="active_customer">
                <label class="form-label" for="active_customer">active customer</label>
              </div>
              <div class="ml-4 f-left">
                <input @if(is_array(old('user_type')) && in_array('inactive_customer', old('user_type'))) checked @endif type="checkbox" class="form-check-input" id="inactive_customer" name="user_type[]" value="inactive_customer">
                <label class="form-label" for="inactive_customer">inactive customer</label>
              </div>
              <div class="ml-4 f-left">
                <input @if(is_array(old('user_type')) && in_array('pending_customer', old('user_type'))) checked @endif type="checkbox" class="form-check-input" id="pending_customer" name="user_type[]" value="pending_customer">
                <label class="form-label" for="pending_customer">pending customer</label>
              </div>
            </div>
          </div>
          @if(!$user_list) <button class="btn btn-primary mt-2" type="submit"> Check User </button>
          @else
          <div class="">
            <div class="form-group mt-4 mb-2">
              <input type="checkbox" name="confirm" value="true" class="form-check-input" id="conf">
              <label for="conf">Confirm Before Send SMS</label>
            </div>
            <button class="btn btn-primary" type="submit"> Submit </button>
          </div>
          @endif
        </div>
        <div class="col-sm-12 col-md-5 card-datatable table-responsive">
          @if($user_list)
          <strong>Customers:</strong> ({{count($user_list)}})
          <table id="b5-dataTable" class="datatables-users table border-top">
            <thead>
              <tr>
                <th>
                  <input type="checkbox" name="confirm" value="true" class="form-check-input" id="check-all">
                </th>
                <th>User Name</th>
                <th>Phone</th>
              </tr>
            </thead>
            <tbody id="users_table">
              @foreach($user_list as $user)
              <tr>
                <td>
                  <input type="checkbox" name="check_users[]" value="{{$user->phone}}" class="form-check-input check-item" id="">
                </td>
                <td>{{$user->name ?? $user->username }}</td>
                <td>{{$user->phone}}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
          @endif
        </div>
      </div>
    </form>
    @endcan
  </div>
</div>
@endsection
@push('pricing-script')
<script>
  // jQuery code for handling the checkbox functionality
  $(document).ready(function() {
    // Check/uncheck all items and groups
    $('#check-all').change(function() {
      $('.check-item').prop('checked', $(this).prop('checked'));
    });
    // Check/uncheck the items within a group
    $('.check-item').change(function() {
      // Uncheck "All" if any item is unchecked
      $('#check-all').prop('checked', $('.check-item').not(':checked').length === 0);
    });

  });
  $(document).ready(function() {
    $("#conf").change(function() {
      if (this.checked) {
        $("#api").prop("required", true);
        $("#message").prop("required", true);
      } else {
        $("#api").prop("required", false);
        $("#message").prop("required", false);
      }
    });
  });
</script>
@endpush