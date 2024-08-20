@extends('layouts/layoutMaster')
@section('title') {{'Transfer Account Balance'}} @endsection
@section('content')
<div class="card">
  <div class="card-body">
    <div class="text-center mb-1">
      <h3 class="mb-2">Transfer Amount</h3>
      <h4 class="mb-2">Name: {{$manager->name}}</h4>
      <p>Avaiable Balance: {{$manager->wallet}}</p>
    </div>
    <div class="row">
      <div class="col-sm-12 col-md-6 m-auto">
        <form action="{{route('manager-balance-transfer.put', $manager->id)}}" class="row g-3" method="POST">
          @method('put')
          @csrf
          <div class="col-12">
            <label class="form-label" for="reciver_id">Managers</label>
            <select id="reciver_id" name="reciver_id" class="select2 form-select">
              <option value="">Please Select One</option>
              @foreach($allmanagers as $manager_item)
              @if($manager_item->id !== auth()->user()->id && $manager_item->id !== $manager->id && $manager_item->type  == APP_MANAGER)
              <option value="{{$manager_item->id}}">{{$manager_item->name}}</option>
              @endif
              @endforeach
            </select>
          </div>
          <div class="col-12">
            <label class="form-label w-100" for="amount">Amount</label>
            <input id="amount" name="amount" class="form-control" type="text" />
          </div>
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Send</button>
            <button type="reset" class="btn btn-label-secondary btn-reset" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>

        </form>
      </div>
    </div>

  </div>
</div>
@endsection