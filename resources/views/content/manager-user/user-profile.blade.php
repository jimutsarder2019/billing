@extends('layouts/layoutMaster')
@section('title') {{$data->name}} @endsection
@section('content')
<h4 style="display:none" class="fw-bold py-3  card card-body">
  <span class="text-muted fw-light">User / View / {{$data->name}}</span>
</h4>
<div class="row">
  <div class="col-xl-8 col-lg-7 col-md-12 order-0 order-md-1">
    <!-- Invoice table -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="tab-content p-1" id="myTabContent">
            <div class="tab-pane fade show active" id="password-tab-pane" role="tabpanel" aria-labelledby="password-tab" tabindex="0">
            <form action="{{route('managerUserChangePassword', $data->id)}}" method="post" class="mt-2">
              @method('put')
              @csrf
              <h5 class="mt-2">Change Password</h5>
              <div class="form-group">
                <label for="password">New Password</label>
                @if($errors->has('password')) <br><span class="text-danger"> {{$errors->first('password')}}</span> @endif
                <input type="text" name="password" id="password" class="form-control">
              </div>
              <div class="form-group">
                <label for="c_password">Confirm Password</label>
                @if($errors->has('password_confirmation'))<br><span class="text-danger"> {{$errors->first('password_confirmation')}}</span> @endif
                <input type="text" name="password_confirmation" id="c_password" class="form-control">
              </div>
              <input type="submit" value="Update Password" class="btn btn-sm btn-outline-primary mt-2">
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ User Content -->

</div>
@endsection

@push('pricing-script')
<script script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js" integrity="sha512-uMtXmF28A2Ab/JJO2t/vYhlaa/3ahUOgj1Zf27M5rOo8/+fcTUVH0/E0ll68njmjrLqOBjXM3V9NiPFL5ywWPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
  // jQuery code for handling the checkbox functionality
  $(document).ready(function() {

    //suspended user
    $('#customer_desabled_btn').click(function() {
      if (confirm('Are you sure to desabled selected user')) {
        var checkedItems = $("input:checkbox[name='customers_id[]']:checked");
        var checkedValues = [];

        checkedItems.each(function() {
          checkedValues.push($(this).val());
        });
        axios.post(`/disabled-multiple-customer`, {
          'customers_id': checkedValues,
          'status': 1
        }).then((resp) => {
          window.location.reload();
        });
      }
    });
    //suspended user
    $('#customer_enabled_btn').click(function() {
      if (confirm('Are you sure to enabled selected user')) {
        var checkedItems = $("input:checkbox[name='customers_id[]']:checked");
        var checkedValues = [];

        checkedItems.each(function() {
          checkedValues.push($(this).val());
        });
        axios.post(`/disabled-multiple-customer`, {
          'customers_id': checkedValues,
          'status': 0
        }).then((resp) => {
          window.location.reload();
        });
      }
    });

    //check and uncheck btn 
    $('#check-all').change(function() {
      const isChecked = $(this).prop('checked');
      $('#customer_desabled_btn').toggleClass('d-none', !isChecked);
      $('#customer_enabled_btn').toggleClass('d-none', !isChecked);
      $('.check-item').prop('checked', isChecked);
    });

    // Check/uncheck the items within a group
    $('.check-item').change(function() {
      // Uncheck "All" if any item is unchecked
      const allChecked = $('.check-item:not(:checked)').length === 0;
      $('#check-all').prop('checked', allChecked);

      // Show/Hide the suspend button based on the checked items
      const anyChecked = $('.check-item:checked').length > 0;
      $('#customer_desabled_btn').toggleClass('d-none', !anyChecked);
      $('#customer_enabled_btn').toggleClass('d-none', !anyChecked);
    });
  });
</script>
@endpush