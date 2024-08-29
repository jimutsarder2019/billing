@extends('layouts/layoutMaster')
@section('title') Edit Ticket @endsection
@section('content')
<div class="card">
    <div class="card-body">
        <div class="text-center mb-1">
            <h3 class="mb-2 text-capitalize">Edit Ticket</h3>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-6 m-auto">
                <form action="{{route('ticket.update', $data->id)}}" class="row g-3" method="POST">
                    @method('PUT')
                    @csrf
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label w-100" for="name">name</label>
                            <input id="c_name" name="name" @if(isset($data)) value="{{ $data->name }}" @else value="{{ old('name') }}" @endif placeholder="Name" class="form-control" type="text" />
                            <label class="form-label w-100" for="phone">phone</label>
                            <input id="c_phone" name="phone" @if(isset($data)) value="{{ $data->phone }}" @else value="{{ old('phone') }}" @endif placeholder="Phone" class="form-control" type="text" />
                        </div>
                        <div class="form-group">
                            <label class="form-label w-100" for="division">category @if($errors->has('category'))<span class="text-danger"> {{$errors->first('category')}}</span> @endif</label>
                            <select name="category" id="category" class="form-control">
                                <option value="">------Select Category------</option>
                                <?php $categorys = App\Models\TicketCategory::select('id', 'name', 'priority')->get() ?>
                                @foreach($categorys as $cat_item)
                                <option {{$data->ticket_category_id == $cat_item->id ? 'selected':''}} value="{{$cat_item->id}}">{{$cat_item->name}} | {{$cat_item->priority}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label w-100" for="division">Priority @if($errors->has('priority'))<span class="text-danger"> {{$errors->first('priority')}}</span> @endif</label>
                            <select name="priority" id="priority" class="form-control">
                                <option value="">Select</option>
                                <option {{$data->priority == 'High'? 'selected':''}} value="High">High</option>
                                <option {{$data->priority == 'Medium'? 'selected':''}} value="Medium">Medium</option>
                                <option {{$data->priority == 'Low'? 'selected':''}} value="Low">Low</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label w-100" for="division">Priority @if($errors->has('priority'))<span class="text-danger"> {{$errors->first('priority')}}</span> @endif</label>
                            <textarea name="note" id="" cols="30" rows="5" placeholder="Write Note" class="form-control">{{$data->note}}</textarea>
                        </div>
                    </div>

                    <div class="col-12 text-left">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        <a href='{{route("ticket.index")}}' class="btn btn-warning">Close</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection