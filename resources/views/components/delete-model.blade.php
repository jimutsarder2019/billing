<div class="modal fade modal-sm" id="deleteConfirmModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-center" style="display: block;">
                <h4 class="text-center fs-5 pb-0 text-danger">Delete Confirmation</h4>
                <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
            </div>
            <form action="{{isset($url)?$url:''}}" method="POST" id="delete_form" style="margin: 0 !important;">
                <?php
                // Parse the URL
                 $urlComponents = isset($url) ? parse_url($url) : [];
                // Extract query string parameters
                $queryParameters = [];
                if (isset($urlComponents['query'])) {
                    parse_str($urlComponents['query'], $queryParameters);
                }
                ?>
                @if(isset($queryParameters['method']))
                @else
                @endif
                @method('DELETE')
                @csrf
                <div class="modal-body text-center p-1">
                    <span>Are You Sure to delete</span>
                </div>
                <div class="modal-footer justify-content-center">
                    <button id="" type="submit" class="btn btn-danger">Yes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                </div>
            </form>
        </div>
    </div>
</div>