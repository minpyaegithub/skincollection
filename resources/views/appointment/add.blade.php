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
        <form method="POST">
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
                        <div class="row">
                            @foreach ($appointment_times as $appointment_time)
                            @if (in_array($appointment_time->time, $appointments))
                            <div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" style="background:red"
                                name="{{$appointment_time->time}}" value="{{$appointment_time->time}}">
                                <span>{{$appointment_time->custom_time}}</span><br>
                                <button type="button" id="btn_view" value="{{$appointment_time->time}}"
                                    class="btn btn-dark btn-sm btn-view">view</button>
                            </div>

                            @else
                            <div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="{{$appointment_time->time}}"
                                value="{{$appointment_time->time}}">
                                <span>{{$appointment_time->custom_time}}</span>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog"
                        aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                                            <span style="display:none;" id="err_name" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="phone" class="col-form-label">Phone:</label>
                                            <input type="number" class="form-control" id="phone">
                                            <span style="display:none;" id="err_phone" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="time" class="col-form-label">Time:</label>
                                            <div class="">
                                                <select class="form-control select2" style="width: 100%" name="time[]"
                                                    id="select_time" multiple>
                                                    @foreach($appointment_times as $appointment_time)
                                                    <option value="{{$appointment_time->time}}">
                                                        {{$appointment_time->custom_time}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
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
              
                      <!-- end appointment create -->
                    <div class="modal fade" id="viewModal" tabindex="-1" role="dialog"
                            aria-labelledby="viewModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="viewModalLabel">View Appointment</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form>
                                            <div class="form-group">
                                                <table id="tbl_view" class="table table-bordered">
                                                    <thead>
                                                            <td>Name</td>
                                                            <td>Phone</td>
                                                            <td>Time</td>
                                                            <td>Description</td>
                                                            <td></td>
                                                    </thead>
                                                    <tbody>

                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end appointment view -->

                </div>
            </div>
        </form>
    </div>

</div>

@endsection
@section('scripts')

<script>
var appointment_times = {!!json_encode($appointment_times -> toArray()) !!};
$(function() {
    $('div.alert').delay(3000).slideUp(300);
    $("#select_time").select2({
        // width: "100%",
    });

    $("#txt_date").datepicker({
        changeMonth: true,
        changeYear: true,
        // showOn: 'button',
        //buttonImageOnly: true,
        //buttonImage: 'images/calendar.gif',
        dateFormat: 'dd-mm-yy',
        yearRange: ':+20',
        onSelect: function(value, ui) {
            appointment_create_structure(value);
        }
    }).datepicker("setDate", 'now');


    $(document).on('click', '.btn-view', function(e) {
        e.stopPropagation();
        $("#viewModal").modal('show');
        var date = $("#txt_date").val();
        var time = $(this).attr('value');
        
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: '{{route('appointments.view')}}',
            data: {date:date, time: time},
            success: function(data) {
                var html = '';
                $("#tbl_view tbody").empty();
                var appointments = data[0];
                for (var appointment of appointments) {
                    html += '<tr value="'+appointment['id']+'">'+
                        '<td>'+ appointment['name'] +'</td>'+
                        '<td>'+appointment['phone'] +'</td>'+
                        '<td>'+appointment['time'] +'</td>'+
                        '<td>'+appointment['description'] +'</td>'+
                        '<td style="display: flex">'+
                            '<a class="btn btn-primary m-2" href="/appointments/edit/'+appointment['id']+'">' +
                                '<i class="fa fa-pen"></i>'+
                            '</a>'+
                            '<button class="btn btn-danger m-2" value="'+appointment['id']+'" id="delete_icon">' +
                                '<i class="fas fa-trash"></i>'+
                            '</button>' +
                        '</td>' +
                        '</tr>';
                }
                $("#tbl_view tbody").append(html);
            }
        });
    });

    $(document).on('click', 'div.appointment-box', function(e) {
        e.preventDefault();
        var time = $(this).attr('name');
        $("#time").val(time);
        $('#select_time').select2().val(time).trigger("change");;
        $("#exampleModal").modal('show');

    });

    $(document).on('click', '#delete_icon', function(e) {
        e.preventDefault();
        $(this).closest('tr').remove();
        var id = $(this).val();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'DELETE',
            url: '/appointments/delete/'+id,
            data: {method: '_DELETE', submit: true},
            success: function(data) {
                console.log(data);
                var date = $("#txt_date").val();
                if (data.message == 'success') {
                    appointment_create_structure(date);
                }

            }
        });

    });

    $("#btn_save").click(function() {
        var name = $("#name").val();
        var date = $("#txt_date").val();
        var phone = $("#phone").val();
        var time = $("#select_time").val();
        var description = $("#description").val();
        var data = {
            name: name,
            date: date,
            phone: phone,
            time: time,
            description: description
        };
        if (name == '') {
            $("#err_name").text('name field is required');
            $("#err_name").show();
            return false;
        } else {
            $("#err_name").hide();
        }

        if (phone == '') {
            $("#err_phone").text('phone field is required');
            $("#err_phone").show();
            return false;
        } else {
            $("#err_phone").hide();
        }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            url: '{{route('appointments.store')}}',
            data: data,
            success: function(data) {
                var html = '';
                console.log(data);
                if (data.message == 'success') {
                    $("#name").val('');
                    $("#phone").val('');
                    $("#select_time").val('');
                    $("#description").val('');
                    appointment_create_structure(date);
                    $("#exampleModal").modal('hide');
                }
                if (data.message == 'duplicate') {
                    $("#err_name").text('User is Already exists!!');
                    $("#err_name").show();
                }

            }
        });
    });


});

function appointment_create_structure(date) {
    console.log(appointment_times);
    $("#appointment_div div.row").empty();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'GET',
        url: '{{route('appointments.list')}}',
        data: {
            date: date
        },
        success: function(data) {
            var html = '';
            var appointments = data[0];
            for (var appointment_time of appointment_times) {
                if (appointments.includes(appointment_time['time'])) {
                    console.log(appointment_time['time']);
                    html +=
                        '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" style="background:red" name="' +
                        appointment_time['time'] + '" value="' + appointment_time['time'] + '">' +
                        '<span>' + appointment_time['custom_time'] + '</span><br>' +
                        '<button type="button" id="btn_view" value="' + appointment_time['time'] +
                        '" class="btn btn-dark btn-sm btn-view">view</button>' +
                        ' </div>';
                } else {
                    html += '<div class="col-sm-2 mb-2 mt-2 mb-sm-0 appointment-box" name="' +
                        appointment_time['time'] + '" value="' + appointment_time['time'] + '">' +
                        '<span>' + appointment_time['custom_time'] + '</span><br>' +
                        '</div>';
                }

            }
            $("#appointment_div div.row").append(html);
        }
    });
}

$('.datepicker-open').click(function(event) {
    event.preventDefault();
    $('.datepicker').focus();
});
</script>
<style>
.appointment-box {
    /* width: 300px; */
    border: 2px solid;
    background: green;
    padding: 50px;
    /* margin: 2px; */
    color: white;
    font-size: 20px;
    text-align: center;
}
</style>
@endsection