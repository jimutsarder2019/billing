@extends('layouts/layoutMaster')
@section('title') Permissions @endsection
@section('content')
<div class="card">
  <div class="card-body">
    <div class="text-center mb-1">
      <h3 class="mb-2 text-capitalize">Role:{{$role->name}}</h3>
    </div>
    <div class="row">
      <div class="col-12">
        <div class="text-center mb-4">
          <h3 class="mb-2">Permissions</h3>
        </div>
        <style>
          .margin-1 {
            margin-top: -1px;
          }
        </style>
        <form action="{{route('permission.update', $role->id)}}" class="row g-3" method="POST">
          @method('put')
          @csrf
          <!-- //header line -->
          <div class="row border py-2">
            <div class="form-group col-3">
              <input type="checkbox" class="form-check-input" id="check-all" {{areAllGroupsAndItemsChecked($permission, $role->permissions) ? 'checked' : ''; }}>
              <label for="check-all">Group</label>
            </div>
            <div class="col-9">
              <span class="ml-4"> Permissions</span>
            </div>
          </div>
          <!-- start dynamic items  -->
          @foreach ($permission->groupBy('group_name') as $group => $items)
          <div class="row border py-2">
            <div class="form-group col-3">
              <input type="checkbox" id="{{ $group }}" class="check-group form-check-input" data-group="{{ $group }}" {{ areAllItemsChecked($items, $role->permissions) ? 'checked' : '' }}>
              <label for="{{ $group }}">{{ $group }}</label>
            </div>
            <div class="col-9">
              @foreach ($items as $item)
              <div class="form-group float-start">
                <input type="checkbox" id="{{ $item['id'] }}" name="permission_id[]" class="check-item ml-4 form-check-input check-item-{{ $group }}" data-group="{{ $group }}" value="{{ $item['id'] }}" {{ isInData($item['id'], $role->permissions) ? 'checked' : '' }}>
                <label for="{{ $item['id'] }}">{{ str_replace('_',' ', str_replace("$group ",'',$item['name']))}}</label>
              </div>
              @endforeach
            </div>
          </div>
          @endforeach
          <?php
          function isInData($itemId, $existingData)
          {
            foreach ($existingData as $existingItem) {
              if ($existingItem['id'] === $itemId) {
                return true;
              }
            }
            return false;
          }


          function areAllItemsChecked($items, $existingData)
          {
            foreach ($items as $item) {
              if (!isInData($item['id'], $existingData)) {
                return false;
              }
            }
            return true;
          }



          function areAllGroupsAndItemsChecked($permission, $existingData)
          {
            foreach ($permission->groupBy('group_name') as $group => $items) {
              if (!areAllItemsChecked($items, $existingData)) {
                return false;
              }
            }
            return true;
          }

          ?>
          <!-- end dynamic item  -->
          <div class="col-12 mt-2 text-center">
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
            <a href='{{route("managers-role-list")}}' class="btn btn-warning">Close</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
@push('pricing-script')

<script>
  // jQuery code for handling the checkbox functionality
  $(document).ready(function() {

    // Check/uncheck all items and groups
    $('#check-all').change(function() {
      $('.check-item').prop('checked', $(this).prop('checked'));
      $('.check-group').prop('checked', $(this).prop('checked'));
    });

    // Check/uncheck the items within a group
    $('.check-item').change(function() {
      var group = $(this).data('group');
      var allItemsChecked = ($('.check-item.check-item-' + group + ':checked').length === $('.check-item.check-item-' + group).length);
      $('.check-group[data-group="' + group + '"]').prop('checked', allItemsChecked);

      // Uncheck "All" if any item is unchecked
      $('#check-all').prop('checked', $('.check-item').not(':checked').length === 0);
    });

    // Check/uncheck the group when all items within the group are checked/unchecked
    $('.check-group').change(function() {
      var group = $(this).data('group');
      $('.check-item.check-item-' + group).prop('checked', $(this).prop('checked'));

      // Uncheck "All" if any group is unchecked
      $('#check-all').prop('checked', $('.check-group').not(':checked').length === 0);
    });
  });
</script>
@endpush