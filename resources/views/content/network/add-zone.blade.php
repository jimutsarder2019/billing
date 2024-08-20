@extends('layouts/layoutMaster')


@section('title', 'Add Zone')
@section('content')
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Network/</span> Add Zone</h4>

    <!-- Basic Layout -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('network-store-zone') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">Zone Name <b class="text-danger">*</b></label>
                    @if ($errors->has('name'))
                        <span class="text-danger"> {{ $errors->first('name') }}</span>
                    @endif
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}"
                        placeholder="Zone Name" />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="abbr">Zone Abbreviation</label>
                    @if ($errors->has('abbr'))
                        <span class="text-danger"> {{ $errors->first('abbr') }}</span>
                    @endif
                    <input type="text" class="form-control" id="abbr" name="abbr" value="{{ old('abbr') }}"
                        placeholder="Zone Abbreviation" />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="Upazila">Upazila <b class="text-danger">*</b></label>
                    @if ($errors->has('upazila'))
                        <span class="text-danger"> {{ $errors->first('upazila') }}</span>
                    @endif
                    <select id="Upazila" name="upazila" class="select2 form-select" aria-placeholder="Upazila">
                        <option value="" selected>Select</option>
                        @foreach ($upazila as $u_item)
                            <option {{ old('upazila') == $u_item->id ? 'selected' : '' }} value="{{ $u_item->id }}">
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
