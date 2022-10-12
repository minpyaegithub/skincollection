@extends('layouts.app')

@section('title', 'Add Appointment')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add Appointment</h1>
        <a href="{{route('appointments.index')}}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                class="fas fa-arrow-left fa-sm text-white-50"></i> Back</a>
    </div>

    {{-- Alert Messages --}}
    @include('common.alert')

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Add New Appointment</h6>
        </div>
        <form method="POST" action="{{route('appointments.store')}}">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    {{-- Appointment Date --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <input type="text"
                                    class="datepicker form-control @error('appointment_date') is-invalid @enderror"
                                    id="txt_date" placeholder="Appointment Date" name="appointment_date"
                                    value="{{ old('appointment_date') }}">
                                @error('appointment_date')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>

                            <div class="col-sm-1 mb-3 mt-3 mb-sm-0" style="margin-left:-23px;">
                                <img class="datepicker-open" src="{{asset('plugin/jqueryui-1.13/images/calendar.png')}}"
                                    width="41px;" alt="">
                            </div>

                        </div>

                    </div>

                    <div class="form-group" id="appointment_div">

                    </div>

                    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Create Appointment</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form>
                                <div class="form-group">
                                    <label for="name" class="col-form-label">Name:</label>
                                    <input type="text" class="form-control" id="name">
                                </div>
                                <div class="form-group">
                                    <label for="phone" class="col-form-label">Phone:</label>
                                    <input type="number" class="form-control" id="phone">
                                </div>
                                    <input type="hidden" class="form-control" id="time">
                                <div class="form-group">
                                    <label for="description" class="col-form-label">Description:</label>
                                    <textarea class="form-control" id="description"></textarea>
                                </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" id="btn_save"> Save </button>
                            </div>
                            </div>
                        </div>
                        </div>

                </div>
            </div>
        </form>
    </div>

</div>

@endsection
@section('scripts')

<script>
$(function() {
    var html = '<div class="row">'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="8:00AM" value="8:00AM">'+  
                            '<span>8:00 AM</span> '+
                    ' </div>'+                   
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="8:30AM" value="8:30AM">'+
                            '<span>8:30 AM</span> '+
                    '</div>'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="9:00AM" value="9:00AM">'+
                            '<span>9:00 AM</span> '+ 
                    '</div>'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="9:30AM" value="9:30AM">'+
                            '<span>9:30 AM</span> '+
                    '</div>'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="10:00AM" value="10:00AM">'+
                            '<span>10:00 AM</span> '+
                    '</div>'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="10:30AM" value="10:30AM">'+
                            '<span>10:30 AM</span> '+
                    '</div>'+

                ' </div>'+
                '<div class="row">'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="11:00AM" value="11:00AM">'+  
                            ' <span>11:00 AM</span>'+ 
                    '</div> '+                  
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="11:30AM" value="11:30AM">'+
                            '<span>11:30 AM</span>'+ 
                    ' </div>'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="12:00PM" value="12:00PM">'+
                            '<span>12:00 PM</span> '+
                    '</div>'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="12:30PM" value="12:30PM">'+
                            ' <span>12:30 PM</span>'+ 
                    '</div>'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="1:00PM" value="1:00PM">'+
                            '<span>1:00 PM</span> '+
                    '</div>'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="1:30PM" value="1:30PM">'+
                            '<span>1:30 PM</span>'+ 
                    '</div>'+

                ' </div>'+
                '<div class="row">'+
                    ' <div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="2:00PM" value="2:00PM"> '+ 
                            '<span>2:00 PM</span> '+
                    ' </div> '+                  
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="2:30PM" value="2:30PM">'+
                            '<span>2:30 PM</span> '+
                    '</div>'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="3:00PM" value="3:00PM">'+
                            '<span>3:00 PM</span>'+ 
                    '</div>'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="3:30PM" value="3:30PM">'+
                            '<span>3:30 PM</span>'+
                    '</div>'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="4:00PM" value="4:00PM">'+
                            '<span>4:00 PM</span>  '+
                    '</div>'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="4:30PM" value="4:30PM">'+
                        '<span>4:30 PM</span>'+
                    '</div>'+

                '</div>'+
                '<div class="row">'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="5:00PM" value="5:00PM">'+  
                            '<span>5:00 PM</span> '+
                    '</div>  '+                 
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="5:30PM" value="5:30PM">'+
                            '<span>5:30 PM</span> ' +
                    '</div>'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="6:00PM" value="6:00PM">'+
                            '<span>6:00 PM</span> '+
                    ' </div>'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="6:30PM" value="6:30PM">'+
                            '<span>6:30 PM</span>'+
                    '</div>'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="7:00PM" value="7:00PM">'+
                            '<span>7:00 PM</span> '+
                    ' </div>'+
                    '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="7:30PM" value="7:30PM">'+
                        '<span>7:30 PM</span>'+
                    '</div>'+

                '</div>';

    $("#appointment_div").append(html);

    $('div[name="4:00PM"]').css("background-color","blue");
    $("#txt_date").datepicker({
        changeMonth: true,
        changeYear: true,
        // showOn: 'button',
        //buttonImageOnly: true,
        //buttonImage: 'images/calendar.gif',
        dateFormat: 'dd-mm-yy',
        yearRange: ':+20',
        onSelect: function(value, ui) {}
    }).datepicker("setDate", 'now');

    $('div.appointment-box').click(function(){
        var time = $(this).attr('name');
        $("#time").val(time);
        $("#exampleModal").modal('show');
        //alert(va);
    });

    $("#btn_save").click(function() {
        alert(1);
    });


});

$('.datepicker-open').click(function(event) {
    event.preventDefault();
    $('.datepicker').focus();
});
</script>
<style>
    .appointment-box {
        /* width: 300px; */
        border: 2px solid;
        background:green;
        padding: 50px;
        /* margin: 2px; */
        color: white;
        font-size: 20px;
        text-align: center;
    }
</style>
@endsection