@extends('layouts.master')
@section('page_title', 'Manage Schools')
@section('content')

    @if($errors->any())
        <div class="alert alert-danger alert-styled-left alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">Schools</h6>
            {!! Qs::getPanelOptions() !!}
        </div>
        <div class="card-body">
            <table class="table datatable-button-html5-columns">
                <thead>
                <tr>
                    <th>S/N</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Students</th>
                    <th>Staff</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($schools as $s)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $s->name }} @if(isset($currentSchool) && $currentSchool && $currentSchool->id == $s->id) <span class="badge badge-primary ml-1">Current</span> @endif</td>
                        <td>{{ $s->code }}</td>
                        <td>{{ $students[$s->id] ?? 0 }}</td>
                        <td>{{ $staff[$s->id] ?? 0 }}</td>
                        <td>{!! $s->active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>' !!}</td>
                        <td>
                            <form method="post" action="{{ route('schools.switch', $s->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">Open</button>
                            </form>
                            <a href="{{ route('schools.edit', $s->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">Add a New School</h6>
            {!! Qs::getPanelOptions() !!}
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('schools.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 border-right-2 border-right-blue-400">
                        <h6 class="font-weight-semibold mb-3">School details</h6>
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label font-weight-semibold">School Name <span class="text-danger">*</span></label>
                            <div class="col-lg-8"><input name="name" value="{{ old('name') }}" required type="text" class="form-control" placeholder="Name of School"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label font-weight-semibold">School Code <span class="text-danger">*</span></label>
                            <div class="col-lg-8"><input name="code" value="{{ old('code') }}" required type="text" class="form-control" placeholder="Short code, e.g. NORTH"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label font-weight-semibold">Phone</label>
                            <div class="col-lg-8"><input name="phone" value="{{ old('phone') }}" type="text" class="form-control" placeholder="Phone"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label font-weight-semibold">School Email</label>
                            <div class="col-lg-8"><input name="email" value="{{ old('email') }}" type="email" class="form-control" placeholder="School Email"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label font-weight-semibold">Address</label>
                            <div class="col-lg-8"><input name="address" value="{{ old('address') }}" type="text" class="form-control" placeholder="School Address"></div>
                        </div>
                        <div class="form-group row">
                            <div class="col-lg-8 offset-lg-4">
                                <label class="form-check-label">
                                    <input type="checkbox" name="copy_defaults" value="1" checked>
                                    Start with the classes and grading scale of the school I am in now
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="font-weight-semibold mb-3">First admin for this school</h6>
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label font-weight-semibold">Admin Name <span class="text-danger">*</span></label>
                            <div class="col-lg-8"><input name="admin_name" value="{{ old('admin_name') }}" required type="text" class="form-control" placeholder="Full name"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label font-weight-semibold">Username <span class="text-danger">*</span></label>
                            <div class="col-lg-8"><input name="admin_username" value="{{ old('admin_username') }}" required type="text" class="form-control" placeholder="Login username"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label font-weight-semibold">Email <span class="text-danger">*</span></label>
                            <div class="col-lg-8"><input name="admin_email" value="{{ old('admin_email') }}" required type="email" class="form-control" placeholder="Admin email"></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label font-weight-semibold">Password <span class="text-danger">*</span></label>
                            <div class="col-lg-8"><input name="admin_password" required type="password" minlength="6" class="form-control" placeholder="At least 6 characters"></div>
                        </div>
                        <p class="text-muted">This admin can only see and manage this school.</p>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary">Create School <i class="icon-paperplane ml-2"></i></button>
                </div>
            </form>
        </div>
    </div>

@endsection
