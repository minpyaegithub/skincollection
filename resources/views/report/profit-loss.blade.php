@extends('layouts.app')

@section('title', 'Profit and Loss')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Profit and Loss</h1>
    </div>

    {{-- Alert Messages --}}
    @include('common.alert')
    <div class="card shadow mb-4">
        <form method="POST" action="{{route('weight.store')}}">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                            <label style="margin-top:9px;">Select Date Range</label>
                        </div>
                        <div class="col-sm-5 mb-3 mt-3 mb-sm-0">
                            <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span></span> <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                        <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                            <button type="button" id="btn_calculate" class="btn btn-sm btn-primary btn-user">Calculate</button>
                        </div>
                    </div>
                    

                </div>
            </div>
        </form>
    </div>

    <!-- <div style="display:none;" class="loader"></div> -->
    <div style="margin-left:50%;display:none;" class="loader"></div>
    <div class="card shadow mb-4 print" id="pl_report" style="display:none;">
    
        <div class="card-header py-3" style="text-align:center;">
            <h5 class="mb-1 font-weight-bold text-primary">Profit and Loss</h5>
            <h6 class="mb-1 font-weight-bold text-primary"> Skin Collections Statement 
            </h6>
            <h6 class="mb-1 font-weight-bold text-primary">
                <span id="date_from"></span>
                <span> to </span>
                <span id="date_to"></span>
            </h6>
            
        </div>
            <div class="card-body">
                <div class="form-group">
                
                    <h6 class="font-weight-bold">Revenues</h6><hr>

                    <h6> Sale Revenues <span id="sale_amt" style="float:right;">-</span></h6>
                    <h6> Treatment Revenues <span id="treatment_amt" style="float:right;">-</span></h6><hr>
                    <h6> Total Revenues <span id="total_amt" style="float:right;">-</span></h6><br>
                    
                    <h6 class="font-weight-bold">Cost of Sales</h6><hr>
                    <h6> Purchases <span id="purchase_amt" style="float:right;">-</span></h6><hr>
                    <h6> Total Cost of Sales <span id="total_cost_sale_amt" style="float:right;">-</span></h6><br>

                    <h6 class="font-weight-bold">Expenses</h6><hr>
                    <div id="div_expense">
                        
                    </div>
                    
                    
                    <hr><h6> Total Expenses <span id="total_expense" style="float:right;">-</span></h6><hr>

                    <h5 class="font-weight-bold"> Profit <span id="profit" style="float:right;">-</span></h5><hr>
                    <h5 class="font-weight-bold"> Loss <span id="loss" style="float:right;">-</span></h5><hr>


                </div>
            </div>

            <div class="card-footer">
                <button type="buttom" id="btn_print" class="no-print btn btn-primary btn-user float-right mb-3">Print</button>
            </div>

    </div>

</div>

@endsection
@section('scripts')

<script>
    $(function () {
        var start = moment().subtract(29, 'days');
        var end = moment();
        var from;var to;

        $("#btn_print").click(function() {
            window.print();
        });

        $("#btn_calculate").click(function() {
            $("#date_from").html(from.format('MMMM D, YYYY'));
            $("#date_to").html(to.format('MMMM D, YYYY'));

            $(".loader").show();
            $("#div_expense").empty();
            $("#loss").html('-');
            $("#profit").html('-');
            console.log('start: ', from.format('YYYY-MM-DD'), 'end: ', to.format('YYYY-MM-DD'));
            let from_date = from.format('YYYY-MM-DD');
            let to_date = to.format('YYYY-MM-DD');

            var data = {from_date:from_date, to_date:to_date};

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
               type:'GET',
               url:'{{route('report.getPfData')}}',
               data: data,
               success:function(data) {
                let revenues = data[0];
                let expenses = data[1];
                let sale_revenues = revenues['sale_revenues'];
                let total_revenues = parseInt(revenues['total_revenues']);
                let purchase_revenues = parseInt(revenues['purchase_revenues']);
                let treatment_revenues = total_revenues - sale_revenues;
                $("#sale_amt").html(numberWithCommas(sale_revenues));
                $("#treatment_amt").html(numberWithCommas(treatment_revenues));
                $("#total_amt").html(numberWithCommas(total_revenues));
                $("#purchase_amt").html(numberWithCommas(purchase_revenues));
                $("#total_cost_sale_amt").html(numberWithCommas(purchase_revenues));
                let expense_total = 0;
                var html = '';
                    for (var expense of expenses) {
                        expense_total += parseInt(expense['amount']);
                        html += '<h6> ' 
                                    + expense['category'] + 
                                    '<span style="float:right;">'
                                    + numberWithCommas(parseInt(expense['amount'])) +
                                '</span></h6>';
                    }

                    $("#div_expense").append(html);
                    $("#total_expense").html(numberWithCommas(expense_total));

                    let total_cost = purchase_revenues + expense_total;
                    let net_income = total_revenues - total_cost;
                    //let profit_percent = (100 * net_income) / total_cost;
                    if(total_cost > total_revenues){
                        $("#loss").html(numberWithCommas(net_income));
                    }else{
                        $("#profit").html(numberWithCommas(net_income));
                    }

                    $(".loader").hide();
                    $("#pl_report").show();

               }
            });
        });

        function cb(start, end) {
            $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            
            from = start;
            to = end;
        }

        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);

        cb(start, end);
    });
</script>
<style>
    hr{
        margin-top: 0rem !important;
    }
    @page {
        size: A5 portrait;
        margin: 10;
    }

    
    
    @media print {
         

    html, body {
      height:100vh; 
      overflow: hidden;
    }

        .no-print, .no-print *
        {
            display: none !important;
        }
        
        body * {
            visibility: hidden;
        }
        #pl_report, #pl_report * {
            visibility: visible;
        }
        #pl_report {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            right: 0;
        }
    }
</style>
@endsection