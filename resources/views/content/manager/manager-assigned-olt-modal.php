<!-- Add New Credit Card Modal -->
<div class="modal fade" id="addRoleToManagerModal_{{$manager->id}}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered1 modal-simple modal-add-new-cc">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2">Assign Role</h3>
        </div>
        <form action="{{route('managers-add-role-to-manager', $manager->id)}}" class="row g-3" method="POST">
          @csrf
          <div class="col-12">
            <label class="form-label" for="role_id">Role</label>
            <select id="role_id" name="role_id" class="form-control">
              <option value="">Please Select One</option>
              @foreach($roles as $role)
              <option value="{{$role->id}}">{{$role->name}}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
            <button type="reset" class="btn btn-label-secondary btn-reset" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>

        </form>


      </div>
    </div>
  </div>
</div>