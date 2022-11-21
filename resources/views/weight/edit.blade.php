@extends('layouts.app')

@section('title', 'Edit Patient Weight')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Weight</h1>
        <a href="{{route('weight.index')}}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                class="fas fa-arrow-left fa-sm text-white-50"></i> Back</a>
    </div>

    {{-- Alert Messages --}}
    @include('common.alert')
   
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Weight</h6>
        </div>
        <form method="POST" action="{{route('weight.update', ['weight' => $weight->id])}}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">

                {{-- Pharmacy --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Select Patient</label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <select class="form-control" name="patient_id" id="select_patient">
                                    @foreach ($patients as $patient)
                                        @if($patient->id == $weight->patient_id)
                                            <option id="{{$patient->token}}" value="{{$patient->id}}" selected>{{$patient->first_name}} {{$patient->last_name}}</option>
                                        @else
                                            <option id="{{$patient->token}}" value="{{$patient->id}}">{{$patient->first_name}} {{$patient->last_name}}</option>
                                        @endif
                                    @endforeach
                                </select> 
                            </div>
                        </div>
                    </div>

                    {{-- Token --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Token</label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                            <input 
                            type="number" 
                            class="form-control" 
                            id="txt_token"
                            name="token" readonly>
                            </div>
                        </div>  
                    </div>

                    {{-- Weight --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Weight<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                            <input
                            type="number" 
                            class="form-control @error('weight') is-invalid @enderror" 
                            id="txt_weight"
                            placeholder="eg - 102" 
                            name="weight" 
                            value="{{ old('weight') ?  old('weight') : $weight->weight}}">

                            @error('weight')
                                <span class="text-danger">{{$message}}</span>
                            @enderror
                            </div>
                        </div>  
                    </div>


                {{-- Expire Date --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Created Time<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <input
                                    type="text" 
                                    class="datepicker form-control @error('created_time') is-invalid @enderror" 
                                    id="txt_date"
                                    placeholder="d-m-y" 
                                    name="created_time" 
                                    value="{{ old('created_time') ?  old('created_time') : $weight->created_time->format('d-m-Y') }}" >
                                    @error('created_time')
                                        <span class="text-danger">{{$message}}</span>
                                    @enderror
                            </div>

                            <div class="col-sm-1 mb-3 mt-3 mb-sm-0" style="margin-left:-23px;">
                                <img class="datepicker-open" src="{{asset('/plugin/jqueryui-1.13/images/calendar.png')}}" width="41px;" alt="">
                            </div>
                        
                        </div>
                            
                        </div>

                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-success btn-user float-right mb-3">Update</button>
                <a class="btn btn-primary float-right mr-3 mb-3" href="{{ route('weight.index') }}">Cancel</a>
            </div>
        </form>
    </div>

</div>

@endsection
@section('scripts')

<script>
    $(function () {
        $('#select_patient').select2({
            //minimumInputLength: 3
        });

        let token = $("#select_patient").children(":selected").attr('id');
        $("#txt_token").val(token);
        
        $("#txt_date").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd-mm-yy',
            yearRange: ':+20',
            onSelect: function (value, ui) {
            }
        });

    });

    $('.datepicker-open').click(function(event) {
        event.preventDefault();
        $('.datepicker').focus();
    });

    $('#select_patient').on('select2:select', function (e) {
        let token = $(this).children(":selected").attr('id');
        $("#txt_token").val(token);
    });

</script>
<style>

</style>
@endsection