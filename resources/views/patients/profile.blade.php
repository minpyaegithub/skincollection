@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4 border-bottom">
            <h1 class="h3 mb-0 text-gray-800">Profile</h1>
        </div>

        {{-- Alert Messages --}}
        @include('common.alert')

        {{-- Page Content --}}
        <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-4 border-right">
                    <div class="d-flex flex-column align-items-center text-center p-3 py-5">
                        <img class="rounded-circle mt-5" width="150px" src="{{ asset('admin/img/undraw_profile.svg') }}">
                        <span class="font-weight-bold">{{ $patient->first_name }} {{ $patient->last_name }}</span>
                        <span class="text-black-50">Token: {{ $patient->token }} </span>
                        <span class="text-black-50">Phone: {{ $patient->phone }} </span>
                        <span class="text-black-50">{{ $patient->email }}</span>
                    </div>
                      <span style="font-weight: bold;font-size: 17px;">Photo</span>  
                    <hr>
                    <div style="height:52%;overflow-x: hidden;overflow-y: auto;border-radius:6px;">
                        <div>
                        <div class="row">
                            @foreach(json_decode($patient->photo) as $photo)
                                <div class="col-md-4">
                                    <div class="thumbnail">
                                        <a href="/patient-photo/{{$photo}}" target="_blank">
                                        <img src="/patient-photo/{{$photo}}" alt="Lights" style="width:100%">
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        </div>
                        
                    </div>
                    
                    

                </div>
            <div class="col-md-8 border-right">
                {{-- Weight Chart --}}
                <div class="p-3 py-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div id="main" style="width:100%;height:400px;"></div>
                    </div>
                    
                </div>

                
                {{-- Invoice --}}
                <div class="p-3 py-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                    
                    <div class="table-responsive">
                    <h4 style="text-align:center;">Invoice Lists</h4>
                    <table class="table table-bordered" id="tbl_invoice" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Invoice No</th>
                                <th>Total</th>
                                <th width="10%">Treatment</th>
                                <th>Created Time</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->invoice_no }}</td>
                                    <td>{{ $invoice->total }}</td>
                                    <td>{{ $invoice->count }}</td>
                                    <td>{{ $invoice->created_time }}</td>

                                    <td style="display: flex">
                                        <a href="{{ route('generateInvoice', ['invoice' => $invoice->invoice_no, 'type'=>$invoice->type]) }}"
                                            class="btn btn-info m-2" target="_blank">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <button class="btn btn-danger m-2" id="delete_icon" data-remote="{{ route('invoices.destroy', ['invoice' => $invoice->invoice_no]) }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                
                            @endforeach
                        </tbody>
                    </table>

                </div>
                    </div>

                    
                </div>
            </div>

        </div>
            </div>
        </div>

    </div>
@endsection
@section('scripts')

<script>

    var patients = {!! json_encode($patient_weight) !!};

$(document).ready(function(){
    $('div.alert').delay(3000).slideUp(300);

    $('#tbl_invoice').DataTable({
        "lengthChange": true,
        "info": true, 
        "searching": true,
        "aaSorting": []
    }).on('click', '#delete_icon', function (e) { 
        e.preventDefault();
         $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
          var url = $(this).data('remote');
          swal({
              title: 'Are you sure?',
              text: "You won't be able to revert this!",
              type: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
              if (result.value) {
                var table = $('#tbl_invoice').DataTable();
                table.row( $(this).parents('tr') ).remove().draw();
                
                $.ajax({
                  url: url,
                  type: 'DELETE',
                  dataType: 'json',
                  data: {method: '_DELETE', submit: true}
              }).always(function (data) {
                    console.log(data);
                    $("#alert-delete").show();
                    $('div.alert').delay(3000).slideUp(300);
              });
                
              }
            })
      });

        weight_chart();
        function weight_chart(){

            //console.log(patients);
            let date = [];
            let count = [];
            console.log(patients);
            for(var i in patients) {
                date.push(patients[i].date);
                count.push(patients[i].weight);
            }

            // Initialize the echarts instance based on the prepared dom
            var myChart = echarts.init(document.getElementById('main'));

            // Specify the configuration items and data for the chart
            var option = {
                    title: {
                        text: 'Patient Weight Chart',
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
                            name: 'Direct',
      type: 'bar',
      barWidth: '60%',
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