<!-- Add New Credit Card Modal -->
<?php
$title = (isset($editData) ? 'Edit ' : 'Add ') .  "Income";
$form_action = (isset($editData)) ? route("updateDailyIncome", $editData->id) : route("account-store-daily-income");
?>
<div class="modal fade" id="{{isset($editData) ? 'editIncomeModal' : 'addIncomeModal'}}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered1 modal-simple modal-add-new-cc">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2">{{$title}} </h3>
        </div>
        <form action='{{$form_action}}' class="row g-3" method="POST">
          @if(isset($editData))
          @method('put')
          @endif
          @csrf
          <div class="col-12">
            <label class="form-label w-100" for="name">Service Name</label>
            <input id="name" name="name" value="{{isset($editData) ? $editData->service_name :''}}" class="form-control" required type="text" />
          </div>
          <div class="col-12">
            <label class="form-label" for="category">Category</label>
            <select id="category" name="category" required class="select2 form-select">
              <option value="">Please Select One</option>
              @foreach($categories as $category)
              <option {{isset($editData) && $editData->category_id == $category->id ? 'selected' :''}} value="{{$category->id}}">{{$category->name}}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12">
            <label class="form-label w-100" for="amount">Amount</label>
            <input id="amount" name="amount" class="form-control" required type="number" value="{{isset($editData) ? $editData->amount :''}}" min="1" />
          </div>
          <div class="col-12">
            <label class="form-label" for="method">Method</label>
            <select id="method" name="method" class="select2 form-select" required onchange="toggleTransactionIdField(this)">
              <option value="">Please Select One</option>
              @foreach(PAYMENT_METHOD_ITEMS as $p_item)
              <option {{isset($editData) && $editData->method == $p_item ? 'selected' :''}} value="{{$p_item}}">{{$p_item}}</option>
              @endforeach
            </select>
          </div>
          <div id="transaction_id_field" class="col-12 d-none">
            <label class="form-label w-100" for="transaction">Transaction Id</label>
            <input id="transaction" name="transaction" value="{{isset($editData) ? $editData->transaction_id :''}}" class="form-control" type="text" />
          </div>
          <div class="col-12">
            <label class="form-label w-100" for="date">Date</label>
            <input id="date" name="date" class="form-control" value="{{isset($editData) ? $editData->date :''}}" required type="datetime-local" />
          </div>
          <div class="col-12">
            <label class="form-label w-100" for="description">Description</label>
            <textarea name="description" id="description" required class="form-control">{{isset($editData) ? $editData->description :''}}</textarea>
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

<script>
  function toggleTransactionIdField(method) {
    if (method.value == 'Bkash') {
      document.getElementById('transaction_id_field').classList.remove('d-none');
    } else {
      document.getElementById('transaction_id_field').classList.add('d-none');
    }
  }
</script>