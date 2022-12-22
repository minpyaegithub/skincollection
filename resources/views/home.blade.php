@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fa-solid fa-gauge-high"></i> Dashboard </h1>
    </div>

    <!-- Content Row -->
    <div class="row">

        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Patient</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">@convertnumber($total_patient[0]->count)</div>
                        </div>
                        <div class="col-auto">
                            <i class="fa-solid fa-user-plus fa-2x text-gray-300"></i>
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
                                Today Patient</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">@convertnumber($today_patient[0]->count)</div>
                        </div>
                        <div class="col-auto">
                        <i class="fa-solid fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Apointment</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">@convertnumber($total_appointment[0]->count)</div>
                        </div>
                        <div class="col-auto">
                            <i class="fa-regular fa-calendar-check fa-2x text-gray-300"></i>
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
                                Today Apointment</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">@convertnumber($today_appointment[0]->count)</div>
                        </div>
                        <div class="col-auto">
                            <i class="fa-regular fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    <div class="card shadow">
        <div class="card-body">
            <div id="main" style="width:100%;height:500px;"></div>
        </div>
    </div>

</div>
@endsection
@section('scripts')

<script type="text/javascript">

    var patients = {!! json_encode($patient_monthly) !!};

    $(function () {

        $("#inventory-home").removeClass("active");
        $("#home").addClass("active");
       
        patient_chart();
        function patient_chart(){

            //console.log(patients);
            let date = [];
            let count = [];

            for(var i in patients) {
                date.push(patients[i].date);
                count.push(patients[i].count);
            }

            // Initialize the echarts instance based on the prepared dom
            var myChart = echarts.init(document.getElementById('main'));

            // Specify the configuration items and data for the chart
            var option = {
                    title: {
                        text: 'Patients Chart List - (Current Month)',
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