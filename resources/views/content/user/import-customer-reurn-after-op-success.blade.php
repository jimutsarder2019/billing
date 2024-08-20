@extends('layouts/layoutMaster')
@php $route = str_replace(['.index'], '', Route::currentRouteName()); @endphp
@section('title') {{$route}} @endsection
@section('content')

<div class="card">
  <div class="card-header border-bottom" style="text-align: justify;">
    <h1 class="text-capitalize">Already Data Exists</h1>
    <h4 class="text-capitalize">This list is not import in database Please check manually</h4>
    @foreach($existsdata as $item)
    <span class="badge bg-label-success mb-1"> {{$item}}</span>
    @endforeach
  </div>
</div>
@endsection