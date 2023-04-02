@extends('layouts.app')

@section('title', 'Patient Weight Lists')

@section('content')
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Patient Weight</h1>
            <div class="row">
                <div class="col-md-12">
                    <a href="{{ route('weight.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                </div>
            </div>

        </div>

        <div class="col-sm-3">
            <select class="select2" name="patient_id" id="select_patient">
                    <option value="all">All</option>
                    @foreach ($patients as $patient)
                        <option value="{{$patient->id}}">{{$patient->first_name}} {{$patient->last_name}}</option>
                    @endforeach
            </select>
           
        </div> <br>

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Patient Weight Lists</h6>

            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="tbl_weight" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Weight</th>
                                <th>Created Time</th>
                                <th>ID</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($weights as $weight)
                                <tr>
                                    <td>{{ $weight->first_name }} {{ $weight->last_name }}</td>
                                    <td>{{ $weight->weight }}</td>
                                    <td>{{ $weight->created_time }}</td>
                                    <td>{{ $weight->token }}</td>
                                   
                                    <td style="display: flex">
                                         <a target="_blank" href="{{ route('weight.view', ['weight' => $weight->id]) }}"
                                            class="btn btn-info m-2">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('weight.edit', ['weight' => $weight->id]) }}"
                                            class="btn btn-primary m-2">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                        <button class="btn btn-danger m-2" id="delete_icon" data-remote="{{ route('weight.destroy', ['weight' => $weight->id]) }}">
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
@endsection

@section('scripts')

<script>
$(document).ready(function(){
    $('#select_patient').select2({
            //minimumInputLength: 3
            width: "100%",
    });

    $('div.alert').delay(3000).slideUp(300);
    $('#tbl_weight').DataTable({
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
                var table = $('#tbl_weight').DataTable();
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

      $('#select_patient').on('select2:select', function (e) {
            var data = e.params.data;
            var patient_id = data.id;
            console.log(patient_id);
            var url = '/weight/listByPatient/'+patient_id;
            $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: url,
            data: {patient_id: patient_id},
            success: function(data) {
              //$("#tbl_weight tbody").empty();
               var table = $('#tbl_weight').DataTable();
               table.clear();
               //table.draw();
               let patients = data[0];
               var html = '';
               for (var patient of patients) {
                    html = '<tr value="'+patient['id']+'">'+
                        '<td>'+ patient['first_name'] + patient['last_name']  +'</td>'+
                        '<td>'+patient['weight'] +'</td>'+
                        '<td>'+patient['created_time'] +'</td>'+
                        '<td>'+patient['token'] +'</td>'+
                        '<td style="display: flex">'+
                            '<a href="/weight/view/'+patient['id']+'" class="btn btn-info m-2">'+
                                ' <i class="fa-solid fa-eye"></i>'+
                            '</a>'+
                            '<a class="btn btn-primary m-2" href="/weight/edit/'+patient['id']+'">' +
                                '<i class="fa fa-pen"></i>'+
                            '</a>'+
                            '<button class="btn btn-danger m-2" value="'+patient['id']+'" id="delete_icon">' +
                                '<i class="fas fa-trash"></i>'+
                            '</button>' +
                        '</td>' +
                        '</tr>';
                        
                        var newRow = $(html);
                        table.row.add(newRow).draw();
                }

                //table.clear();
                // var newRow = $(html);
                // table.row.add(newRow).draw();
                //table.draw();
                //$("#tbl_weight tbody").append(html);
                
                //$("#tbl_weight").fnDestroy();
                //$("#tbl_weight").DataTable();
               
                //table.clear();
                //table.draw();
                //var table = $('#tbl_weight').DataTable();
                
                // $("#tbl_weight").dataTable();
                
                // var rowsPerPage = $('#tbl_weight tbody').length;
                // console.log(rowsPerPage);
                // $('#totalRows').text(rowsPerPage);
                // $('#numRows').text(rowsPerPage);
            }
        });
    });
            
});


</script>
    
@endsection
