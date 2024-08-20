<!-- Add New Credit Card Modal -->
<div class="modal fade" id="customer_import" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered1 modal-simple modal-add-new-cc">
    <div class="modal-content p-1">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center">
          <h3 class="mb-2">Import Customers</h3>
        </div>
        <form action="{{route('import-customer')}}" class="row g-3" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="form-group">
            <label for="select_import_file">Choose File</label>
            <input type="file" name="file" accept=".csv" id="select_import_file" required class="form-control">
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