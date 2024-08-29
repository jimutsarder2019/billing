<div class="modal fade" id="bulk_grace_model" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered1 modal-simple modal-add-new-cc">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2">Bulk Grace </h3>
        </div>
        <form action="{{route('customer_bulk_grace')}}" class="row g-3" method="POST">
          @csrf
          <div class="col-12">
            <label class="form-label w-100" for="allow_grace">Allow Grace <small class="text-primary">(Max Allow Grace {{auth()->user()->grace_allowed ?? 0}})</small></label>
            <input id="allow_grace" name="allow_grace" class="form-control" required type="number" value="" min="1" max="{{auth()->user()->grace_allowed ?? 0}}" />
            <style>
              #checked_preview_customer_list_for_allow_grace tr td {
                font-size: 15px;
              }
            </style>
            <table class="datatables-users table">
              <thead>
                <tr>
                  <th><input type="checkbox" checked class="form-check-input" id="check-all-selected"></th>
                  <th>username</th>
                  <th>Dxpire Date</th>
                </tr>
              </thead>
              <tbody id="checked_preview_customer_list_for_allow_grace">
              </tbody>
            </table>
          </div>
          <div class="col-12 text-center">
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