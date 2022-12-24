<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Larave Generate Invoice PDF - Nicesnippest.com</title>
</head>
<style type="text/css">
    
    body{
        font-family: 'Roboto Condensed', sans-serif;
    }
    .m-0{
        margin: 0px;
    }
    .p-0{
        padding: 0px;
    }
    .pt-5{
        padding-top:5px;
    }
    .mt-10{
        margin-top:10px;
    }
    .text-center{
        text-align:center !important;
    }
    .w-100{
        width: 100%;
    }
    .w-50{
        width:50%;   
    }
    .w-85{
        width:85%;   
    }
    .w-15{
        width:15%;   
    }
    .logo img{
        width:20%;
        /* height:100%; */
        padding-top:30px;
    }
    .logo span{
        margin-left:8px;
        top:19px;
        position: absolute;
        font-weight: bold;
        font-size:25px;
    }
    .gray-color{
        color:#5D5D5D;
    }
    .text-bold{
        font-weight: bold;
    }
    .border{
        border:1px solid black;
    }
    table tr,th,td{
        border: 1px solid #d2d2d2;
        border-collapse:collapse;
        padding:7px 8px;
    }
    table tr th{
        background: #F4F4F4;
        font-size:15px;
    }
    table tr td{
        font-size:13px;
    }
    table{
        border-collapse:collapse;
    }
    .box-text p{
        line-height:10px;
    }
    .float-left{
        float:left;
    }

    .float-right{
        float:right;
    }
    .total-part{
        font-size:16px;
        line-height:12px;
    }
    .total-right p{
        padding-right:20px;
    }
    .no-print{
        margin-top: 20px;
        float: right;
        width: 149px;
        height: 41px;
        background: cadetblue;
        color: white;
    }
    @page {
        size: A5 portrait;
        margin: 10;
    }
    @media print
    {    
        .no-print, .no-print *
        {
            display: none !important;
        }
    }

</style>
<body>

<div class="head-title logo">
    <div class="w-100 mt-10" style="text-align:center;">
        <img src="/logo/logo-black.png">
    </div>

    <div class="w-100 mt-10" style="text-align:center;margin-bottom:15px;">
        <p class="m-0 pt-5 text-bold w-100"> အမှတ် - ၁၆/၃၊ ဗိုလ်မှူးဖိုးကွန်းလမ်း၊</p>
        <p class="m-0 pt-5 text-bold w-100"> မြန်မာ့အလှဧည့်ရိပ်သာနှင့်မျက်နှာချင်းဆိုင်။ </p>
        <p class="m-0 pt-5 text-bold w-100"> ဖုန်း - 09400650300 </p>
        <!-- <p class="m-0 pt-5 text-bold w-100"> Opening Hours - From 8AM to 8PM </p> -->
        <hr>
    </div>
    
</div>
<div class="add-detail mt-10" style="margin-bottom:30px;">
    <div class="w-100 mt-10">
        <div class="float-left">
            <p class="m-0 pt-5 text-bold w-100">Invoice No - <span class="gray-color">{{$treatments[0]->invoice_no}}</span></p>
            <p class="m-0 pt-5 text-bold w-100">Invoice Date - <span class="gray-color">{{$treatments[0]->created_time}}</span></p>
        </div>
        
        <div class="float-right">
            <p class="m-0 pt-5 w-100">{{$treatments[0]->first_name}} {{$treatments[0]->last_name}}</p>
            <p class="m-0 pt-5 w-100">{{$treatments[0]->phone}}</p>
        </div>
        
    </div>

    <div style="clear: both;"></div>
</div>

<div class="table-section bill-tbl w-100 mt-10">
    <table class="table w-100 mt-10">
        <tr>
            <th class="w-50">Treatment Name</th>
            <th class="w-50">Price</th>
            <th class="w-50">Discount</th>
            <th class="w-50">Subtotal</th>        
        </tr>

        @foreach($treatments as $treatment)
        <tr align="center">
            <td>{{$treatment->treatment_name}}</td>
            <td>{{$treatment->price}}</td>
            
            @if($treatment->discount_type == 'mmk')
                @if($treatment->discount)
                    <td>{{$treatment->discount}} MMK</td>
                @else
                    <td> - </td>
                @endif
            @else
                @if($treatment->discount)
                    <td>{{$treatment->discount}} %</td>
                @else
                    <td> - </td>
                @endif
            @endif
            <td>{{$treatment->sub_total}}</td>
        </tr>
       @endforeach
        <tr>
            <td colspan="5">
                <div class="total-part">
                    <div class="total-left w-70 float-left" align="right" style="width:75%;">
                        <p>Total Payable - </p>
                    </div>
                    <div class="total-right float-left text-bold" align="center" style="width:25%;">
                        <p>@convert($treatment_total)</p>
                    </div>
                    <div style="clear: both;"></div>
                </div> 
            </td>
        </tr>
    </table>
 </div>

 @if(!empty($sales))
 <div class="table-section bill-tbl w-100 mt-10">
    <table class="table w-100 mt-10">
        <tr>
            <th class="w-50">Pharmacy Name</th>
            <th class="w-50">Price</th>
            <th class="w-50">Quantity</th>
            <th class="w-50">Discount</th>
            <th class="w-50">Subtotal</th>      
        </tr>

        @foreach($sales as $sale)
        <tr align="center">
            <td>{{$sale->phar_name}}</td>
            <td>{{$sale->price}}</td>
            <td>{{$sale->qty}}</td>
            
            @if($sale->discount_type == 'mmk')
                @if($sale->discount)
                    <td>{{$sale->discount}} MMK</td>
                @else
                    <td> - </td>
                @endif
            @else
                @if($sale->discount)
                    <td>{{$sale->discount}} %</td>
                @else
                    <td> - </td>
                @endif
            @endif
            <td>{{$sale->sub_total}}</td>
        </tr>
       @endforeach
        <tr>
            <td colspan="5">
                <div class="total-part">
                    <div class="total-left w-70 float-left" align="right" style="width:75%;">
                        <p>Total Payable - </p>
                    </div>
                    <div class="total-right float-left text-bold" align="center" style="width:25%;">
                        <p>@convert($sale_total)</p>
                    </div>
                    <div style="clear: both;"></div>
                </div> 
            </td>
        </tr>
    </table>
 </div>
 @endif

 <br><div style="float:right;">
    <label>Grand Total - </label>
    <label class="text-bold" style="margin-right:59px;">@convert($grand_total)</label>
 </div><br>
 <hr>
 <button class="no-print" onclick="window.print();" id="print"> Print </button>
</body>
<script>
    window.print();
    // printlayer('print');
    // function printlayer(layer){
    //     var generator=window.open(",'name,");
    //     var layertext=document.getElementById(layer);
    //     generator.document.write(layertext.innerHTML.replace('Print Me'));
    //     generator.document.close();
    //     generator.print();
    //     generator.close();
    // }
 </script>
</html>