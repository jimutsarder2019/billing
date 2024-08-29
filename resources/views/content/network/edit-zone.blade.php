@extends('layouts/layoutMaster')
@section('title', 'Edit Zone')
@section('content')
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Network/</span> Edit Zone</h4>
    <!-- Basic Layout -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('network-update-zone', $zone->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="name">Zone Name</label>
                    @if ($errors->has('name'))
                        <span class="text-danger"> {{ $errors->first('name') }}</span>
                    @endif
                    <input type="text" class="form-control" id="name" name="name" placeholder="Zone Name"
                        value="{{ $zone->name }}" />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="abbr">Zone Abbreviation <b class="text-danger">*</b></label>
                    @if ($errors->has('abbr'))
                        <span class="text-danger"> {{ $errors->first('abbr') }}</span>
                    @endif
                    <input type="text" class="form-control" id="abbr" name="abbr" placeholder="Zone Abbreviation"
                        value="{{ $zone->abbreviation }}" />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="Upazila">Upazila <b class="text-danger">*</b></label>
                    @if ($errors->has('upazila'))
                        <span class="text-danger"> {{ $errors->first('upazila') }}</span>
                    @endif
                    <select id="Upazila" name="upazila" class="select2 form-select">
                        <option selected>Select</option>
                        @foreach ($upazila as $u_item)
                            <option {{ $u_item->id == $zone->upazila_id ? 'selected' : '' }} value="{{ $u_item->id }}">
                                {{ $u_item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href='{!! URL::previous() !!}' class="btn btn-warning">Back</a>

            </form>
        </div>
    </div>
@endsection
