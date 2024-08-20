<!-- Add New Credit Card Modal -->
<div class="modal fade" id="refund_{{$id}}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered1 modal-simple modal-add-new-cc">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body p-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2">Refund Invoice</h3>
        </div>
        <div class="">
          <form class="mt-3" action="{{route('invoice-refund', $id)}}" method="POST">
            @csrf
            @method('put')
            <label class="text-left" for="">Reasons</label>
            <select name="reasons" id="" class="form-control">
              <option value="">-----select-----</option>
              <option value="update-error">Error To Update</option>
            </select>
            <label class="text-left" for="">Note</label>
            <textarea name="note" class="form-control" id="" cols="30" rows="3" placeholder="Write Note"></textarea>
            <button onclick="return confirm('are you sure to save changes !')" type="submit" class="btn btn-primary mt-3"> Submit</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<!--/ Add New Credit Card Modal -->