@extends('layouts.app')

@section('title', 'Add Patients')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add Patients</h1>
        <a href="{{route('patients.index')}}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                class="fas fa-arrow-left fa-sm text-white-50"></i> Back</a>
    </div>

    {{-- Alert Messages --}}
    @include('common.alert')
   
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Add New Patient</h6>
        </div>
        <form method="POST" action="{{route('patients.store')}}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="form-group">

                    {{-- Name --}}
                    <div class="form-group">
                        <div class="row" style="margin-bottom:-30px;">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Name<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                            <input 
                            type="text" 
                            class="form-control @error('first_name') is-invalid @enderror" 
                            id="txt_firstname"
                            placeholder="First Name" 
                            name="first_name" 
                            value="{{ old('first_name') }}">
                                @error('first_name')
                                    <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>

                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <input 
                                type="text" 
                                class="form-control @error('last_name') is-invalid @enderror" 
                                id="txt_lastname"
                                placeholder="Last Name" 
                                name="last_name" 
                                value="{{ old('last_name') }}">

                                    @error('last_name')
                                        <span class="text-danger">{{$message}}</span>
                                    @enderror
                            </div>

                        </div>  
                    </div>

                    {{-- Last Name --}}
                    <div class="form-group">
                        <div class="row">
                            
                        </div>  
                    </div>

                    {{-- Email --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Email<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                            <input 
                            type="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            id="txt_email"
                            placeholder="example@gmail.com" 
                            name="email" 
                            value="{{ old('email') }}">

                            @error('email')
                                <span class="text-danger">{{$message}}</span>
                            @enderror
                            </div>
                        </div>  
                    </div>

                    {{-- Mobile Number --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Mobile Number<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                            <input 
                            type="number" 
                            class="form-control @error('phone') is-invalid @enderror" 
                            id="txt_phone"
                            placeholder="Mobile Number" 
                            name="phone" 
                            value="{{ old('phone') }}">

                            @error('phone')
                                <span class="text-danger">{{$message}}</span>
                            @enderror
                            </div>
                        </div>  
                    </div>

                    {{-- Gender --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Gender<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <select id="gender" class="form-control @error('gender') is-invalid @enderror" name="gender">
                                <option selected disabled>Select Gender</option>
                                    <option id="txt_male" name="male" value="Male" {{ old('gender') == 'Male' ? "selected" : "" }}>Male</option>
                                    <option id="txt_female" name="female" value="Female" {{ old('gender') == 'Female' ? "selected" : "" }}>Female</option>
                                </select>
                                @error('gender')
                                    <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                        </div>  
                    </div>

                    {{-- DOB --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Date of Birth<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <input
                                    type="text" 
                                    class="datepicker form-control @error('dob') is-invalid @enderror" 
                                    id="txt_date"
                                    placeholder="d-m-y" 
                                    name="dob" 
                                    value="{{ old('dob') }}">
                                    <span id="lblError" style = "color:Red"></span>
                                    @error('dob')
                                        <span class="text-danger">{{$message}}</span>
                                    @enderror
                            </div>

                            <div class="col-sm-1 mb-3 mt-3 mb-sm-0" style="margin-left:-23px;">
                                <img class="datepicker-open" src="{{asset('plugin/jqueryui-1.13/images/calendar.png')}}" width="41px;" alt="">
                            </div>
                        
                        </div>
                            
                        </div>

                    {{-- address --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Address<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <input
                                    type="text" 
                                    class="datepicker form-control @error('address') is-invalid @enderror" 
                                    id="txt_address"
                                    placeholder="address" 
                                    name="address" 
                                    value="{{ old('address') }}">
                                    @error('address')
                                        <span class="text-danger">{{$message}}</span>
                                    @enderror
                            </div>
                        </div>  
                    </div>

                    {{-- weight --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Weight<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <input
                                    type="number" 
                                    class="datepicker form-control @error('weight') is-invalid @enderror" 
                                    id="txt_weight"
                                    placeholder="eg. 102" 
                                    name="weight" 
                                    value="{{ old('weight') }}">
                                    
                                    @error('weight')
                                        <span class="text-danger">{{$message}}</span>
                                    @enderror
                            </div>
                            <div class="col-sm-1 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:6px;">lbs *</label>
                            </div>
                        </div>  
                    </div>

                    {{-- height --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Height<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <input
                                    type="number" 
                                    class="datepicker form-control @error('feet') is-invalid @enderror" 
                                    id="txt_feet"
                                    placeholder="feet" 
                                    name="feet"
                                    value="{{ old('feet') }}">
                                    @error('feet')
                                        <span class="text-danger">{{$message}}</span>
                                    @enderror
                            </div>
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                            <input
                                    type="number" 
                                    class="datepicker form-control @error('inches') is-invalid @enderror" 
                                    id="txt_inches"
                                    placeholder="inches" 
                                    name="inches"
                                    value="{{ old('inches') }}">
                                    @error('inches')
                                        <span class="text-danger">{{$message}}</span>
                                    @enderror
                            </div>
                        </div>  
                    </div>

                    {{-- disease --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Disease<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <textarea
                                    type="text" 
                                    class="form-control @error('disease') is-invalid @enderror" 
                                    id="txt_disease"
                                    placeholder="Disease" 
                                    name="disease" 
                                    rows="4"
                                    value="">
                                    {{ old('disease') }}
                                    </textarea>
                                    @error('disease')
                                        <span class="text-danger">{{$message}}</span>
                                    @enderror
                            </div>
                        </div>  
                    </div>

                    {{-- photo --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Photo<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-6 mb-3 mt-3 mb-sm-0">
                                <!-- <form action="url" enctype="multipart/form-data"> -->
                                    <div class="input-images"></div>
                                <!-- </form> -->
                            </div>
                        </div>  
                    </div>

                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-success btn-user float-right mb-3">Save</button>
                <a class="btn btn-primary float-right mr-3 mb-3" href="{{ route('patients.index') }}">Cancel</a>
            </div>
        </form>
    </div>

</div>

@endsection
@section('scripts')

<script>
    $(function () {
        $("#txt_date").datepicker({
            changeMonth: true,
            changeYear: true,
            // showOn: 'button',
            //buttonImageOnly: true,
            //buttonImage: 'images/calendar.gif',
             dateFormat: 'dd-mm-yy',
             yearRange: '1900:+0',
            onSelect: function (value, ui) {
                var today = new Date();
                age = today.getFullYear() - ui.selectedYear;
                $("#lblError").html('Age is : ' + age + ' Year');
                //ValidateDOB(dateString);
            }
        }).datepicker("setDate", 'now');

        $('.input-images').imageUploader();

    });

    $('.datepicker-open').click(function(event) {
        event.preventDefault();
        $('.datepicker').focus();
    });
</script>
<style>

</style>
@endsection