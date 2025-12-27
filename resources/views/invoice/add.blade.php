@extends('layouts.app')

@section('title', 'Create Invoice')

@section('content')
    <div class="container-fluid">
        <div class="alert alert-info">
            Invoice creation has moved to the new Livewire interface.
            <a href="{{ route('invoices.index') }}" class="alert-link">Go to Invoices</a> to create an invoice.
        </div>
    </div>
@endsection


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

            var exitSubmit = false;
            $('.select_pharmacy').each(function(){
                var treatment   = $(this).val();
                var errormessage    = "";

                if (treatment == '')
                    errormessage = "Please select an pharmacy";

                if (errormessage != "") {
                    alert(errormessage);
                    exitSubmit = true;
                    return false;
                }
            });
            if(exitSubmit) {
                return false;
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
                        '<select class="select2 select_treatment" name="select_treatment" id="select_treatment" style="width:100%;">'+
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