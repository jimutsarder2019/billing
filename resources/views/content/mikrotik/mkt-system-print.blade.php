@extends('layouts/layoutMaster')

@section('title', 'Mikrotik Info')
@section('content')
<div class="row">
  <div class="col-sm-12 col-md-6 m-auto">
    <div class="card">
      <div class="card-body align-self-center">
        <h4>System Resource</h4>
        <table>
          <tbody>
            @foreach($res_data[0] as $key => $r_item)
            <tr>
              <th><span class="text-capitalize text-start float-start">{{str_replace('-',' ', $key)}}</span></th>
              <td>:</td>
              <td><span class="float-start">{{$r_item}}</span></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="align-self-center">
        <a href="{{ url()->previous() }}" type="reset" class="btn btn-label-warning btn-reset my-3">Back</a>
      </div>
    </div>
  </div>
</div>
@endsection