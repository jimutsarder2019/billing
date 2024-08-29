@extends('layouts/layoutMaster')
@section('title','View Transfer')
@section('content')
<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col-sm-12 col-md-6 m-auto">
        <h3 class="text-center">View Transfer</h3>
        @if($data->status == 'pending' && $data->reciver_id == auth()->user()->id)
        <form action="{{route('accept_transfer_balance', $data->id)}}" class="row g-3">
          @csrf
          <div class="col-12">
            <label class="form-label w-100" for="amount">Amount</label>
            <input disabled="amount" name="amount" class="form-control" value="{{$data->amount}}" type="text" />
          </div>
          <div class="col-12">
            <label class="form-label w-100" for="recived_amount">recived amount</label>
            <input id="recived_amount" name="recived_amount" class="form-control" max="{{$data->amount}}" min="1" required type="number" />
          </div>
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary">Accept</button>
            <a href="{{route('rejacte_managers_balance_transfer', $data->id)}}" class="btn btn-danger me-sm-3 me-3">Rejected</a>
            <a href="{{URL::previous()}}" class="btn btn-label-secondary btn-reset">Back</a>
          </div>
        </form>
        @else
        <table class="table borderd">
          <thead>
            <tr>
              <th>sender</th>
              <th>Reciver</th>
              <th>Amount</th>
              <th>Received Amount</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{{$data->sender->name}}</td>
              <td>{{$data->receiver->name}}</td>
              <td>{{$data->amount}}</td>
              <td>{{$data->recived_amount}}</td>
              <td>{{$data->status}}</td>
            </tr>
          </tbody>
        </table>
        @endif
      </div>
    </div>

  </div>
</div>
@endsection