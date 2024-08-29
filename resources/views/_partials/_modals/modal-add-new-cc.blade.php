<!-- Add New Credit Card Modal -->
<div class="modal fade" id="addNewCCModal_{{$id}}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered1 modal-simple modal-add-new-cc">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2">Sync Mikrotik</h3>
        </div>
        <div class="text-center">
          <form class="mt-3" action="{{route('mikrotik-sync-mikrotik', $id)}}" method="POST">
            @csrf
            @method('GET')
            <select name="type" id="type" style="display:none;">
              <option value="1" selected>Sync IP Pool</option>
            </select>
            <button class="btn btn-sm btn-primary" type="submit">Sync IP Pool</button>
          </form>

          <form class="mt-3" action="{{route('mikrotik-sync-mikrotik', $id)}}" method="POST">
            @csrf
            @method('GET')
            <select name="type" id="type" style="display:none;">
              <option value="2" selected>Sync Profile</option>
            </select>
            <button class="btn btn-sm btn-primary" type="submit">Sync Profile</button>
          </form>

          <form class="mt-3" action="{{route('mikrotik-sync-mikrotik', $id)}}" method="POST">
            @csrf
            @method('GET')
            <select name="type" id="type" style="display:none;">
              <option value="3" selected>Sync User</option>
            </select>
            <button class="btn btn-sm btn-primary" type="submit">Sync User</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<!--/ Add New Credit Card Modal -->