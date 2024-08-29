@extends('layouts/layoutMaster')
@section('content')
<?php
$route = 'ticketcategory';
$title = (isset($data) ? 'Edit ' : 'Add ') .  'ticket category'; ?>
@section('title', $title)
@if(isset($data)) @php $form_action = route("$route.update", $data->id); @endphp @else @php $form_action = route("$route.store"); @endphp @endif
<div class="card">
    <div class="card-body">
        <div class="text-center mb-1">
            <h3 class="mb-2 text-capitalize">{{$title}}</h3>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-8 m-auto">
                <form action="{{$form_action}}" class="row g-3" method="POST">
                    @if(isset($data))
                    @method('put')
                    @endif
                    @csrf
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label w-100" for="name">name @if($errors->has('name'))<span class="text-danger"> {{$errors->first('name')}}</span> @endif</label>
                            <input id="name" name="name" @if(isset($data)) value="{{ $data->name }}" @else value="{{ old('name') }}" @endif placeholder="Name" class="form-control" type="text" />
                        </div>
                        <div class="form-group">
                            <label class="form-label w-100" for="division">Priority @if($errors->has('priority'))<span class="text-danger"> {{$errors->first('priority')}}</span> @endif</label>
                            <select name="priority" id="priority" class="form-control">
                                <option value="">Select</option>
                                <option {{isset($data->priority) && $data->priority =='High' ? 'selected' : ''}} value="High">High</option>
                                <option {{isset($data->priority) && $data->priority =='Medium' ? 'selected' : ''}} value="Medium">Medium</option>
                                <option {{isset($data->priority) && $data->priority =='Low' ? 'selected' : ''}} value="Low">Low</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 text-left">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        <a href='{{route("$route.index")}}' class="btn btn-warning">Close</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection