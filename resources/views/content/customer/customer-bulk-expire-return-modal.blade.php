<!-- Add New Credit Card Modal -->
<div class="modal fade show" style="display: block;" id="customer_customer_update_return" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered1 modal-simple modal-add-new-cc">
    <div class="modal-content p-1">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center">
          <h3 class="mb-2">Bulk Renew Response</h3>
        </div>
        <div class="form-group">
          <table class="datatables-users table border">
            <thead>
              <tr>
                <th class="p-0" colspan="2">
                  <p class="m-0 text-center alert alert-success">Completed </p>
                </th>
              </tr>
            </thead>
            <thead>
              <tr>
                <th>Username</th>
                <th>Renew Expire Date</th>
              </tr>
            </thead>
            <tbody>
              @foreach($updated_customer as $update_item)
              <tr>
                <td>{{$update_item->username}}</td>
                <td>{{$update_item->expire_date}}</td>
              </tr>
              @endforeach
              
            </tbody>
          </table>
          @if($non_updated_customer->count() >0)
          <table class="datatables-users table border mt-3">
            <thead>
              <tr>
                <th class="p-0" colspan="2">
                  <p class="m-0 text-center alert alert-warning">Not Completed </p>
                </th>
              </tr>
            </thead>
            <thead>
              <tr>
                <th>Username</th>
                <th>Expire Date</th>
              </tr>
            </thead>
            <tbody>
              @foreach($non_updated_customer as $n_update_item)
              <tr>
                <td>{{$n_update_item->username}}</td>
                <td>{{$n_update_item->expire_date}}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
          @endif
        </div>
        <div class="col-12 text-center mt-2">
          <a href='{{route("user-view-user")}}' class="btn btn-label-secondary btn-reset">Close</a>
        </div>
      </div>
    </div>
  </div>
</div>