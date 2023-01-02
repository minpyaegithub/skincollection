@extends('layouts.app')

@section('title', 'Invoices List')

@section('content')
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Invoices</h1>
            <div class="row">
            
                <div class="col-md-12">
                    <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                </div>          
            </div>

        </div>

        <div class="col-sm-2 mb-4">
            <label style="margin-top:9px;">Type<span style="color:red;">*</span></label>
                <select class="form-control" name="type" id="select_type">
                        <option value="">All</option>
                        <option value="treatment">Treatment</option>
                        <option value="sale">Sale</option>   
                </select>
        </div>

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Invoice</h6>

            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="tbl_invoice" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Invoice No</th>
                                <th>Total</th>
                                <th>Type</th>
                                <th width="10%">Item Count</th>
                                <th>Created Time</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->invoice_no }}</td>
                                    <td>{{ $invoice->total }}</td>
                                    <td>{{ $invoice->type }}</td>
                                    <td>{{ $invoice->count }}</td>
                                    <td>{{ $invoice->created_time }}</td>

                                    <td style="display: flex">
                                        <a href="{{ route('generateInvoice', ['invoice' => $invoice->invoice_no, 'type'=>$invoice->type]) }}"
                                            class="btn btn-info m-2" target="_blank">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @hasrole('Admin')
                                        <button class="btn btn-danger m-2" id="delete_icon" data-remote="{{ route('invoices.destroy', ['invoice' => $invoice->invoice_no]) }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endhasrole
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
    $('#tbl_invoice').DataTable({
        "lengthChange": true,
        "info": true, 
        "searching": true,
        "aaSorting": []
        //order: [[4, 'desc']],
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

      $('#select_type').on('change', function() {
            var table = $('#tbl_invoice').DataTable();
            table
            .columns( 2 )
            .search( this.value )
            .draw();
        });
            
});


</script>
    
@endsection
