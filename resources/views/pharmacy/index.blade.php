@extends('layouts.app')

@section('title', 'Patients List')

@section('content')
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Pharmacy</h1>
            <div class="row">
                <div class="col-md-6">
                    <a href="{{ route('pharmacy.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="{{ route('pharmacy.export') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-check"></i> Export To Excel
                    </a>
                </div>
                
            </div>

        </div>

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- DataTales Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Pharmacy</h6>

            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="tbl_pharmacy" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Selling Price</th>
                                @hasrole('Admin')
                                    <th>Purchase Price</th>
                                @endhasrole
                                <th>Created Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pharmacy as $phar)
                                <tr>
                                    <td>{{ $phar->name }}</td>
                                    <td>{{ $phar->selling_price }}</td>
                                    @hasrole('Admin')
                                    <td>{{ $phar->net_price }}</td>
                                    @endhasrole
                                    <td>{{ $phar->created_at->format('d-m-Y') }}</td>

                                    <td style="display: flex">
                                        <a href="{{ route('pharmacy.edit', ['pharmacy' => $phar->id]) }}"
                                            class="btn btn-primary m-2">
                                            <i class="fa fa-pen"></i>
                                        </a>
                                        @hasrole('Admin')
                                        <button class="btn btn-danger m-2" id="delete_icon" data-remote="{{ route('pharmacy.destroy', ['pharmacy' => $phar->id]) }}">
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
    $('#tbl_pharmacy').DataTable({
        "lengthChange": true,
        "info": true, 
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
                var table = $('#tbl_pharmacy').DataTable();
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
