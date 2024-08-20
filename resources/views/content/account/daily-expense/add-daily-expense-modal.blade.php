<!-- Add or update Card Modal -->
<?php
$title = (isset($editData) ? 'Edit ' : 'Add ') .  "Expense";
$form_action = (isset($editData)) ? route("updateDailyExpense", $editData->id) : route("account-store-daily-expense");
?>
<div class="modal fade @if($errors->any()) show @endif" @if($errors->any()) style="display: block;" @endif id='{{isset($editData) ? "updateModal-".$editData->id : "createModal"}}' data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="updateModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered1 modal-simple modal-add-new-cc">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        @if($errors->any())
        <a href='{{route("account-daily-expenses")}}'>
          <button type="button" class="btn-close" aria-label="Close"></button>
        </a>
        @else
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        @endif
        <div class="text-center mb-4">
          <h3 class="mb-2">{{$title}} </h3>
        </div>
        <form action=' {{$form_action}}' class="row g-3" method="POST">
          @if(isset($editData))
          @method('put')
          @endif
          @csrf
          <div class="col-12">
            <label class="form-label w-100" for="name">Expense Claimant</label>
            <input id="name" required name="name" value="{{isset($editData) ? $editData->expense_claimant :''}}" class="form-control" type="text" />
          </div>
          <div class="col-12">
            <label class="form-label" for="category">Category</label>
            <select required id="category" name="category" class="form-control">
              <option>Select</option>
              @foreach($categories as $category)
              <option {{isset($editData) && $editData->category_id == $category->id ? 'selected' :''}} value="{{$category->id}}">{{$category->name}}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12">
            <label class="form-label w-100" for="amount">Amount</label>
            <input required id="amount" name="amount" class="form-control" type="number" name="name" value="{{isset($editData) ? $editData->amount :''}}" />
          </div>
          <div class="col-12">
            <label class="form-label" for="method">Method</label>
            <select id="method" required name="method" class="form-control" onchange="toggleTransactionIdField(this)">
              <option value="">Please Select One</option>
              <option {{isset($editData) && $editData->method == 'Cash' ? 'selected' :''}} value="Cash">Cash</option>
              <option {{isset($editData) && $editData->method == 'Bkash' ? 'selected' :''}} value="Bkash">Bkash</option>
            </select>
          </div>
          @if(isset($editData) && $editData->transaction_id)
          <div class="col-12">
            <label class="form-label w-100" for="transaction">Transaction Id</label>
            <input id="transaction" name="transaction" class="form-control" type="text" name="name" value="{{isset($editData) ? $editData->transaction_id :''}}" />
          </div>
          @endif
          <div id="transaction_id_field" class="col-12 d-none">
            <label class="form-label w-100" for="transaction">Transaction Id</label>
            <input id="transaction" name="transaction" class="form-control" type="text" name="name" value="{{isset($editData) ? $editData->transaction_id :''}}" />
          </div>
          <div class="col-12">
            <label class="form-label w-100" for="date">Date</label>
            <input id="date" required name="date" class="form-control" type="datetime-local" name="name" value="{{isset($editData) ? $editData->date :''}}" />
          </div>
          <div class="col-12">
            <label class="form-label w-100" for="description">Description</label>
            <textarea required name="description" id="description" class="form-control">{{isset($editData) ? $editData->description :''}}</textarea>
          </div>
          <div class="col-12 text-center">
            <button onclick="return confirm('are you sure to submit it')" type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
            @if($errors->any())
            <a href='{{route("account-daily-expenses")}}' class="btn btn-secondary">Close</a>
            @else
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            @endif
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