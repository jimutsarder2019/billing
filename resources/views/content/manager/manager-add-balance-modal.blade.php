<!-- Add New Credit Card Modal -->
<div class="modal fade" id="managet_addbalance_{{$manager->id}}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered1 modal-simple modal-add-new-cc">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2">Add Balance</h3>
          <h3 class="mb-2">Current Balance {{$manager->panel_balance}} TK</h3>
        </div>
        <form action="{{route('managers-add-balance', $manager->id)}}" class="row g-3" method="POST">
          @method('put')
          @csrf
          <div class="form-group">
            <label for="">Manager Credit Balance</label>
            <input type="number" name="amount" class="form-control">
          </div>
          <div class="form-group">
            <label for="">Received Balance</label>
            <input type="number" name="received_amount" class="form-control">
          </div>
          <div class="col-12 text-center mt-2">
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
            <button type="reset" class="btn btn-label-secondary btn-reset" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>