@extends('layouts.app')

@section('title', 'Inventory Dashboard')

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fa-solid fa-gauge-high"></i> Inventory Dashboard </h1>
    </div>

    <!-- Content Row -->
    <div class="row">
    @hasrole('Admin')
        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Monthly Purchase</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">@convert($total_purchase[0]->total)</div>
                        </div>
                        <div class="col-auto">
                            <i class="fa-solid fa-money-bill-1-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Monthly Sale</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">@convert($total_sale[0]->total)</div>
                        </div>
                        <div class="col-auto">
                            <i class="fa-solid fa-money-bill-1-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endhasrole
        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Today Income</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">@convert($today_income[0]->sub_total)</div>
                        </div>
                        <div class="col-auto">
                            <i class="fa-solid fa-money-bill-1-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Requests Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Out of Stock Medicine</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">@convertnumber($out_of_stock)</div>
                        </div>
                        <div class="col-auto">
                            <i class="fa-solid fa-pills fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    <div class="card shadow mb-4">
        <div class="card-body">
            <div id="main" style="width:100%;height:500px;"></div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                    <table class="table table-bordered" id="tbl_stock" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Medicine Name</th>
                                <th>Quantity</th>
                                <th>Available Quantity</th>
                                <th>Added On</th>
                                <th>Updated On</th>
                                @hasrole('Admin')
                                    <th width="14%">Action</th>
                                @endhasrole
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stock_details as $stock_detail)
                                <tr>
                                    <td>{{ $stock_detail->name }}</td>
                                    <td>{{ $stock_detail->qty }}</td>
                                    <td>{{ $stock_detail->available_qty }}</td>
                                    <td>{{ $stock_detail->created_time }}</td>
                                    <td>{{ $stock_detail->updated_at }}</td>
                                    @hasrole('Admin')
                                        <td style="display: flex">
                                            <a href="{{ route('purchase.create') }}"
                                                class="btn btn-sm btn-primary m-2">
                                                <i class="fa-solid fa-plus"></i> Purchase
                                            </a>
                                        </td>
                                    @endhasrole
                                </tr>

                                
                            @endforeach
                        </tbody>
                    </table>

                </div>
        </div>
    </div>

</div>
@endsection
@section('scripts')

<script type="text/javascript">

    var patients = {!! json_encode($sale_monthly) !!};

    $(function () {

        $("#home").removeClass("active");
        $("#inventory-home").addClass("active");
        
        $('#tbl_stock').DataTable({
            "lengthChange": true,
            "info": true, 
            "searching": true,
            "aaSorting": [],
            // "dom": 'Bfrtip',
            // "buttons": [
            //         'copy', 'csv', 'excel', 'pdf', 'print'
            //     ]
        })

        patient_chart();
        function patient_chart(){

            //console.log(patients);
            let date = [];
            let count = [];

            for(var i in patients) {
                date.push(patients[i].date);
                count.push(patients[i].total);
            }

            // Initialize the echarts instance based on the prepared dom
            var myChart = echarts.init(document.getElementById('main'));

            // Specify the configuration items and data for the chart
            var option = {
                    title: {
                        text: 'Sale Chart List',
                        x:'center'
                    },
                    tooltip: { trigger: 'item'},
                    toolbox: {
                    show: true,
                    feature: {
                        saveAsImage: {}
                    }
                    },
                    xAxis: {
                        type: 'category',
                        boundaryGap: true,
                        data: date,
                        axisLabel: {
                                formatter: function (value, index) {
                                    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun","July", "Aug", "Sep", "Oct", "Nov", "Dec"];
                                
                                var date = new Date(value);
                                var xx= date.getDate() + ' ' + monthNames[date.getMonth()];
                                return xx;

                        },
                    },
                },
                    yAxis: {
                        type: 'value',
                        scale:true,
                        axisTick: {
                       // length: 3
                        },
                       // splitNumber: 3
                    },
                    series: [
                        {
                            data: count,
                            type: 'line',
                            smooth: true
                        }
                    ]
                };

                // Display the chart using the configuration items and data just specified.
                myChart.setOption(option);
                }
    
    });
</script>
@endsection