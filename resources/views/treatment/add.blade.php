@extends('layouts.app')

@section('title', 'Add Treatment')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add Treatment</h1>
        <a href="{{route('treatment.index')}}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                class="fas fa-arrow-left fa-sm text-white-50"></i> Back</a>
    </div>

    {{-- Alert Messages --}}
    @include('common.alert')
   
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Add New Treatment</h6>
        </div>
        <form method="POST" action="#">
            @csrf
            <div class="card-body">
                <div class="form-group">

                    {{-- Name --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Name<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                            <input 
                            type="text" 
                            class="form-control" 
                            id="txt_name"
                            placeholder="Name" 
                            name="name" 
                            value="" required>

                                    <span style="display:none;" id="err_name" class="text-danger"></span>
                            </div>

                        </div>  
                    </div>


                    {{-- Price --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Price<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                            <input 
                            type="number" 
                            class="form-control" 
                            id="txt_price"
                            placeholder="0.00" 
                            name="price"
                            value="" required>
                                <span style="display:none;" id="err_price" class="text-danger">price field is required</span>
                            </div>
                        </div>  
                    </div>

                    {{-- Pharmacy --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Select Medicine</label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <select class="form-control" name="phar_id" id="select_pharmacy">
                                    @foreach ($pharmacy as $phar)
                                        <option value="{{$phar->id}}">{{$phar->name}}</option>
                                    @endforeach
                                </select>
                                
                            </div>
                            <div class="col-sm-1 mb-1 mt-1 mb-sm-0">
                                <button type="button" class="btn btn-primary" id="btn_add" style="height:36px;margin-top:7px;">Add</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:-30px;">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <!-- <label style="margin-top:9px;">Select Medicine</label> -->
                            </div>
                            <div class="col-sm-4 mb-4 mt-4 mb-sm-0">
                            
                            <table id="tbl_pharmacy">
                                <thead>
                                        <td>Medicine</td>
                                        <td>Quantity</td>
                                </thead>
                            </table>

                            </div>
                        </div>  
                    </div>



                </div>
            </div>

            <div class="card-footer">
                <button type="button" id="btn_save" class="btn btn-success btn-user float-right mb-3">Save</button>
                <a class="btn btn-primary float-right mr-3 mb-3" href="{{ route('treatment.index') }}">Cancel</a>
            </div>
        </form>
    </div>

</div>

@endsection
@section('scripts')

<script>
    var pharmacy = {!! json_encode($pharmacy->toArray()) !!};
    $(function () {
        
        $('#select_pharmacy').select2({
            //minimumInputLength: 3
        });

        $("#btn_add").on("click", function() {
            var txt = $("#select_pharmacy option:selected").text();
            var val = $("#select_pharmacy option:selected").val();
            addrow(val, txt);
        });

        $("#tbl_pharmacy").on("click", "#btn_remove", function() {
            $(this).closest("tr").remove();
            //alert("click");
        });

        $("#btn_save").on("click", function() {
            var tbl_values =[];

            if($("#txt_name").val() == ''){
                $("#err_name").text('name field is required');
                $("#err_name").show();
                return false;
            }else{
                $("#err_name").hide();
            }

            if($("#txt_price").val() == ''){
                $("#err_price").show();
                return false;
            }else{
                $("#err_price").hide();
            }
            
            $('#tbl_pharmacy tr').each(function(){
                var o={};
                var inputs = $(this).find('input');
                if(inputs.length != 0){
                    inputs.each(function(){
                        o[$(this).attr('name')]=this.value;
                    });

                    tbl_values.push(o);
                }  
            });

            var name = $("#txt_name").val();
            var price = $("#txt_price").val();
            var data = {name:name, price:price, tbl_values:tbl_values};

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
               type:'POST',
               url:'{{route('treatment.store')}}',
               data: data,
               success:function(data) {
                    console.log(data);
                    if(data.message == 'fail'){
                        $("#err_name").text('Name is Already Exists');
                        $("#err_name").show();
                    }else{
                        $("#err_name").hide();
                        window.location.href = "{{ route('treatment.saveIndex')}}";
                    }
               }
            });

        });


        
    });
    //addrow();
    function addrow(val, txt){
        var html = '';
            html += '<tr id="'+val+'">'+
                    '<td>'+
                        '<input class="form-control" placeholder="0" type="hidden" value="'+val+'" name="phar_id"/>'+
                        '<input class="form-control" placeholder="0" type="text" value="'+txt+'" name="phar_name" disabled/>'+
                    '</td>'+
                    '<td>'+
                        '<input class="form-control" placeholder="0" type="number" name="qty"/>'+
                    '</td>'+
                    '<td>'+
                    '<button id="btn_remove" type="button" class="btn btn-danger m-2"><i aria-hidden="true" class="fas fa-trash"></i></button>'+
                    '</td>'+
                    '</tr>';
                   // console.log(html);
            
            $("#tbl_pharmacy").append(html);
       }
</script>
<style>

</style>
@endsection