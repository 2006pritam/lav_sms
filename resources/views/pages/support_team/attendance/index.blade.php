@extends('layouts.master')
@section('page_title', 'Manage Class Attendance')
@section('content')

    {{-- Class, Section, Timetable Slot & Date Selector --}}
    <div class="card">
        <div class="card-header header-elements-inline bg-primary text-white">
            <h6 class="card-title font-weight-bold">
                <i class="icon-calendar52 mr-2"></i> Class &amp; Timetable Attendance Management
            </h6>
            <div class="header-elements">
                @if($selected && isset($ttr) && $ttr)
                    <a href="{{ route('ttr.show', $ttr->id) }}" class="btn btn-sm btn-light text-primary font-weight-semibold mr-2">
                        <i class="icon-table2 mr-1"></i> Open Class Timetable ({{ $ttr->name }})
                    </a>
                @else
                    <a href="{{ route('tt.index') }}" class="btn btn-sm btn-light text-primary font-weight-semibold mr-2">
                        <i class="icon-table2 mr-1"></i> All Timetables
                    </a>
                @endif
            </div>
        </div>

        <div class="card-body">
            <form method="post" action="{{ route('attendance.selector') }}">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="my_class_id" class="font-weight-semibold">Class <span class="text-danger">*</span></label>
                            <select required onchange="getClassSections(this.value); getClassSubjects(this.value);" class="form-control select" name="my_class_id" id="my_class_id">
                                <option value="">Select Class...</option>
                                @foreach($my_classes as $mc)
                                    <option {{ ($selected && $my_class_id == $mc->id) ? 'selected' : '' }} value="{{ $mc->id }}">{{ $mc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="section_id" class="font-weight-semibold">Section</label>
                            <select class="form-control select" name="section_id" id="section_id">
                                <option value="">All Sections</option>
                                @if($selected && isset($class_sections))
                                    @foreach($class_sections as $sec)
                                        <option {{ ($section_id == $sec->id) ? 'selected' : '' }} value="{{ $sec->id }}">{{ $sec->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="subject_id" class="font-weight-semibold">Subject / Class Period</label>
                            <select class="form-control select" name="subject_id" id="subject_id">
                                <option value="">General / Homeroom Attendance</option>
                                @if($selected && isset($subjects))
                                    @foreach($subjects as $sub)
                                        <option {{ ($subject_id == $sub->id) ? 'selected' : '' }} value="{{ $sub->id }}">{{ $sub->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="attendance_date" class="font-weight-semibold">Attendance Date <span class="text-danger">*</span></label>
                            <input required type="date" name="attendance_date" id="attendance_date" class="form-control" value="{{ $attendance_date ?? date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-primary">
                        Load Class &amp; Timetable Attendance <i class="icon-arrow-right14 ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(!$selected && isset($tt_records) && $tt_records->count() > 0)
        <div class="card">
            <div class="card-header header-elements-inline">
                <h6 class="card-title font-weight-semibold"><i class="icon-table2 mr-2"></i> Quick Open by Class Timetable</h6>
                {!! Qs::getPanelOptions() !!}
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($tt_records as $rec)
                        <div class="col-md-4 mb-3">
                            <div class="card card-body border-left-info border-left-3 mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="font-weight-bold mb-1">{{ $rec->my_class->name ?? 'Class' }} — {{ $rec->name }}</h6>
                                        <span class="text-muted font-size-sm">Session: {{ $rec->year }}</span>
                                    </div>
                                    <div>
                                        <a href="{{ route('ttr.show', $rec->id) }}" class="btn btn-sm btn-outline-info mr-1" title="View Timetable">
                                            <i class="icon-eye"></i> Timetable
                                        </a>
                                        <a href="{{ route('attendance.manage', ['class_id' => $rec->my_class_id, 'ttr_id' => $rec->id]) }}" class="btn btn-sm btn-primary" title="Manage Attendance">
                                            <i class="icon-checkmark-circle"></i> Attendance
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($selected)
        {{-- Respected Class Timetable Schedule Bar --}}
        <div class="card border-info">
            <div class="card-header bg-info text-white header-elements-inline">
                <h6 class="card-title font-weight-bold">
                    <i class="icon-table2 mr-2"></i> Respected Timetable for {{ $my_class->name }}
                    @if($ttr) ({{ $ttr->name }} - {{ $ttr->year }}) @endif
                </h6>
                <div class="header-elements">
                    @if($ttr)
                        <a href="{{ route('ttr.show', $ttr->id) }}" class="btn btn-sm btn-light text-info font-weight-bold">
                            <i class="icon-new-tab mr-1"></i> Open Full Timetable
                        </a>
                        @if(Qs::userIsTeamSA())
                            <a href="{{ route('ttr.manage', $ttr->id) }}" class="btn btn-sm btn-outline-light ml-2">
                                <i class="icon-cog5 mr-1"></i> Edit Timetable
                            </a>
                        @endif
                    @else
                        <a href="{{ route('tt.index') }}" class="btn btn-sm btn-light text-info font-weight-bold">
                            <i class="icon-plus-circle2 mr-1"></i> Create Timetable for {{ $my_class->name }}
                        </a>
                    @endif
                </div>
            </div>

            @if($ttr && $timetable_entries->count() > 0)
                <div class="card-body py-2">
                    <p class="text-muted font-size-sm mb-2">
                        Click any timetable period below to take or view student attendance for that specific class session on <strong>{{ date('d M Y', strtotime($attendance_date)) }}</strong>:
                    </p>
                    <div class="d-flex flex-wrap">
                        <a href="{{ route('attendance.manage', ['class_id' => $my_class_id, 'section_id' => $section_id, 'ttr_id' => $ttr->id, 'attendance_date' => $attendance_date]) }}"
                           class="btn btn-sm mr-2 mb-2 {{ (!$subject_id && !$ts_id) ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="icon-users4 mr-1"></i> General Homeroom
                        </a>
                        @foreach($timetable_entries as $tt_item)
                            <a href="{{ route('attendance.manage', ['class_id' => $my_class_id, 'section_id' => $section_id, 'ttr_id' => $ttr->id, 'tt_id' => $tt_item->id, 'subject_id' => $tt_item->subject_id, 'ts_id' => $tt_item->ts_id, 'attendance_date' => $attendance_date]) }}"
                               class="btn btn-sm mr-2 mb-2 {{ ($tt_id == $tt_item->id || ($subject_id == $tt_item->subject_id && $ts_id == $tt_item->ts_id)) ? 'btn-success' : 'btn-outline-secondary' }}">
                                <strong>{{ $tt_item->day ?: $tt_item->exam_date }}</strong>:
                                {{ $tt_item->subject->name ?? 'Subject' }}
                                <span class="badge badge-light ml-1">{{ $tt_item->time_slot->full ?? '' }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Per-Student Attendance Marking Form --}}
        <div class="card">
            <div class="card-header header-elements-inline">
                <h6 class="card-title font-weight-bold">
                    Mark Student Attendance — {{ $my_class->name }}
                    @if($section_id && $class_sections->where('id', $section_id)->first())
                        (Section {{ $class_sections->where('id', $section_id)->first()->name }})
                    @endif
                    — <span class="text-primary">{{ date('l, d M Y', strtotime($attendance_date)) }}</span>
                </h6>
                <div class="header-elements">
                    <button type="button" onclick="setAllAttendance('present')" class="btn btn-sm btn-outline-success mr-2">
                        <i class="icon-checkmark3 mr-1"></i> Mark All Present
                    </button>
                    <button type="button" onclick="setAllAttendance('absent')" class="btn btn-sm btn-outline-danger">
                        <i class="icon-cross2 mr-1"></i> Mark All Absent
                    </button>
                </div>
            </div>

            <div class="card-body">
                @if($students->count() > 0)
                    <form method="post" action="{{ route('attendance.store') }}">
                        @csrf
                        <input type="hidden" name="my_class_id" value="{{ $my_class_id }}">
                        <input type="hidden" name="section_id" value="{{ $section_id }}">
                        <input type="hidden" name="subject_id" value="{{ $subject_id }}">
                        <input type="hidden" name="ttr_id" value="{{ $ttr_id }}">
                        <input type="hidden" name="ts_id" value="{{ $ts_id }}">
                        <input type="hidden" name="tt_id" value="{{ $tt_id }}">
                        <input type="hidden" name="attendance_date" value="{{ $attendance_date }}">

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5%">S/N</th>
                                        <th width="22%">Student Name</th>
                                        <th width="14%">Adm No / Section</th>
                                        <th width="24%" class="text-center">Attendance Status ({{ $attendance_date }})</th>
                                        <th width="20%" class="text-center">Overall Attendance % (Tap to View History)</th>
                                        <th width="15%">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $sr)
                                        @php
                                            $sid = $sr->user_id;
                                            $existing = $existing_records->get($sid);
                                            $currentStatus = $existing ? $existing->status : 'present';
                                            $stats = $student_stats[$sid] ?? ['total' => 0, 'present' => 0, 'absent' => 0, 'percentage' => 0, 'present_list' => collect(), 'absent_list' => collect()];
                                            $badgeClass = $stats['percentage'] >= 75 ? 'badge-success' : ($stats['percentage'] >= 50 ? 'badge-warning' : 'badge-danger');
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="font-weight-semibold">
                                                {{ $sr->user->name ?? 'Student' }}
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $sr->adm_no }}</span>
                                                @if($sr->section)
                                                    <span class="badge badge-info ml-1">{{ $sr->section->name }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                    <label class="btn btn-sm {{ $currentStatus === 'present' ? 'btn-success active' : 'btn-outline-success' }} att-label-present">
                                                        <input type="radio" class="att-radio-present" name="statuses[{{ $sid }}]" value="present" {{ $currentStatus === 'present' ? 'checked' : '' }}>
                                                        <i class="icon-checkmark3 mr-1"></i> Present
                                                    </label>
                                                    <label class="btn btn-sm {{ $currentStatus === 'absent' ? 'btn-danger active' : 'btn-outline-danger' }} att-label-absent">
                                                        <input type="radio" class="att-radio-absent" name="statuses[{{ $sid }}]" value="absent" {{ $currentStatus === 'absent' ? 'checked' : '' }}>
                                                        <i class="icon-cross2 mr-1"></i> Absent
                                                    </label>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <a href="#student-att-modal-{{ $sid }}" data-toggle="modal" class="badge {{ $badgeClass }} font-size-sm px-2 py-1" title="Tap to view Present & Absent classes">
                                                    {{ $stats['percentage'] }}% ({{ $stats['present'] }}P / {{ $stats['absent'] }}A)
                                                </a>
                                            </td>
                                            <td>
                                                <input type="text" name="remarks[{{ $sid }}]" value="{{ $existing->remarks ?? '' }}" class="form-control form-control-sm" placeholder="Optional note...">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="text-right mt-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="icon-check mr-2"></i> Save Class Attendance
                            </button>
                        </div>
                    </form>

                    {{-- Per-Student Present / Absent Classes Modals --}}
                    @foreach($students as $sr)
                        @php
                            $sid = $sr->user_id;
                            $stats = $student_stats[$sid] ?? ['total' => 0, 'present' => 0, 'absent' => 0, 'percentage' => 0, 'present_list' => collect(), 'absent_list' => collect()];
                        @endphp
                        <div class="modal fade" id="student-att-modal-{{ $sid }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header bg-dark text-white">
                                        <h6 class="modal-title font-weight-bold">
                                            Attendance History — {{ $sr->user->name ?? 'Student' }} ({{ $stats['percentage'] }}%)
                                        </h6>
                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <ul class="nav nav-tabs nav-tabs-highlight">
                                            <li class="nav-item">
                                                <a href="#st-present-{{ $sid }}" class="nav-link active text-success font-weight-semibold" data-toggle="tab">
                                                    Present Classes ({{ $stats['present'] }})
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="#st-absent-{{ $sid }}" class="nav-link text-danger font-weight-semibold" data-toggle="tab">
                                                    Absent Classes ({{ $stats['absent'] }})
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane fade show active" id="st-present-{{ $sid }}">
                                                @if($stats['present_list']->count() > 0)
                                                    <table class="table table-sm table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Date</th>
                                                                <th>Subject / Class</th>
                                                                <th>Time Slot</th>
                                                                <th>Marked By</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($stats['present_list'] as $pr)
                                                                <tr>
                                                                    <td>{{ date('D, d M Y', strtotime($pr->attendance_date)) }}</td>
                                                                    <td>{{ $pr->subject->name ?? 'General / Homeroom' }}</td>
                                                                    <td>{{ $pr->time_slot->full ?? 'Full Day' }}</td>
                                                                    <td>{{ $pr->teacher->name ?? 'Teacher' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @else
                                                    <p class="text-muted text-center py-3">No present class records yet.</p>
                                                @endif
                                            </div>
                                            <div class="tab-pane fade" id="st-absent-{{ $sid }}">
                                                @if($stats['absent_list']->count() > 0)
                                                    <table class="table table-sm table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Date</th>
                                                                <th>Subject / Class</th>
                                                                <th>Time Slot</th>
                                                                <th>Remarks</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($stats['absent_list'] as $ab)
                                                                <tr>
                                                                    <td>{{ date('D, d M Y', strtotime($ab->attendance_date)) }}</td>
                                                                    <td>{{ $ab->subject->name ?? 'General / Homeroom' }}</td>
                                                                    <td>{{ $ab->time_slot->full ?? 'Full Day' }}</td>
                                                                    <td>{{ $ab->remarks ?: '-' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @else
                                                    <p class="text-muted text-center py-3">No absent class records.</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-warning text-center">
                        No active students found in {{ $my_class->name }}.
                    </div>
                @endif
            </div>
        </div>
    @endif

    <script>
        function setAllAttendance(status) {
            if (status === 'present') {
                document.querySelectorAll('.att-radio-present').forEach(function (el) { el.checked = true; });
                document.querySelectorAll('.att-label-present').forEach(function (el) {
                    el.classList.add('btn-success', 'active');
                    el.classList.remove('btn-outline-success');
                });
                document.querySelectorAll('.att-label-absent').forEach(function (el) {
                    el.classList.remove('btn-danger', 'active');
                    el.classList.add('btn-outline-danger');
                });
            } else {
                document.querySelectorAll('.att-radio-absent').forEach(function (el) { el.checked = true; });
                document.querySelectorAll('.att-label-absent').forEach(function (el) {
                    el.classList.add('btn-danger', 'active');
                    el.classList.remove('btn-outline-danger');
                });
                document.querySelectorAll('.att-label-present').forEach(function (el) {
                    el.classList.remove('btn-success', 'active');
                    el.classList.add('btn-outline-success');
                });
            }
        }
    </script>
@endsection
