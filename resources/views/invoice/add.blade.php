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

        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#treat">Treatment</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#sale">Sale</a>
            </li>
        </ul>
        <form method="POST" action="#">
                    @csrf
        <div class="tab-content">
            <div id="treat" class="tab-pane active">
                    
                    <div class="card-body">
                        <div class="form-group">

                            <div class="form-group">
                                <div class="row">
                                    {{-- Invoice --}}
                                    <div class="col-sm-3">
                                        <label style="margin-top:9px;">Invoice No.</label>
                                        <input type="text" class="form-control" id="txt_invoice" placeholder="" name="txt_invoice" value="{{$invoice_number}}" readonly>
                                    </div>
                                </div>
                            </div>

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
                                        <input type="text" class="datepicker form-control" id="txt_date" placeholder="d-m-y" 
                                                name="created_time">
                                        <span style="display:none;" id="err_date" class="text-danger"></span>
                                        
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
                                                <th style="width: 115px;"></th>
                                                <th style="width: 20%"><p>Sub Total</p></th>
                                                <th style="width:50px"><p>Action</p></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <tr class="tr_clone">
                                        <td>
                                            <select class="items select" name="select_treatment" id="select_treatment" style="width:100%;">
                                                <option value="">Select Treatment</option>
                                                @foreach($treatments as $treatment)
                                                    <option value="{{$treatment->id}}">{{$treatment->name}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input class="form-control" placeholder="0" type="number" name="price" id="price" readonly/>
                                        </td>
                                        <td>
                                            <input class="form-control" placeholder="0" type="number" id="discount" name="discount"/>
                                        </td>
                                        <td>
                                            <select class="form-control" name="discount_type" id="discount_type">
                                                <option value="mmk" selected> MMK </option>
                                                <option value="percent"> % </option>
                                            </select>
                                        </td>
                                        <td>
                                            <input class="form-control" placeholder="0.00" type="number" id="sub_total" name="sub_total" readonly/>
                                        </td>
                                            <td style="text-align:center;">
                                            <span class="btn btn-danger" id="btn_remove">
                                                <i class="fa fa-remove"></i>
                                            </span>
                                            </td>
                                        </tr>
                                        </tbody>
                                        
                                        <tr class="grand-total">
                                            <td colspan="4"><label style="float:right;">Total: </label></td>
                                            <td><input class="form-control" placeholder="0.00" type="number" id="total_amount" name="total_amount" readonly/></td>
                                        </tr>
                                    </table>
                                    </div>
                                </div>  
                            </div>

                            {{-- Table Patient Sale Add --}}
                            <div class="form-group" style="margin-top:52px;">
                            <div>
                                <button type="button" class="btn btn-primary" id="btn_patient_sale_add" style="float:right;margin-top:-43px;">Add</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-12">
                                <table id="tbl_patient_sale" class="table table-bordered table-striped table-hover" style="width:100%;margin:0 auto;">
                                    <thead>
                                        <tr>
                                            <th><p>Pharmacy</p></th>
                                            <th style="width: 20%"><p>Price</p></th>
                                            <th style="width: 10%"><p>Quantity</p></th>
                                            <th style="width: 10%"><p>Discount</p></th>
                                            <th style="width: 115px;"></th>
                                            <th style="width: 20%"><p>Sub Total</p></th>
                                            <th style="width:50px"><p>Action</p></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="tr_clone">
                                    <td>
                                        <select class="items select" name="select_pharmacy" id="select_pharmacy" style="width:100%;">
                                            <option value="">Select Pharmacy</option>
                                            @foreach($pharmacies as $pharmacy)
                                                <option value="{{$pharmacy->id}}">{{$pharmacy->name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control" placeholder="0" type="number" name="price" id="price" readonly/>
                                    </td>
                                    <td>
                                        <input class="form-control" placeholder="0" type="number" name="qty" id="qty"/>
                                    </td>
                                    <td>
                                        <input class="form-control" placeholder="0" type="number" id="discount" name="discount"/>
                                    </td>
                                    <td>
                                        <select class="form-control" name="discount_type" id="discount_type">
                                            <option value="mmk" selected> MMK </option>
                                            <option value="percent"> % </option>
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control" placeholder="0.00" type="number" id="sub_total" name="sub_total" readonly/>
                                    </td>
                                        <td style="text-align:center;">
                                        <span class="btn btn-danger" id="btn_remove">
                                            <i class="fa fa-remove"></i>
                                        </span>
                                        </td>
                                    </tr>
                                    </tbody>
                                    
                                    <tr class="grand-total">
                                        <td colspan="5"><label style="float:right;">Total: </label></td>
                                        <td><input class="form-control" placeholder="0.00" type="number" id="patient_sale_total_amount" name="patient_sale_total_amount" readonly/></td>
                                    </tr>
                                </table>
                                </div>
                            </div>  
                        </div>

                        </div>

                        <div class="form-group">
                        <div class="row">
                            {{-- Grand Total --}}
                            <div class="col-sm-9">
                                <label style="margin-top:9px;float:right;">Grand Total : </label>
                            </div>
                            <div class="col-sm-3">
                                <input type="number" class="form-control" placeholder="0.00" id="grand_total" placeholder="" name="txt_grand_total" readonly>
                            </div>
                        </div>
                    </div>
                    </div>

                    
                    
                    <div class="card-footer">
                        <button type="button" id="btn_save" class="btn btn-success btn-user float-right mb-3">Save</button>
                        <a class="btn btn-primary float-right mr-3 mb-3" href="{{ route('treatment.index') }}">Cancel</a>
                    </div>
                
            </div>
            <div id="sale" class="tab-pane fade"> <br>
                <div class="card-body">
                    <div class="form-group">
                        <div class="form-group">
                            <div class="row">
                                {{-- Invoice --}}
                                <div class="col-sm-3">
                                    <label style="margin-top:9px;">Invoice No.</label>
                                    <input type="text" class="form-control" id="txt_sale_invoice" placeholder="" name="txt_sale_invoice" value="{{$invoice_number}}" readonly>
                                </div>
                                <div class="col-sm-1"></div>
                                    {{-- Date --}}
                                    <div class="col-sm-3">
                                        <label style="margin-top:9px;">Invoice Date<span style="color:red;">*</span></label>
                                        <input type="text" class="datepicker form-control" id="txt_sale_date" placeholder="d-m-y" 
                                                name="created_time">
                                        <span style="display:none;" id="err_sale_date" class="text-danger"></span>
                                        
                                    </div>
                                    <div class="col-sm-1 mb-3 mt-3 mb-sm-0" style="margin-left:-23px;">
                                        <img class="datepicker-open" src="{{asset('plugin/jqueryui-1.13/images/calendar.png')}}"
                                            width="41px;" style="margin-top: 23px;">
                                    </div>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top:52px;">
                            <div>
                                <button type="button" class="btn btn-primary" id="btn_sale_add" style="float:right;margin-top:-43px;">Add</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-12">
                                <table id="tbl_sale" class="table table-bordered table-striped table-hover" style="width:100%;margin:0 auto;">
                                    <thead>
                                        <tr>
                                            <th><p>Pharmacy</p></th>
                                            <th style="width: 20%"><p>Price</p></th>
                                            <th style="width: 10%"><p>Quantity</p></th>
                                            <th style="width: 10%"><p>Discount</p></th>
                                            <th style="width: 115px;"></th>
                                            <th style="width: 20%"><p>Sub Total</p></th>
                                            <th style="width:50px"><p>Action</p></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="tr_clone">
                                    <td>
                                        <select class="items select" name="select_pharmacy" id="select_pharmacy" style="width:100%;">
                                            <option value="">Select Pharmacy</option>
                                            @foreach($pharmacies as $pharmacy)
                                                <option value="{{$pharmacy->id}}">{{$pharmacy->name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control" placeholder="0" type="number" name="price" id="price" readonly/>
                                    </td>
                                    <td>
                                        <input class="form-control" placeholder="0" type="number" name="qty" id="qty"/>
                                    </td>
                                    <td>
                                        <input class="form-control" placeholder="0" type="number" id="discount" name="discount"/>
                                    </td>
                                    <td>
                                        <select class="form-control" name="discount_type" id="discount_type">
                                            <option value="mmk" selected> MMK </option>
                                            <option value="percent"> % </option>
                                        </select>
                                    </td>
                                    <td>
                                        <input class="form-control" placeholder="0.00" type="number" id="sub_total" name="sub_total" readonly/>
                                    </td>
                                        <td style="text-align:center;">
                                        <span class="btn btn-danger" id="btn_remove">
                                            <i class="fa fa-remove"></i>
                                        </span>
                                        </td>
                                    </tr>
                                    </tbody>
                                    
                                    <tr class="grand-total">
                                        <td colspan="5"><label style="float:right;">Total: </label></td>
                                        <td><input class="form-control" placeholder="0.00" type="number" id="sale_total_amount" name="sale_total_amount" readonly/></td>
                                    </tr>
                                </table>
                                </div>
                            </div>  
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" id="btn_sale_save" class="btn btn-success btn-user float-right mb-3">Save</button>
                </div>
            </div>
        </div>
        </form>

        
    </div>

</div>

@endsection
@section('scripts')

<script>
    var patients = {!! json_encode($patients->toArray()) !!};
    var treatments = {!! json_encode($treatments->toArray()) !!};
    var pharmacies = {!! json_encode($pharmacies->toArray()) !!};
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
            // yearRange: ':+20',
            onSelect: function (value, ui) {
            }
        }).datepicker("setDate", 'now');

        $("#txt_sale_date").datepicker({
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
            $(".items").select2("destroy");
            var $tr = $('#tbl_invoice tbody tr').first();

            var $clone = $tr.clone();
            $clone.find(':text').val('');
            $clone.find(':input').val('');
            $('#tbl_invoice tbody tr.grand-total').before($clone);
            $("select.items").select2();
            var $trNew = $('#tbl_invoice tr:last').prev();
            $trNew.find("select.form-control").val("mmk");

        });

        $("#btn_sale_add").on("click", function() {
            $(".items").select2("destroy");
            var $tr = $('#tbl_sale tbody tr').first();

            var $clone = $tr.clone();
            $clone.find(':text').val('');
            $clone.find(':input').val('');
            $('#tbl_sale tbody tr.grand-total').before($clone);
            $("select.items").select2();
            var $trNew = $('#tbl_sale tr:last').prev();
            $trNew.find("select.form-control").val("mmk");

        });

        $("#btn_patient_sale_add").on("click", function() {
            $(".items").select2("destroy");
            var $tr = $('#tbl_patient_sale tbody tr').first();

            var $clone = $tr.clone();
            $clone.find(':text').val('');
            $clone.find(':input').val('');
            $('#tbl_patient_sale tbody tr.grand-total').before($clone);
            $("select.items").select2();
            var $trNew = $('#tbl_patient_sale tr:last').prev();
            $trNew.find("select.form-control").val("mmk");

        });

        $("#tbl_invoice").on("click", "#btn_remove", function() {
            var $tr = $('#tbl_invoice tbody tr').length;
            if($tr != 2){
                $(this).closest("tr").remove();
            }

            calculate($(this));
            
        });

        $("#tbl_sale").on("click", "#btn_remove", function() {
            var $tr = $('#tbl_sale tbody tr').length;
            if($tr != 2){
                $(this).closest("tr").remove();
            }

            calculateSale($(this));
            
        });

        $("#tbl_patient_sale").on("click", "#btn_remove", function() {
            var $tr = $('#tbl_patient_sale tbody tr').length;
            if($tr != 2){
                $(this).closest("tr").remove();
            }

            calculatePatientSale($(this));
            
        });

        $("#tbl_invoice").on("change", "#discount_type", function() {
            calculate($(this));
        });

        $("#tbl_sale").on("change", "#discount_type", function() {
            calculateSale($(this));
        });

        $("#tbl_patient_sale").on("change", "#discount_type", function() {
            calculatePatientSale($(this));
        });

        $("#tbl_invoice").on("change", "#select_treatment", function() {
            let id = $(this).val();

            for (var treatment of treatments) {
                if(treatment['id'] == id) {
                    $(this).closest('tr').find('#price').val(treatment['price']);
                    calculate($(this));
                }
            }

        });

        $("#tbl_sale").on("change", "#select_pharmacy", function() {
            let id = $(this).val();

            for (var pharmacy of pharmacies) {
                if(pharmacy['id'] == id) {
                    $(this).closest('tr').find('#price').val(pharmacy['selling_price']);
                    $(this).closest('tr').find('#qty').val(1);
                    calculateSale($(this));
                }
            }

        });

        $("#tbl_patient_sale").on("change", "#select_pharmacy", function() {
            let id = $(this).val();

            for (var pharmacy of pharmacies) {
                if(pharmacy['id'] == id) {
                    $(this).closest('tr').find('#price').val(pharmacy['selling_price']);
                    $(this).closest('tr').find('#qty').val(1);
                    calculatePatientSale($(this));
                }
            }

        });

        $("#tbl_invoice").on("input", "#discount", function() {
            
            calculate($(this));

        });

        $("#tbl_sale").on("input", "#discount", function() {
            
            calculateSale($(this));

        });

        $("#tbl_patient_sale").on("input", "#discount", function() {
            
            calculatePatientSale($(this));

        });

        $("#tbl_sale").on("input", "#qty", function() {
            
            calculateSale($(this));

        });

        $("#tbl_patient_sale").on("input", "#qty", function() {
            
            calculatePatientSale($(this));

        });

        $("#btn_save").on("click", function() {
            
            var tbl_values =[];
            var tbl_sale_values = [];
            let invoice_no = $("#txt_invoice").val();
            let patient_id = $("#select_patient").val();
            let invoice_date = $("#txt_date").val();

            if($("#txt_date").val() == ''){
                $("#err_date").text('Invoice Date field is required');
                $("#err_date").show();
                return false;
            }else{
                $("#err_date").hide();
            }


            $('#tbl_invoice tr.tr_clone').each(function(){
                var o={};
                var inputs = $(this).find('select, input');
                if(inputs.length != 0){
                    inputs.each(function(){
                        o[$(this).attr('name')]=this.value;
                    });

                    tbl_values.push(o);
                }  
            });

            $('#tbl_patient_sale tr.tr_clone').each(function(){
                var o={};
                var inputs = $(this).find('select, input');
                if(inputs.length != 0){
                    inputs.each(function(){
                        o[$(this).attr('name')]=this.value;
                    });

                    tbl_sale_values.push(o);
                }  
            });

            var data = {invoice_no:invoice_no, patient_id:patient_id, 
                       invoice_date:invoice_date, type:'treatment',
                       tbl_values,tbl_sale_values};

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
               type:'POST',
               url:'{{route('invoices.store')}}',
               data: data,
               success:function(data) {
                 window.open('/invoice/'+invoice_no+'/treatment');
                 window.location.reload();
               }
            });

        });

        
    });

    $("#btn_sale_save").on("click", function() {
            
            var tbl_values =[];
            let invoice_no = $("#txt_sale_invoice").val();
            let invoice_date = $("#txt_sale_date").val();

            if($("#txt_sale_date").val() == ''){
                $("#err_sale_date").text('Invoice Date field is required');
                $("#err_sale_date").show();
                return false;
            }else{
                $("#err_sale_date").hide();
            }


            $('#tbl_sale tr.tr_clone').each(function(){
                var o={};
                var inputs = $(this).find('select, input');
                if(inputs.length != 0){
                    inputs.each(function(){
                        o[$(this).attr('name')]=this.value;
                    });

                    tbl_values.push(o);
                }  
            });

            var data = {invoice_no:invoice_no,
                       invoice_date:invoice_date, type:'sale',
                       tbl_values,tbl_values};

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
               type:'POST',
               url:'{{route('invoices.store')}}',
               data: data,
               success:function(data) {
                 window.open('/invoice/'+invoice_no+'/sale');
                 window.location.reload();
               }
            });

        });
    //addrow();

    function calculate(element){
            let discount = parseInt(element.closest('tr').find('#discount').val());
            let type = element.closest('tr').find('#discount_type').val();
            let price = parseInt(element.closest('tr').find('#price').val());
            let sub_total=0;
            let discount_price = 0;
            let grand_total = 0;
            if(type == 'percent'){
                if(isNaN(discount)){
                    sub_total = price;
                }else{
                    discount_price = (price * discount/100);
                    sub_total = price - discount_price.toFixed(2);
                }
                
            }else{

                if(isNaN(discount)){
                    discount = 0;
                }
                discount_price = discount;
                sub_total = price - discount;
            }

            element.closest('tr').find('#sub_total').val(sub_total.toFixed(2));

            let total_amount = 0;
            $("#tbl_invoice tbody tr").each(function(){
                let sub_total_amount = parseInt($(this).find('#sub_total').val());
                
                if(isNaN(sub_total_amount)){
                    sub_total_amount = 0;
                    
                }
                total_amount += sub_total_amount;

            });

            $("#total_amount").val(total_amount.toFixed(2));
            let sale_total = $("#patient_sale_total_amount").val();
            if(isNaN(sale_total) || sale_total == '')
                sale_total = 0;
            
            grand_total = total_amount + parseInt(sale_total);
            console.log(sale_total);
            //alert(grand_total);
            //$("#sub_totalamount").val(total_amount.toFixed(2));
            
            $("#grand_total").val(grand_total.toFixed(2));
    }

    function calculateSale(element){
            let discount = parseInt(element.closest('tr').find('#discount').val());
            let type = element.closest('tr').find('#discount_type').val();
            let price = parseInt(element.closest('tr').find('#price').val());
            let qty = parseInt(element.closest('tr').find('#qty').val());
            let sub_total=0;
            let qty_price = 0;
            let discount_price = 0;
 
            if(type == 'percent'){

                if(isNaN(qty)){
                    qty_price = price;
                }else{
                    qty_price = price * qty;
                    discount_price = qty_price;
                }

                if(isNaN(discount)){
                    sub_total = qty_price;
                }else{
                    discount_price = (qty_price * discount/100);
                    sub_total = (qty_price - discount_price.toFixed(2));
                }
                
            }else{

                if(isNaN(qty)){
                    qty_price = price;
                }else{
                    qty_price = price * qty;
                }
                if(isNaN(discount)){
                    discount = 0;
                }

                discount_price = discount;
                sub_total = qty_price - discount;

            }

            element.closest('tr').find('#sub_total').val(sub_total.toFixed(2));

            let total_amount = 0;
            $("#tbl_sale tbody tr").each(function(){
                let sub_total_amount = parseInt($(this).find('#sub_total').val());
                
                if(isNaN(sub_total_amount)){
                    sub_total_amount = 0;
                    
                }
                total_amount += sub_total_amount;

            });

            //$("#sale_sub_totalamount").val(total_amount.toFixed(2));
            $("#sale_total_amount").val(total_amount.toFixed(2));
    }

    function calculatePatientSale(element){
            let discount = parseInt(element.closest('tr').find('#discount').val());
            let type = element.closest('tr').find('#discount_type').val();
            let price = parseInt(element.closest('tr').find('#price').val());
            let qty = parseInt(element.closest('tr').find('#qty').val());
            let sub_total=0;
            let qty_price = 0;
            let discount_price = 0;
            let grand_total = 0;
 
            if(type == 'percent'){

                if(isNaN(qty)){
                    qty_price = price;
                }else{
                    qty_price = price * qty;
                    discount_price = qty_price;
                }

                if(isNaN(discount)){
                    sub_total = qty_price;
                }else{
                    discount_price = (qty_price * discount/100);
                    sub_total = (qty_price - discount_price.toFixed(2));
                }
                
            }else{

                if(isNaN(qty)){
                    qty_price = price;
                }else{
                    qty_price = price * qty;
                }
                if(isNaN(discount)){
                    discount = 0;
                }

                discount_price = discount;
                sub_total = qty_price - discount;

            }
            console.log(discount);
            element.closest('tr').find('#sub_total').val(sub_total.toFixed(2));

            let total_amount = 0;
            $("#tbl_patient_sale tbody tr").each(function(){
                let sub_total_amount = parseInt($(this).find('#sub_total').val());
                
                if(isNaN(sub_total_amount)){
                    sub_total_amount = 0;
                    
                }
                total_amount += sub_total_amount;

            });

            //$("#sale_sub_totalamount").val(total_amount.toFixed(2));
            let treatment_total = $("#total_amount").val();
            if(isNaN(treatment_total) || treatment_total == '')
                treatment_total = 0;

            console.log(treatment_total);
            grand_total = total_amount + parseInt(treatment_total);
            $("#patient_sale_total_amount").val(total_amount.toFixed(2));
            $("#grand_total").val(grand_total.toFixed(2));
    }

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
            
            $("#tbl_invoice tbody").append(html);
            var newSelect=$("#tbl_invoice").find(".select2").last();
            initializeSelect2(newSelect);
            
    }
</script>
@endsection