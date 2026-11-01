@extends('layouts.master')
@section('page_title', 'Edit School')
@section('content')

    @if($errors->any())
        <div class="alert alert-danger alert-styled-left alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">Edit {{ $school->name }}</h6>
            {!! Qs::getPanelOptions() !!}
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('schools.update', $school->id) }}">
                @csrf @method('PUT')
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label font-weight-semibold">School Name <span class="text-danger">*</span></label>
                    <div class="col-lg-6"><input name="name" value="{{ old('name', $school->name) }}" required type="text" class="form-control"></div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label font-weight-semibold">School Code <span class="text-danger">*</span></label>
                    <div class="col-lg-6"><input name="code" value="{{ old('code', $school->code) }}" required type="text" class="form-control"></div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label font-weight-semibold">Phone</label>
                    <div class="col-lg-6"><input name="phone" value="{{ old('phone', $school->phone) }}" type="text" class="form-control"></div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label font-weight-semibold">School Email</label>
                    <div class="col-lg-6"><input name="email" value="{{ old('email', $school->email) }}" type="email" class="form-control"></div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label font-weight-semibold">Address</label>
                    <div class="col-lg-6"><input name="address" value="{{ old('address', $school->address) }}" type="text" class="form-control"></div>
                </div>
                <div class="form-group row">
                    <div class="col-lg-6 offset-lg-3">
                        <label class="form-check-label">
                            <input type="checkbox" name="active" value="1" {{ old('active', $school->active) ? 'checked' : '' }}>
                            Active (inactive schools cannot sign in)
                        </label>
                    </div>
                </div>
                <div class="text-right">
                    <a href="{{ route('schools.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

@endsection
