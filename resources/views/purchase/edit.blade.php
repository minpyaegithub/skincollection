@extends('layouts.app')

@section('title', 'Edit Purchase')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Purchase</h1>
        <a href="{{route('purchase.index')}}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                class="fas fa-arrow-left fa-sm text-white-50"></i> Back</a>
    </div>

    {{-- Alert Messages --}}
    @include('common.alert')
   
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Purchase</h6>
        </div>
        <form method="POST" action="{{route('purchase.update', ['purchase' => $purchase->id])}}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">

                {{-- Pharmacy --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Select Medicine</label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <select class="form-control" name="phar_id" id="select_pharmacy">
                                    @foreach ($pharmacy as $phar)
                                        @if($phar->id == $purchase->phar_id)
                                            <option value="{{$phar->id}}" selected>{{$phar->name}}</option>
                                        @else
                                            <option value="{{$phar->id}}">{{$phar->name}}</option>
                                        @endif
                                    @endforeach
                                </select> 
                            </div>
                        </div>
                    </div>


                    {{-- Selling Price --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Selling Price<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                            <input 
                            type="number" 
                            class="form-control @error('selling_price') is-invalid @enderror" 
                            id="txt_selling_price"
                            placeholder="Selling Price" 
                            name="selling_price" 
                            value="{{ old('selling_price') ?  old('selling_price') : $purchase->selling_price}}">

                            @error('selling_price')
                                <span class="text-danger">{{$message}}</span>
                            @enderror
                            </div>
                        </div>  
                    </div>

                    {{-- Net Price --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Net Price<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                            <input 
                            type="number" 
                            class="form-control @error('net_price') is-invalid @enderror" 
                            id="txt_net_price"
                            placeholder="Net Price" 
                            name="net_price" 
                            value="{{ old('net_price') ?  old('net_price') : $purchase->net_price}}">

                            @error('net_price')
                                <span class="text-danger">{{$message}}</span>
                            @enderror
                            </div>
                        </div>  
                    </div>

                    {{-- Qty --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Quantity<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                            <input 
                            type="number" 
                            class="form-control @error('qty') is-invalid @enderror" 
                            id="txt_qty"
                            placeholder="Quantity" 
                            name="qty"
                            value="{{ old('qty') ?  old('qty') : $purchase->qty}}">

                            @error('qty')
                                <span class="text-danger">{{$message}}</span>
                            @enderror
                            </div>
                        </div>  
                    </div>


                    {{-- Expire Date --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Created Time<span style="color:red;">*</span></label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <input
                                    type="text" 
                                    class="datepicker form-control @error('created_time') is-invalid @enderror" 
                                    id="txt_date"
                                    placeholder="d-m-y" 
                                    name="created_time" 
                                    value="{{ old('created_time') ?  old('created_time') : $purchase->created_time->format('d-m-Y') }}" >
                                    @error('created_time')
                                        <span class="text-danger">{{$message}}</span>
                                    @enderror
                            </div>

                            <div class="col-sm-1 mb-3 mt-3 mb-sm-0" style="margin-left:-23px;">
                                <img class="datepicker-open" src="{{asset('/plugin/jqueryui-1.13/images/calendar.png')}}" width="41px;" alt="">
                            </div>
                        
                        </div>
                            
                        </div>

                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-success btn-user float-right mb-3">Update</button>
                <a class="btn btn-primary float-right mr-3 mb-3" href="{{ route('purchase.index') }}">Cancel</a>
            </div>
        </form>
    </div>

</div>

@endsection
@section('scripts')

<script>
    $(function () {
        $("#txt_date").datepicker({
            changeMonth: true,
            changeYear: true,
            // showOn: 'button',
            //buttonImageOnly: true,
            //buttonImage: 'images/calendar.gif',
             dateFormat: 'dd-mm-yy',
             yearRange: ':+20',
            onSelect: function (value, ui) {
            }
        });

    });

    $('.datepicker-open').click(function(event) {
        event.preventDefault();
        $('.datepicker').focus();
    });
</script>
<style>

</style>
@endsection