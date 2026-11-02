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
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label font-weight-semibold">Functions</label>
                    <div class="col-lg-6">
                        <p class="text-muted mb-2">Untick a function to switch it off for this school. Its pages and menu items disappear for everyone in the school.</p>
                        @foreach(App\Models\School::$modules as $key => $m)
                            <div class="mb-1"><label class="form-check-label">
                                <input type="checkbox" name="modules[]" value="{{ $key }}" {{ $school->moduleEnabled($key) ? 'checked' : '' }}>
                                {{ $m['label'] }}
                            </label></div>
                        @endforeach
                    </div>
                </div>
                <div class="text-right">
                    <a href="{{ route('schools.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">School admins</h6>
        </div>
        <div class="card-body">
            <p class="text-muted">A school admin manages everything inside this school: students, parents, teachers, accountants, classes, sections, subjects, dormitories, exams and fees.</p>
            <table class="table mb-4">
                <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>New password</th></tr></thead>
                <tbody>
                @forelse($admins as $a)
                    <tr>
                        <td>{{ $a->name }}</td><td>{{ $a->username }}</td><td>{{ $a->email }}</td>
                        <td>
                            <form method="post" action="{{ route('schools.admins.password', [$school->id, $a->id]) }}" class="form-inline">
                                @csrf
                                <input type="password" name="new_password" minlength="6" required class="form-control form-control-sm mr-2" placeholder="New password">
                                <button class="btn btn-sm btn-outline-secondary">Change</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">This school has no admin yet. Add one below.</td></tr>
                @endforelse
                </tbody>
            </table>
            <h6 class="font-weight-semibold">Add another admin</h6>
            <form method="post" action="{{ route('schools.admins.store', $school->id) }}">
                @csrf
                <div class="row">
                    <div class="col-md-3 mb-2"><input name="admin_name" required class="form-control" placeholder="Full name"></div>
                    <div class="col-md-2 mb-2"><input name="admin_username" required class="form-control" placeholder="Username"></div>
                    <div class="col-md-3 mb-2"><input name="admin_email" type="email" required class="form-control" placeholder="Email"></div>
                    <div class="col-md-2 mb-2"><input name="admin_password" type="password" minlength="6" required class="form-control" placeholder="Password"></div>
                    <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block">Add Admin</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-danger">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold text-danger">Delete this school</h6>
        </div>
        <div class="card-body">
            <p>This permanently deletes <strong>{{ $school->name }}</strong> with all its students, staff, classes, marks, payments and attendance. It cannot be undone. Consider switching the school to inactive instead.</p>
            <form method="post" action="{{ route('schools.destroy', $school->id) }}" onsubmit="return confirm('Delete this school and all of its data for good?');">
                @csrf @method('DELETE')
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label font-weight-semibold">Type the code <code>{{ $school->code }}</code> to confirm</label>
                    <div class="col-lg-6"><input name="confirm_code" required autocomplete="off" type="text" class="form-control" placeholder="{{ $school->code }}"></div>
                </div>
                <div class="text-right"><button type="submit" class="btn btn-danger"><i class="icon-trash mr-1"></i> Delete School</button></div>
            </form>
        </div>
    </div>

@endsection
