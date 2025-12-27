@extends('layouts.app')

@section('title', 'Patient Record Lists')

@section('content')
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Patient Record</h1>
            <div class="row">
                <div class="col-md-12">
                    <a href="{{ route('record.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                </div>
                
            </div>

        </div>

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Patient Record Lists</h6>

            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="tbl_record" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="15%">Name</th>
                                <th>Note</th>
                                <th width="8%">Created Time</th>
                                <th width="5%">Token</th>
                                <th width="18%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($records as $record)
                                <tr>
                                    <td>{{ $record->first_name }} {{ $record->last_name }}</td>
                                    <td>{{ $record->description }}</td>
                                    <td>{{ $record->created_time }}</td>
                                    <td>{{ $record->token }}</td>
                                   
                                    <td style="white-space: nowrap; display: flex; flex-wrap: wrap; gap: 6px;">
                                        <a href="{{ route('record.view', ['record' => $record->id]) }}"
                                            class="btn btn-info btn-sm">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('record.edit', ['record' => $record->id]) }}"
                                            class="btn btn-primary btn-sm">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                        <button class="btn btn-danger btn-sm" id="delete_icon" data-remote="{{ route('record.destroy', ['record' => $record->id]) }}">
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
    $('div.alert').delay(3000).slideUp(300);
    $('#tbl_record').DataTable({
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
                var table = $('#tbl_record').DataTable();
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
            
});


</script>
    
@endsection
