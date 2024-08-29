@extends('layouts/layoutMaster')
@section('title') {{'olt'}} @endsection
@section('content')
<div class="row">
  <div class="col-8 m-auto">
    <!-- Basic Layout -->
    <div class="card mb-4">
      <!-- <div class="card-header"> -->
      <h3 class="text-center">Assign <span class="{{request('assign_from')=='olt'? 'text-uppercase':'text-capitalize'}}">{{request('assign_from')}}</span></h3>
      <!-- </div> -->
      <div class="card-body">
        <form action="{{route('storeOltassignManager')}}" method="POST">
          @csrf
          @method('POST')
          @if(request('assign_from') == 'olt')
          <div class="">
            <label class="form-label" for="name">Olt</label>
            @if($errors->has('olt_id'))<span class="text-danger"> {{$errors->first('olt_id')}}</span> @endif
            <input type="hidden" name="assign_from" value="{{request('assign_from')}}">
            <select id="type" name="olt_id" class="form-select field_2_select2 {{request('assign_from') == 'olt' ? 'bg-light':''}}">
              <option selected value="{{$olt->id}}">{{$olt->name}}</option>
            </select>
            <label class="form-label" for="pon">Manager</label>
            @if($errors->has('manager_id'))<span class="text-danger"> {{$errors->first('manager_id')}}</span> @endif
            <select multiple id="type" name="manager_id[]" class="select2 form-select">
              <option value="">Please Select One</option>
              @foreach($manager as $m_item)
              <option {{ is_array($old_value->pluck('manager_id')->toArray()) && in_array($m_item->id, $old_value->pluck('manager_id')->toArray()) ? 'selected' : '' }} value="{{$m_item->id}}">{{$m_item->name}}</option>
              @endforeach
            </select>
          </div>
          @endif
          @if(request('assign_from') == 'manager')
          <div class="">
            <label class="form-label" for="pon">Manager</label>
            @if($errors->has('manager_id'))<span class="text-danger"> {{$errors->first('manager_id')}}</span> @endif
            <select id="type" name="manager_id" class="form-control bg-light">
              <option value="{{$manager->id}}">{{$manager->name}}</option>
            </select>
            <label class="form-label" for="name">Olt</label>
            @if($errors->has('olt_id'))<span class="text-danger"> {{$errors->first('olt_id')}}</span> @endif
            <input type="hidden" name="assign_from" value="{{request('assign_from')}}">
            <select id="type" name="olt_id[]" multiple class="select2 form-select">
              <option value="">Please Select One</option>
              @foreach($olt as $olt_item)
              <option {{ is_array($old_value->pluck('olt_id')->toArray()) && in_array($olt_item->id, $old_value->pluck('olt_id')->toArray()) ? 'selected' : '' }} value="{{$olt_item->id}}">{{$olt_item->name}}</option>
              @endforeach
            </select>
          </div>
          @endif
          <div class="mt-3">
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="{{route('olt.index')}}" class="btn btn-warning">Back</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
@push('pricing-script')
<script type="text/javascript">
  function openConfirmation(url) {
    document.querySelector('#submit_btn').setAttribute("href", url);;
    $("#submitConfirmLinkModal").modal('show')
  }
</script>
@endpush