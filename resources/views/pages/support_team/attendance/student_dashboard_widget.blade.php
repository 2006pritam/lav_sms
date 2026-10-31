<div class="card border-primary mb-4">
    <div class="card-header bg-primary text-white header-elements-inline">
        <h5 class="card-title font-weight-bold mb-0">
            <i class="icon-calendar52 mr-2"></i> My Class Attendance Overview
            @if(isset($sr) && $sr && $sr->my_class)
                — {{ $sr->my_class->name }} {{ $sr->section ? '('.$sr->section->name.')' : '' }}
            @endif
        </h5>
        <div class="header-elements">
            @if(isset($student_ttr) && $student_ttr)
                <a href="{{ route('ttr.show', $student_ttr->id) }}" class="btn btn-sm btn-light text-primary font-weight-bold">
                    <i class="icon-table2 mr-1"></i> Open Class Timetable
                </a>
            @endif
        </div>
    </div>

    <div class="card-body">
        @php
            $pct = $attendance_percentage ?? 0;
            $presentCnt = $attendance_present_count ?? 0;
            $absentCnt = $attendance_absent_count ?? 0;
            $totalCnt = $attendance_total ?? 0;
            $barClass = $pct >= 75 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger');
        @endphp

        <p class="text-muted font-size-sm mb-3">
            Tap on <strong>Attendance Percentage</strong>, <strong>Present Classes</strong>, or <strong>Absent Classes</strong> below to view your detailed class-by-class attendance records:
        </p>

        <div class="row">
            {{-- Overall Percentage Card (Tap to show Subject Breakdown) --}}
            <div class="col-sm-6 col-xl-4">
                <a href="#student-attendance-details" onclick="showStudentAttendanceTab('tab-att-subjects')" class="text-decoration-none d-block">
                    <div class="card card-body {{ $barClass }} has-bg-image text-white mb-3" style="cursor: pointer;">
                        <div class="media">
                            <div class="media-body">
                                <h2 class="mb-0 font-weight-bold">{{ $pct }}%</h2>
                                <span class="text-uppercase font-size-xs font-weight-semibold">Attendance Percentage (Tap for Subjects)</span>
                            </div>
                            <div class="ml-3 align-self-center">
                                <i class="icon-pie-chart2 icon-3x opacity-75"></i>
                            </div>
                        </div>
                        <div class="progress bg-white alpha-2 mt-2" style="height: 6px;">
                            <div class="progress-bar bg-white" style="width: {{ min(100, $pct) }}%"></div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Present Classes Card (Tap to show Present Classes) --}}
            <div class="col-sm-6 col-xl-4">
                <a href="#student-attendance-details" onclick="showStudentAttendanceTab('tab-att-present')" class="text-decoration-none d-block">
                    <div class="card card-body bg-success-400 has-bg-image text-white mb-3" style="cursor: pointer;">
                        <div class="media">
                            <div class="media-body">
                                <h2 class="mb-0 font-weight-bold">{{ $presentCnt }} <small class="font-size-sm">/ {{ $totalCnt }}</small></h2>
                                <span class="text-uppercase font-size-xs font-weight-semibold">Present Classes (Tap to View Present)</span>
                            </div>
                            <div class="ml-3 align-self-center">
                                <i class="icon-checkmark-circle icon-3x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Absent Classes Card (Tap to show Absent Classes) --}}
            <div class="col-sm-6 col-xl-4">
                <a href="#student-attendance-details" onclick="showStudentAttendanceTab('tab-att-absent')" class="text-decoration-none d-block">
                    <div class="card card-body bg-danger-400 has-bg-image text-white mb-3" style="cursor: pointer;">
                        <div class="media">
                            <div class="media-body">
                                <h2 class="mb-0 font-weight-bold">{{ $absentCnt }} <small class="font-size-sm">/ {{ $totalCnt }}</small></h2>
                                <span class="text-uppercase font-size-xs font-weight-semibold">Absent Classes (Tap to View Absent)</span>
                            </div>
                            <div class="ml-3 align-self-center">
                                <i class="icon-cancel-circle2 icon-3x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Interactive Tabbed Breakdown: Present Classes / Absent Classes / Subject Percentage --}}
        <div id="student-attendance-details" class="mt-2">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item">
                    <a id="link-tab-att-present" href="#tab-att-present" class="nav-link active font-weight-bold text-success" data-toggle="tab">
                        <i class="icon-checkmark3 mr-1"></i> Present Classes ({{ $presentCnt }})
                    </a>
                </li>
                <li class="nav-item">
                    <a id="link-tab-att-absent" href="#tab-att-absent" class="nav-link font-weight-bold text-danger" data-toggle="tab">
                        <i class="icon-cross2 mr-1"></i> Absent Classes ({{ $absentCnt }})
                    </a>
                </li>
                <li class="nav-item">
                    <a id="link-tab-att-subjects" href="#tab-att-subjects" class="nav-link font-weight-bold text-primary" data-toggle="tab">
                        <i class="icon-books mr-1"></i> Subject-wise Attendance %
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                {{-- Present Classes List --}}
                <div class="tab-pane fade show active" id="tab-att-present">
                    @if(isset($present_classes) && $present_classes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="bg-success text-white">
                                    <tr>
                                        <th>#</th>
                                        <th>Date &amp; Day</th>
                                        <th>Subject / Class</th>
                                        <th>Time Slot</th>
                                        <th>Teacher</th>
                                        <th>Status</th>
                                        <th>Timetable</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($present_classes as $pr)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="font-weight-semibold">{{ date('D, d M Y', strtotime($pr->attendance_date)) }}</td>
                                            <td>{{ $pr->subject->name ?? 'General / Homeroom' }}</td>
                                            <td>{{ $pr->time_slot->full ?? 'Full Day' }}</td>
                                            <td>{{ $pr->teacher->name ?? 'Class Teacher' }}</td>
                                            <td><span class="badge badge-success">PRESENT</span></td>
                                            <td>
                                                @if($pr->ttr_id)
                                                    <a href="{{ route('ttr.show', $pr->ttr_id) }}" class="btn btn-xs btn-outline-primary">
                                                        <i class="icon-table2 mr-1"></i> Timetable
                                                    </a>
                                                @elseif(isset($student_ttr) && $student_ttr)
                                                    <a href="{{ route('ttr.show', $student_ttr->id) }}" class="btn btn-xs btn-outline-primary">
                                                        <i class="icon-table2 mr-1"></i> Timetable
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center mb-0">
                            No present class records recorded yet for this session.
                        </div>
                    @endif
                </div>

                {{-- Absent Classes List --}}
                <div class="tab-pane fade" id="tab-att-absent">
                    @if(isset($absent_classes) && $absent_classes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="bg-danger text-white">
                                    <tr>
                                        <th>#</th>
                                        <th>Date &amp; Day</th>
                                        <th>Subject / Class</th>
                                        <th>Time Slot</th>
                                        <th>Teacher</th>
                                        <th>Remarks</th>
                                        <th>Status</th>
                                        <th>Timetable</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($absent_classes as $ab)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="font-weight-semibold">{{ date('D, d M Y', strtotime($ab->attendance_date)) }}</td>
                                            <td>{{ $ab->subject->name ?? 'General / Homeroom' }}</td>
                                            <td>{{ $ab->time_slot->full ?? 'Full Day' }}</td>
                                            <td>{{ $ab->teacher->name ?? 'Class Teacher' }}</td>
                                            <td>{{ $ab->remarks ?: '-' }}</td>
                                            <td><span class="badge badge-danger">ABSENT</span></td>
                                            <td>
                                                @if($ab->ttr_id)
                                                    <a href="{{ route('ttr.show', $ab->ttr_id) }}" class="btn btn-xs btn-outline-primary">
                                                        <i class="icon-table2 mr-1"></i> Timetable
                                                    </a>
                                                @elseif(isset($student_ttr) && $student_ttr)
                                                    <a href="{{ route('ttr.show', $student_ttr->id) }}" class="btn btn-xs btn-outline-primary">
                                                        <i class="icon-table2 mr-1"></i> Timetable
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-success text-center mb-0">
                            Great job! You have 0 absent classes recorded for this session.
                        </div>
                    @endif
                </div>

                {{-- Subject-wise Attendance Percentage --}}
                <div class="tab-pane fade" id="tab-att-subjects">
                    @if(isset($subject_attendance) && count($subject_attendance) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>Subject / Class</th>
                                        <th class="text-center">Total Classes</th>
                                        <th class="text-center">Present</th>
                                        <th class="text-center">Absent</th>
                                        <th class="text-center">Attendance %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subject_attendance as $subRow)
                                        <tr>
                                            <td class="font-weight-semibold">{{ $subRow['subject'] }}</td>
                                            <td class="text-center">{{ $subRow['total'] }}</td>
                                            <td class="text-center text-success font-weight-bold">{{ $subRow['present'] }}</td>
                                            <td class="text-center text-danger font-weight-bold">{{ $subRow['absent'] }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $subRow['percentage'] >= 75 ? 'badge-success' : ($subRow['percentage'] >= 50 ? 'badge-warning' : 'badge-danger') }} font-size-sm">
                                                    {{ $subRow['percentage'] }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center mb-0">
                            No subject attendance records recorded yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showStudentAttendanceTab(tabId) {
        var link = document.getElementById('link-' + tabId);
        if (link) {
            link.click();
        }
    }
</script>
