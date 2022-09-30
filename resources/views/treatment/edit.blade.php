@extends('layouts.app')

@section('title', 'Edit Treatment')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Treatment Category</h1>
        <a href="{{route('treatment.index')}}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                class="fas fa-arrow-left fa-sm text-white-50"></i> Back</a>
    </div>

    {{-- Alert Messages --}}
    @include('common.alert')
   
    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Treatment Category</h6>
        </div>
        <form method="POST" action="{{route('treatment.update', ['treatment' => $treatment->id])}}">
            @csrf
            @method('PUT')
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
                            class="form-control @error('name') is-invalid @enderror" 
                            id="txt_name"
                            placeholder="Name" 
                            name="name" 
                            value="{{ old('name') ?  old('name') : $treatment->name}}">
                                @error('name')
                                    <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>

                        </div>  
                    </div>


                    {{-- Type--}}
                    <div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 mb-3 mt-3 mb-sm-0">
                                <label style="margin-top:9px;">Type</label>
                            </div>
                            <div class="col-sm-3 mb-3 mt-3 mb-sm-0">
                            <input 
                            type="text" 
                            class="form-control @error('type') is-invalid @enderror" 
                            id="txt_type"
                            placeholder="Type" 
                            name="type" 
                            value="{{ old('type') ?  old('type') : $treatment->type}}">
                            </div>
                        </div>  
                    </div>


                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-success btn-user float-right mb-3">Update</button>
                <a class="btn btn-primary float-right mr-3 mb-3" href="{{ route('treatment.index') }}">Cancel</a>
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