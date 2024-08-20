@extends('layouts/layoutMaster')
@php $route = 'settings' @endphp
<style>
    label {
        text-transform: capitalize !important;
    }
</style>
@section('title','Bkash')
@section('content')
<div class="card">
    <div class="card-body">
        <!-- can('setting_view') -->
        @can('Settings edit')
        <form action='' method="POST" enctype="multipart/form-data">
            @endcan
            @csrf
            <div class="row">
                <h3>Bkash</h3>
                <div class="row">
                    <div class="col-sm-12 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="form-label" for="copyright_right">Merchant Number @if($errors->has('copyright_right'))<span class="text-danger"> {{$errors->first('copyright_right')}}</span> @endif</label>
                                    <input type="text" value="" name="copyright_right" id="copyright_right" placeholder="" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="copyright_right">Username @if($errors->has('copyright_right'))<span class="text-danger"> {{$errors->first('copyright_right')}}</span> @endif</label>
                                    <input type="text" value="" name="copyright_right" id="copyright_right" placeholder="" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="copyright_right">Password @if($errors->has('copyright_right'))<span class="text-danger"> {{$errors->first('copyright_right')}}</span> @endif</label>
                                    <input type="text" value="" name="copyright_right" id="copyright_right" placeholder="" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="copyright_right">Status @if($errors->has('copyright_right'))<span class="text-danger"> {{$errors->first('copyright_right')}}</span> @endif</label>
                                    <select name="" id="" class="form-control">
                                        <option value="1">Active</option>
                                        <option value="0">Disabled</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-2"> @can('Settings edit')
                    <input type="submit" disabled value="Save" class="btn btn-outline-primary btn-sm mt-1 float-right">
                    @endcan
                </div>
            </div>
            @can('Settings edit')
        </form>
        @endcan
    </div>
</div>
@endsection