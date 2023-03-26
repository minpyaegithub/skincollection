@extends('layouts.app')

@section('title', 'View Patient Weight')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"></h1>
        <a href="{{route('weight.index')}}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fa-solid fa-list fa-sm text-white-50"></i> List </a>
    </div>

    {{-- Alert Messages --}}
    @include('common.alert')
   
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h4 class="m-0 font-weight-bold text-primary" style="text-align:center">{{$weight[0]->first_name}} {{$weight[0]->last_name}} <br> {{$weight[0]->created_at}}</h4>
        </div>
        <form>
            <div class="card-body">
                <div class="form-group">

                    {{-- Weight --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Weight</label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                            <input disabled
                            type="text" 
                            class="form-control"
                            id="txt_weight"
                            placeholder="eg - 102" 
                            name="weight" 
                            value="{{$weight[0]->weight}}" disabled>
                            </div>
                        </div>  
                    </div>

                    
                    {{-- Arm Circumference --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Arm Circumference</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-group col-sm-3 mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Contract</span>
                                    </div>
                                    <input disabled type="text" class="form-control" name="arm_contract" value="{{ old('arm_contract') ?  old('arm_contract') : $weight[0]->arm_contract}}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">in</span>
                                    </div>
                            </div>
                            <div class="input-group col-sm-3 mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Upper relax</span>
                                    </div>
                                    <input disabled type="text" class="form-control" name="arm_relax"
                                    value="{{ old('arm_relax') ?  old('arm_relax') : $weight[0]->arm_relax}}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">in</span>
                                    </div>
                            </div>
                            <div class="input-group col-sm-3 mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Middle</span>
                                    </div>
                                    <input disabled type="text" class="form-control" name="arm_middle"
                                    value="{{ old('arm_middle') ?  old('arm_middle') : $weight[0]->arm_middle}}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">in</span>
                                    </div>
                            </div>
                            <div class="input-group col-sm-3 mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Lower</span>
                                    </div>
                                    <input disabled type="text" class="form-control" name="arm_lower"
                                    value="{{ old('arm_lower') ?  old('arm_lower') : $weight[0]->arm_lower}}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">in</span>
                                    </div>
                            </div>
                        </div>
                        
                    </div>

                    {{-- Waist Circumference --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Waist Circumference</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-group col-sm-3 mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Upper</span>
                                    </div>
                                    <input disabled type="text" class="form-control" name="waist_upper"
                                    value="{{ old('waist_upper') ?  old('waist_upper') : $weight[0]->waist_upper}}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">in</span>
                                    </div>
                            </div>
                            <div class="input-group col-sm-3 mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Middle</span>
                                    </div>
                                    <input disabled type="text" class="form-control" name="waist_middle"
                                    value="{{ old('waist_middle') ?  old('waist_middle') : $weight[0]->waist_middle}}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">in</span>
                                    </div>
                            </div>
                            <div class="input-group col-sm-3 mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Lower</span>
                                    </div>
                                    <input disabled type="text" class="form-control" name="waist_lower"
                                    value="{{ old('waist_lower') ?  old('waist_lower') : $weight[0]->waist_lower}}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">in</span>
                                    </div>
                            </div>
                        </div>
                        
                    </div>

                    {{-- Thigh Circumference --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Thigh Circumference</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-group col-sm-3 mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Upper</span>
                                    </div>
                                    <input disabled type="text" class="form-control" name="thigh_upper"
                                    value="{{ old('thigh_upper') ?  old('thigh_upper') : $weight[0]->thigh_upper}}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">in</span>
                                    </div>
                            </div>
                            <div class="input-group col-sm-3 mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Middle</span>
                                    </div>
                                    <input disabled type="text" class="form-control" name="thigh_middle"
                                    value="{{ old('thigh_middle') ?  old('thigh_middle') : $weight[0]->thigh_middle}}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">in</span>
                                    </div>
                            </div>
                            <div class="input-group col-sm-3 mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Lower</span>
                                    </div>
                                    <input disabled type="text" class="form-control" name="thigh_lower"
                                    value="{{ old('thigh_lower') ?  old('thigh_lower') : $weight[0]->thigh_lower}}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">in</span>
                                    </div>
                            </div>
                        </div>
                        
                    </div>

                    {{-- Calf Circumference --}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Calf Circumference</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="input-group col-sm-3 mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Upper</span>
                                    </div>
                                    <input disabled type="text" class="form-control" name="calf_upper"
                                    value="{{ old('calf_upper') ?  old('calf_upper') : $weight[0]->calf_upper}}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">cm</span>
                                    </div>
                            </div>
                            <div class="input-group col-sm-3 mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Middle</span>
                                    </div>
                                    <input disabled type="text" class="form-control" name="calf_middle"
                                    value="{{ old('calf_middle') ?  old('calf_middle') : $weight[0]->calf_middle}}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">cm</span>
                                    </div>
                            </div>
                            <div class="input-group col-sm-3 mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Lower</span>
                                    </div>
                                    <input disabled type="text" class="form-control" name="calf_lower"
                                    value="{{ old('calf_lower') ?  old('calf_lower') : $weight[0]->calf_lower}}">
                                    <div class="input-group-append">
                                        <span class="input-group-text">cm</span>
                                    </div>
                            </div>
                        </div>
                        
                    </div>

                </div>
            </div>

        </form>
    </div>

</div>

@endsection
@section('scripts')

<script>
    $(function () {
        

    });


</script>
<style>

</style>
@endsection