<div class="modal fade" id="addInvoiceModal_{{$user->id}}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered1 modal-simple modal-add-new-cc">
        <div class="modal-content p-3 p-md-5">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mb-4">
                    <h3 class="mb-2">Set Connection Information</h3>
                </div>
                <form action="{{route('set-new-user-in-mikrotik', $user->id)}}" class="row g-3" method="POST" autocomplete="off">
                    @method('put')
                    @csrf
                    <div class="col-12 text-center mt-2">
                        Package: {{$user->package->name}} <br>
                        Bill: {{$user->package->price}} TK<br>
                        User Discount: {{$user->discount}} TK <br>
                        Name: {{$user->full_name}}
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label" for="mikrotik">Mikrotik</label>
                        <select required id="mikrotik" name="mikrotik" class="form-control">
                            <option value="">Please Select One</option>
                            @foreach($mikrotik as $mkt_item)
                            <option value="{{$mkt_item->id}}">{{$mkt_item->identity}} | {{$mkt_item->host}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label" for="mikrotik_user_name">MikroTik User Name</label>
                        <input type="text" name="mikrotik_user_name" autocomplete="off" placeholder="User Name" required class="form-control">
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label">MikroTik Password</label>
                        <input required type="text" name="password" class="form-control" placeholder="MikroTik Password" autocomplete="off">
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label w-100" for="expire_date">Package</label>
                        @if($errors->has('package_id'))<span class="text-danger"> {{$errors->first('package_id')}}</span> @endif
                        @if(auth()->user()->type == FRANCHISE_MANAGER)
                        <?php
                        $assigned_package = App\Models\ManagerAssignPackage::with('package')->where('manager_id', auth()->user()->id)->get();
                        ?>
                        <select id="package_id" name="package_id" class="form-control" onchange="addPriceToBillField()">
                            <option value="">Please Select One</option>
                            @foreach($assigned_package as $asg_pkg)
                            <option {{old('package_id') == $asg_pkg->package->id ? 'selected' :''}} value="{{$asg_pkg->package->id}}">{{$asg_pkg->package->name}} | {{$package->franchise_price ?? '0'}}TK</option>
                            @endforeach
                        </select>
                        @else
                        <select id="package_id" name="package_id" class="form-control" onchange="addPriceToBillField()">
                            <option value="">Please Select One</option>
                            <?php $packages = App\Models\Package::select('id', 'name', 'price', 'franchise_price')->get() ?>
                            @foreach($packages as $package)
                            <option {{$user->package_id == $package->id ?'selected' :''}} value="{{$package->id}}">{{$package->name}} | {{$package->price ?? '0'}} TK</option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                    <div class="col-12 mt-2">
                        <!-- <label class="form-label w-100" for="amount">Total Amount (Discount:{{$user->discount??0}}TK)<span data-bs-toggle="tooltip" data-bs-title="Bill After Package Price - Discount" class="ms-2"><i class="bi bi-info-circle"></i></span></label> -->
                        <label class="form-label w-100" for="amount">Total Amount</label>
                        <input id="amount" name="amount" readonly value="{{$user->bill}}" class="form-control" type="text" />
                    </div>
                    <div class="col-12 text-center mt-4">
                        <button onclick="return confirm('are you sure to Save It!')" id="submit_btn" type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        <button type="reset" class="btn btn-label-secondary btn-reset" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js" integrity="sha512-uMtXmF28A2Ab/JJO2t/vYhlaa/3ahUOgj1Zf27M5rOo8/+fcTUVH0/E0ll68njmjrLqOBjXM3V9NiPFL5ywWPQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
</script>
<script>
    function addPriceToBillField() {
        $("#submit_btn").attr("disabled", "disabled");
        $("#submit_btn").html('Loading...');
        let package_id = document.getElementById('package_id').value;
        let app_url = document.head.querySelector('meta[name="app_url"]').content;
        axios.get(`${app_url}/user/get-package-details/${package_id}`).then((resp) => {
            if (resp.status == 200) {
                $("#submit_btn").removeAttr("disabled");
                $("#submit_btn").html('Submit');
                let bill_field = document.getElementById('amount')
                bill_field.value = resp.data.bill;
            }
        })
    }
</script>