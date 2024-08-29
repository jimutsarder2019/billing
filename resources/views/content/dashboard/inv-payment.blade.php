<div class="modal fade" id="payment_{{$inv->id}}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered1 modal-simple modal-add-new-cc">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2">Invoice Update</h3>
        </div>
        <form action="{{route('mini-dashboard-inv-update', $inv->id)}}" class="row g-3" method="POST">
          @method('put')
          @csrf
          <div class="col-12 text-center mt-2">
          </div>
          <div class="col-12 mt-2">
            <label class="form-label w-100" for="amount">Total Amount</label>
            <input id="amount" name="amount" class="form-control" type="text" value="{{$inv->amount}}" readonly />
          </div>
          <div class="col-12 mt-2">
            <label class="form-label w-100" for="received_amount">Received Amount</label>
            <input id="received_amount" name="received_amount" class="form-control" type="number" value="{{$inv->amount}}" readonly />
          </div>
          <div class="col-12 mt-2">
            <label class="form-label" for="paid_by">Paid By</label>
            <select readonly id="paid_by" name="paid_by" class="form-control">
              <option value="Bkash">Bkash</option>
            </select>
          </div>
          <div class="col-12 mt-2">
            <label class="form-label w-100" for="transaction_id">Transaction Id</label>
            <input id="transaction_id" required name="transaction_id" class="form-control" type="text" />
          </div>
          <div class="col-12 text-center mt-4">
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
            <button type="reset" class="btn btn-label-secondary btn-reset" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>