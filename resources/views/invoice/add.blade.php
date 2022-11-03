@extends('layouts.app')

@section('title', 'Create Invoice')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Invoice</h1>
        <a href="{{route('invoices.index')}}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                class="fas fa-arrow-left fa-sm text-white-50"></i> Back</a>
    </div>

    {{-- Alert Messages --}}
    @include('common.alert')
   
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">New Invoice</h6>
        </div>
        <form method="POST" action="#">
            @csrf
            <div class="card-body">
                <div class="form-group">

                    {{-- Name --}}
                    <div class="form-group">
                        <div class="row">
                                
                            <div class="col-sm-3">
                            <label style="margin-top:9px;">Patient<span style="color:red;">*</span></label>
                                <select class="select2" name="patient_id" id="select_patient">
                                        <option value="">Select Patient</option>
                                        @foreach ($patients as $patient)
                                            <option value="{{$patient->id}}">{{$patient->first_name}} {{$patient->last_name}}</option>
                                        @endforeach
                                </select>

                                <span style="display:none;" id="err_name" class="text-danger"></span>
                            </div>
                            <div class="col-sm-1"></div>
                            {{-- Date --}}
                            <div class="col-sm-3">
                            <label style="margin-top:9px;">Invoice Date<span style="color:red;">*</span></label>
                            <input type="text" class="datepicker form-control @error('created_time') is-invalid @enderror" id="txt_date" placeholder="d-m-y" 
                                    name="created_time" 
                                    value="{{ old('created_time') }}">
                                    @error('created_time')
                                        <span class="text-danger">{{$message}}</span>
                                    @enderror
                            </div>
                            <div class="col-sm-1 mb-3 mt-3 mb-sm-0" style="margin-left:-23px;">
                                <img class="datepicker-open" src="{{asset('plugin/jqueryui-1.13/images/calendar.png')}}"
                                    width="41px;" style="margin-top: 23px;">
                            </div>
                        </div>  
                    </div>

                    <div class="form-group">
                        <div class="row">
                            {{-- Phone --}}
                            <div class="col-sm-3">
                                <label style="margin-top:9px;">Phone</label>
                                <input type="text" class="form-control" id="txt_phone" placeholder="" name="phone" value="" readonly>
                            </div>
                            <div class="col-sm-1"></div>
                            {{-- Email --}}
                            <div class="col-sm-3">
                                <label style="margin-top:9px;">Email</label>
                                <input type="text" class="form-control" id="txt_email" placeholder="" name="email" value="" readonly>
                            </div>
                        </div>  
                    </div>

                    <div class="form-group">
                        <div class="row">
                            {{-- Address --}}
                            <div class="col-sm-3">
                                <label style="margin-top:9px;">Address</label>
                                <textarea type="text" class="form-control" id="txt_address" name="address" 
                                    rows="4"
                                    value="" readonly>
                                </textarea>
                            </div>
                            <div class="col-sm-1"></div>
                            {{-- Disease --}}
                            <div class="col-sm-3">
                                <label style="margin-top:9px;">Disease</label>
                                <input type="text" class="form-control" id="txt_disease" placeholder="" name="disease" value="" readonly>
                            </div>
                        </div>  
                    </div>

                    {{-- Table --}}
                    <div class="form-group" style="margin-top:52px;">
                        <div>
                            <button type="button" class="btn btn-primary" id="btn_add" style="float:right;margin-top:-43px;">Add</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-12">
                            <table id="tbl_invoice" class="table table-bordered table-striped table-hover" style="width:100%;margin:0 auto;">
                                <thead>
                                    <tr>
                                        <th><p>Treatment</p></th>
                                        <th style="width: 20%"><p>Price</p></th>
                                        <th style="width: 20%"><p>Discount</p></th>
                                        <th style="width: 20%"><p>Sub Total</p></th>
                                        <th style="width:50px"><p>Action</p></th>
                                    </tr>
                                </thead>
                                <tr class="tr_clone">
                                <td>
                                    <select class="items" name="select_treatment" id="select_treatment" style="width:100%;">
                                        <option value="">Select Treatment</option>
                                        @foreach($treatments as $treatment)
                                            <option price={{$treatment->price}} value="{{$treatment->id}}">{{$treatment->name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input class="form-control" placeholder="0" type="number" name="price" id="price" readonly/>
                                </td>
                                <td>
                                    <input class="form-control" placeholder="0" type="number" name="discount"/>
                                </td>
                                <td>
                                    <input class="form-control" placeholder="0" type="number" name="sub_total" readonly/>
                                </td>
                                    <td style="text-align:center;">
                                    <span class="btn btn-danger" id="btn_remove">
                                        <i class="fa fa-remove"></i>
                                    </span>
                                    </td>
                                </tr>
                            </table>
                            </div>
                        </div>  
                    </div>

                </div>
            </div>

            <div class="card-footer">
                <button type="button" id="btn_save" class="btn btn-success btn-user float-right mb-3">Save</button>
                <a class="btn btn-primary float-right mr-3 mb-3" href="{{ route('treatment.index') }}">Cancel</a>
            </div>
        </form>
    </div>

</div>

@endsection
@section('scripts')

<script>
    var patients = {!! json_encode($patients->toArray()) !!};
    var treatments = {!! json_encode($treatments->toArray()) !!};
    $(function () {
        
        $('#select_patient').select2({
            //minimumInputLength: 3
            width: "100%",

        });

        $('#select_patient').on('select2:select', function (e) {
            var data = e.params.data;
            if(data.id == ''){
                $("#txt_phone").val('');
                $("#txt_email").val('');
                $("#txt_address").val('');
                $("#txt_disease").val('');
            }else{
                for (var patient of patients) {
                    if(patient['id'] == data.id){
                        $("#txt_phone").val(patient['phone']);
                        $("#txt_email").val(patient['email']);
                        $("#txt_address").val(patient['address']);
                        $("#txt_disease").val(patient['disease']);

                    }
                }
            }
            
        });

        $('select.items').select2();

       // addrow();

        $("#txt_date").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'dd-mm-yy',
            yearRange: ':+20',
            onSelect: function (value, ui) {
            }
        }).datepicker("setDate", 'now');

        $('.datepicker-open').click(function(event) {
            event.preventDefault();
            $('.datepicker').focus();
        });

        $("#btn_add").on("click", function() {
            //addrow();
            $(".items").select2("destroy");
            var $tr = $('#tbl_invoice tr').last();
            var $clone = $tr.clone();
            $clone.find(':text').val('');
            $clone.find(':input').val('');
            $tr.after($clone);
            $("select.items").select2();
        });

        $("#tbl_invoice").on("click", "#btn_remove", function() {
            $(this).closest("tr").remove();
        });

        $("#tbl_invoice").on("change", "#select_treatment", function() {
            let id = $(this).val();

            for (var treatment of treatments) {
                if(treatment['id'] == id) {
                    $(this).closest('tr').find('#price').val(treatment['price']);
                }
            }

        });

        $("#btn_save").on("click", function() {
            var tbl_values =[];

            if($("#txt_name").val() == ''){
                $("#err_name").text('name field is required');
                $("#err_name").show();
                return false;
            }else{
                $("#err_name").hide();
            }

            if($("#txt_price").val() == ''){
                $("#err_price").show();
                return false;
            }else{
                $("#err_price").hide();
            }
            
            $('#tbl_pharmacy tr').each(function(){
                var o={};
                var inputs = $(this).find('input');
                if(inputs.length != 0){
                    inputs.each(function(){
                        o[$(this).attr('name')]=this.value;
                    });

                    tbl_values.push(o);
                }  
            });

            var name = $("#txt_name").val();
            var price = $("#txt_price").val();
            var data = {name:name, price:price, tbl_values:tbl_values};

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
               type:'POST',
               url:'{{route('treatment.store')}}',
               data: data,
               success:function(data) {
                    console.log(data);
                    if(data.message == 'fail'){
                        $("#err_name").text('Name is Already Exists');
                        $("#err_name").show();
                    }else{
                        $("#err_name").hide();
                        window.location.href = "{{ route('treatment.saveIndex')}}";
                    }
               }
            });

        });

        
    });
    //addrow();

    
        
    function initializeSelect2(selectElementObj) {
        let data = $.map(treatments, function (obj) {
            obj.id = obj.id;
            obj.text = obj.name;
            return obj;
        });
        $(selectElementObj).select2({
            data: data
        });
    }

    

    function addrow(){
        var html = '';
            html += '<tr>'+
                    '<td>'+
                        '<select class="select2" name="select_treatment" id="select_treatment" style="width:100%;">'+
                            '<option value="">Select Treatment</option>'+
                        '</select>' +
                    '</td>'+
                    '<td>'+
                        '<input class="form-control" placeholder="0" type="number" name="price" id="price" readonly/>'+
                    '</td>'+
                    '<td>'+
                        '<input class="form-control" placeholder="0" type="number" name="discount"/>'+
                    '</td>'+
                    '<td>'+
                        '<input class="form-control" placeholder="0" type="number" name="sub_total" readonly/>'+
                    '</td>'+
                    '<td style="text-align:center;"><span class="btn btn-danger" id="btn_remove"><i class="fa fa-remove"></i></span></td>' +
                    '</tr>';
                   // console.log(html);
            
            $("#tbl_invoice").append(html);
            var newSelect=$("#tbl_invoice").find(".select2").last();
            initializeSelect2(newSelect);
            
    }
</script>
<style>
.select2-selection {
    height: 35px !important;
}
.select2-selection__rendered {
line-height: 35px !important;
}
.select2-selection__arrow{
    height: 35px !important;
}
</style>
@endsection