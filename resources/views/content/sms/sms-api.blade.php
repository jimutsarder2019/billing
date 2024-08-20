@extends('layouts/layoutMaster')
@section('title') {{'SMS API'}} @endsection
@section('content')

<div class="card">
  <div class="card-body">
    <h5 class="card-title mb-3">SMS APi</h5>
    @can('SMS Api Add')
    <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addNewApiModal">Add New</button>
    @endcan

    <div class="row">
      @if($sms_api->count() > 0)
      <div class="border p-4 col-sm-12 col-md-6">
        <div>Name: {{$sms_api[0]->name}}</div>
        <div>API URL: {{$sms_api[0]->api_url}}</div>
        <div>API KEY: {{$sms_api[0]->api_key}}</div>
        <div>Sender ID: {{$sms_api[0]->sender_id}}</div>
        <div>Client ID: {{$sms_api[0]->client_id}}</div>
        <button id="check_balance_btn_{{$sms_api[0]->id}}" onclick="check_balance({{$sms_api[0]->id}})" class="btn btn-sm btn-primary mt-3">Check Balance</button>
      </div>
      @endif
      @if( $sms_api->count() > 1)
      <div class="border p-4 col-sm-12 col-md-6" style="border-left: 0 !important;">
        <div>Name: {{$sms_api[1]->name}}</div>
        <div>API URL: {{$sms_api[1]->api_url}}</div>
        <div>API KEY: {{$sms_api[1]->api_key}}</div>
        <div>Secret Key: {{$sms_api[1]->sender_id}}</div>
        <div>Caller ID: {{$sms_api[1]->client_id}}</div>
        <button id="check_balance_btn_{{$sms_api[1]->id}}" onclick="check_balance({{$sms_api[1]->id}})" class="btn btn-sm btn-primary mt-3">Check Balance</button>
      </div>
      @endif
    </div>
  </div>
</div>
@include('content/sms/add-sms-api-modal')
@endsection

<script script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js" integrity="sha512-uMtXmF28A2Ab/JJO2t/vYhlaa/3ahUOgj1Zf27M5rOo8/+fcTUVH0/E0ll68njmjrLqOBjXM3V9NiPFL5ywWPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
  function check_balance(api_id) {
    let get_btn_content = document.getElementById(`check_balance_btn_${api_id}`);
    get_btn_content.innerText = 'Loading...'
    let app_url = document.head.querySelector('meta[name="app_url"]').content;
    axios.get(`${app_url}/check-balance?api_id=${api_id}`).then((resp) => {
      if (resp.status === 200) {
        get_btn_content.innerText = resp.data.balance + ' TK'
      }
    });
  }
</script>