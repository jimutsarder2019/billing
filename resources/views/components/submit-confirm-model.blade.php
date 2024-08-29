<div class="modal fade modal-sm" id="submitConfirmModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="submitConfirmModalLabel" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-center" style="display: block;">
                <h4 class="text-center fs-5 pb-0 text-success">Submit Confirmation</h4>
                <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
            </div>
            <div class="modal-body text-center p-1">
                <span>Are You Sure to Save it</span>
                @if(isset($message)) <br><span class="font-semibold text-{{$message_text_color}}">{{$message}}</span> @endif
            </div>
            <div class="modal-footer justify-content-center">
                <button id="saveData" type="submit" class="btn btn-primary">Yes</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>