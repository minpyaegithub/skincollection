@extends('layouts.app')

@section('title', 'Edit Pharmacy')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Appointment</h1>
        <a href="{{route('appointments.create')}}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fa-solid fa-list fa-sm text-white-50"></i> List </a>
    </div>

    {{-- Alert Messages --}}
    @include('common.alert')
   
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Appointment</h6>
        </div>
        <form method="POST" action="{{route('appointments.update', ['appointment' => $appointment->id])}}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">

                    {{-- Name --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Name<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                            <input 
                            type="text" 
                            class="form-control @error('name') is-invalid @enderror" 
                            id="txt_name"
                            placeholder="Name" 
                            name="name" 
                            value="{{ old('name') ?  old('name') : $appointment->name}}">
                                @error('name')
                                    <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>

                        </div>  
                    </div>

                    {{-- Phone --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Phone<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                            <input 
                            type="number" 
                            class="form-control @error('phone') is-invalid @enderror" 
                            id="txt_phone"
                            placeholder="eg.0912345679" 
                            name="phone" 
                            value="{{ old('phone') ?  old('phone') : $appointment->phone}}">
                                @error('phone')
                                    <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>

                        </div>  
                    </div>

                    {{-- Phone --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Time<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <select class="form-control" style="width: 100%" name="time[]"
                                    id="select_time" multiple>
                                    <?php 
                                        $times = explode(",", $appointment->time);
                                    ?>
                                    @foreach($appointment_times as $appointment_time)
                                    @foreach($times as $time)
                                    @if($time == $appointment_time->time)
                                        <option value="{{$appointment_time->time}}" selected>
                                        {{$appointment_time->custom_time}}</option>
                                    @else
                                        <option value="{{$appointment_time->time}}">
                                        {{$appointment_time->custom_time}}</option>
                                    @endif
                                    @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>  
                    </div>

                    {{-- Phone --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Status<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <select class="form-control" style="width: 100%" name="status" id="status">
                                    @if($appointment->status == 0)
                                        <option value="0" selected>Pending</option>
                                        <option value="1">Finish</option>
                                    @else
                                        <option value="1" selected>Finish</option>
                                        <option value="0">Pending</option>
                                        
                                    @endif
                                </select>
                            </div>
                    </div>
                </div>

                    {{-- Description --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Description<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                            <textarea
                                    type="text" 
                                    class="form-control @error('description') is-invalid @enderror" 
                                    id="txt_description"
                                    placeholder="Description" 
                                    name="description" 
                                    rows="4"
                                    value="">
                                    {{ old('description') ?  old('description') : $appointment->description}}
                                    </textarea>
                                    @error('description')
                                        <span class="text-danger">{{$message}}</span>
                                    @enderror
                            </div>

                        </div>  
                    </div>

                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-success btn-user float-right mb-3">Update</button>
                <a class="btn btn-primary float-right mr-3 mb-3" href="{{ route('appointments.index') }}">Cancel</a>
            </div>
        </form>
    </div>

</div>

@endsection
@section('scripts')

<script>
    $(function () {
        $("#select_time").select2({});
    });
</script>
<style>

</style>
@endsection