<div class="modal fade" id="bulk_grace_model" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered1 modal-xl modal-add-new-cc">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body p-0">
        <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
        <div class="text-center mb-4">
          <h3 class="mb-2"><span class="p-1 rounded">Bulk Renew</span></h3>
        </div>
        <form action="{{route('bulk_renew_customer_expire_date')}}" class="row g-3" method="POST">
          @csrf
          <div class="col-6 m-auto">
            <div class="d-flex justify-content-center">
              <select name="extended_days" id="extended_days" style="width: 100px;" class="text-center form-control" onchange="changeDays()">
                <option value="">--Select Days--</option>
                {{$start = 1}}
                @for ($i = $start; $i <= 31; $i++) <option value="{{$i}}">{{$i}}</option>@endfor
              </select>
            </div>
          </div>
          <div class="col-12">
            <!-- <label class="form-label w-100" for="allow_grace">Date</label>
            <input type="datetime-local" class="form-control" required type="number" value="" min="1" max="{{auth()->user()->grace_allowed ?? 0}}" /> -->
            <style>
              #checked_preview_customer_list_for_allow_grace tr td {
                font-size: 15px;
              }
            </style>
            <table class="datatables-users table border">
              <thead>
                <tr>
                  <th class="p-1 border-end"><input type="checkbox" checked class="form-check-input" id="check-all-selected"></th>
                  <th class="p-1 border-end">Username</th>
                  <th class="p-1 border-end">Wallet</th>
                  <th class="p-1 border-end">Bill</th>
                  <th class="p-1 border-end">Expire Date</th>
                  <th class="p-1">Renew Expire Date</th>
                </tr>
              </thead>
              <tbody id="checked_preview_customer_list_for_allow_grace">
              </tbody>
            </table>
          </div>
          <div class="col-12 text-center mt-3">
            <button onclick="return confirm('are you sure to submit')" type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
            <button type="reset" class="btn btn-label-secondary btn-reset" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@push('pricing-script')
<script>
  function changeDays(e) {
    // console.log('extended_days', extended_days);
    const checkedItems = $("input[name='selected_for_grace_customers[]']:checked")
    if (checkedItems.length > 0) {
      var html = ''
      checkedItems.each(function() {
        var item = JSON.parse($(this).val());
        // console.log('item', item);
        var expireDate = new Date(item.expire_date);
        var wallet = item.wallet;
        var bill = item.bill;
        
        const extended_days = document.querySelector('select[name="extended_days"]').value ? parseInt(document.querySelector('select[name="extended_days"]').value) : 0;
        // console.log(extended_days);
        expireDate.setMonth(expireDate.getMonth() + 1);
        // console.log(expireDate.getDate());
        expireDate.setDate(expireDate.getDate() + extended_days);
        // const auth_user_type = document.querySelector('input[name="auth_user_type"]').value;
        // console.log(auth_user_type);
        var formattedDate = expireDate.toISOString().slice(0, 10); // Formatting to YYYY-MM-DD
        var formattedTime = expireDate.toLocaleTimeString('en-US', {
          hour12: false
        }); // Formatting time
        var new_exp_date = formattedDate + ' ' + formattedTime;
        html += `<tr class="${item.bill <item.wallet?'alert-success':'' }">
                  <td class="p-1 border-end"><input type="checkbox" name="selected_for_customers[]" checked value="${item.id}|${new_exp_date}" class="form-check-input selected_check-item"></td>
                  <td class="p-1 border-end"> ${item.username}</td>
                  <td class="p-1 border-end">${item.wallet}</td>
                  <td class="p-1 border-end">${item.bill}</td>
                  <td class="p-1 border-end">${item.expire_date}</td>
                  <td class="p-1"><input class="form-control" type="datetime-local" value="${new_exp_date}"> </td>
                </tr>`
      });
      document.getElementById("checked_preview_customer_list_for_allow_grace").innerHTML = html
    }
  }






  $(document).ready(function() {
    // Check/uncheck all items and groups
    $('#check-all-selected').change(function() {
      $('.selected_check-item').prop('checked', $(this).prop('checked'));
    });
    // Check/uncheck the items within a group
    $('.selected_check-item').change(function() {
      // Uncheck "All" if any item is unchecked
      $('#check-all-selected').prop('checked', $('.selected_check-item').not(':checked').length === 0);
    });
  });
</script>
@endpush