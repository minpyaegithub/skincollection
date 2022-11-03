@extends('layouts.app')

@section('title', 'Invoices List')

@section('content')
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Invoices</h1>
            <div class="row">
                <div class="col-md-6">
                    <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-primary">
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
                <h6 class="m-0 font-weight-bold text-primary">All Patient</h6>

            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="tbl_patient" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Gender</th>
                                <th>DOB</th>
                                <th>Address</th>
                                <th>Weight</th>
                                <th>Height</th>
                                <th>Token</th>
                                <th>Disease</th>
                                <th>Created Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->first_name }} {{ $invoice->last_name }}</td>
                                    <td>{{ $invoice->email }}</td>
                                    <td>{{ $invoice->phone }}</td>
                                    <td>{{ $invoice->gender }}</td>

                                    <td>{{ $invoice->dob }}</td>
                                    <td>{{ $invoice->address }}</td>
                                    <td>{{ $invoice->weight }} lbs</td>

                                    <td>{{ $invoice->feet }}' {{ $patient->inches }}"</td>
                                    <td>{{ $invoice->token }}</td>
                                    <td>{{ $invoice->disease }}</td>
                                    <td>{{ $invoice->created_at->format('d-m-Y') }}</td>

                                    <td style="display: flex">
                                        <a href="{{ route('invoices.edit', ['invoice' => $invoice->id]) }}"
                                            class="btn btn-primary m-2">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                        <button class="btn btn-danger m-2" id="delete_icon" data-remote="{{ route('invoices.destroy', ['invoice' => $invoice->id]) }}">
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
    $('#tbl_patient').DataTable({
        "lengthChange": true,
        "info": false, 
        "searching": true,
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
              });
                
              }
            })
      });
            
});


</script>
    
@endsection
