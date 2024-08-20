@extends('layouts/layoutMaster')
@section('title') View Ticket @endsection
@section('content')
<div class="card">
    <div class="card-body">
        <div class="text-center mb-1">
            <h3 class="mb-2 text-capitalize">View Ticket</h3>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-6 m-auto">
                <table class="table border">
                    <tbody>
                        <tr>
                            <td>Name</td>
                            <td>:</td>
                            <td>{{$data->name}}</td>
                        </tr>
                        <tr>
                            <td>Phone</td>
                            <td>:</td>
                            <td>{{$data->phone}}</td>
                        </tr>
                        <tr>
                            <td>Priority</td>
                            <td>:</td>
                            <td>{{$data->priority}}</td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>:</td>
                            <td><div class="text-white text-capitalize badge {{ $data->status == 'pending' ? 'bg-warning' : ($data->status == 'processing' ? 'bg-primary' : 'bg-success') }}">{{$data->status}}</div></td>
                        </tr>
                        <tr>
                            <td>Issue Date</td>
                            <td>:</td>
                            <td>{{$data->created_at}}</td>
                        </tr>
                        <tr>
                            <td>Created By</td>
                            <td>:</td>
                            <td>{{$data->manager->name}}</td>
                        </tr>
                        @if($data->status == 'completed')
                        <tr>
                            <td>Solved Date</td>
                            <td>:</td>
                            <td>{{$data->updated_at}}</td>
                        </tr>
                        <tr>
                            <td>Solved By</td>
                            <td>:</td>
                            <td>{{$data->manager->name}}</td>
                        </tr>
                        @endif
                        <tr>
                            <td>Ticket Category</td>
                            <td>:</td>
                            <td>{{$data->ticket_category->name}}
                                <br>
                                <small>Priority : {{$data->ticket_category->priority}}</small>
                            </td>
                        </tr>
                        <tr>
                            <td>Note</td>
                            <td>:</td>
                            <td>{{$data->note}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
