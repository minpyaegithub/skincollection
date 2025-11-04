@extends('layouts.app')

@section('title', 'Clinic Management')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4"></div>

    {{-- Alert Messages --}}
    @include('common.alert')

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-body">
            @livewire('clinic-management')
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);

        $('#dataTable').DataTable({
            "lengthChange": true,
            "info": true, 
            "searching": true,
            "aaSorting": [],
            // "dom": 'Bfrtip',
            // "buttons": [
            //         'copy', 'csv', 'excel', 'pdf', 'print'
            //     ]
        });
    });
</script>

    
@endsection