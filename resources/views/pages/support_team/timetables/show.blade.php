@extends('layouts.master')
@section('page_title', 'View TimeTable')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <div class="row w-100 align-items-center">
                <div class="col-md-3"><h6 class="card-title mb-0"><strong>Name: </strong> {{ $ttr->name }}</h6></div>
                <div class="col-md-3"><h6 class="card-title mb-0"><strong>Class: </strong> {{ $my_class->name }}</h6></div>
                <div class="col-md-3"><h6 class="card-title mb-0"><strong>Year: </strong> {{ ($ttr->exam_id) ? 'Exam TimeTable' : 'Class TimeTable' }} {{ '('.$ttr->year.')' }}</h6></div>
                <div class="col-md-3 text-right">
                    @if(Qs::userIsTeamSAT() && !$ttr->exam_id)
                        <a href="{{ route('attendance.manage', ['class_id' => $my_class->id, 'ttr_id' => $ttr->id]) }}" class="btn btn-sm btn-primary font-weight-semibold">
                            <i class="icon-calendar52 mr-1"></i> Manage Class Attendance
                        </a>
                    @elseif(Qs::userIsStudent())
                        <a href="{{ route('attendance.student') }}" class="btn btn-sm btn-success font-weight-semibold">
                            <i class="icon-calendar52 mr-1"></i> My Attendance
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-responsive table-striped table-bordered">
                <thead>
                <tr>
                    <th rowspan="2">Time <i class="icon-arrow-right7 ml-2"></i> <br> Date<i class="icon-arrow-down7 ml-2"></i>
                    </th>
                    @foreach($time_slots as $tms)
                        <th rowspan="2">{{ $tms->time_from }} <br>
                        {{ $tms->time_to }}
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                    @foreach($days as $day)
                        <tr>
                            @if($ttr->exam_id)
                            <td><strong>{{ date('l', strtotime($day)) }} <br> {{ date('d/m/Y', strtotime($day)) }} </strong></td>
                            @else
                            <td><strong>{{ $day }}</strong></td>
                            @endif
                            @foreach($d_time->where('day', $day) as $dt)
                            <td class="text-center">
                                <div class="font-weight-semibold">{{ $dt['subject'] ?? '-' }}</div>
                                @if(Qs::userIsTeamSAT() && !$ttr->exam_id && !empty($dt['subject']))
                                    <a href="{{ route('attendance.manage', ['class_id' => $my_class->id, 'ttr_id' => $ttr->id, 'tt_id' => $dt['tt_id'] ?? null, 'subject_id' => $dt['subject_id'] ?? null, 'ts_id' => $dt['ts_id'] ?? null]) }}"
                                       class="badge badge-primary mt-1 d-inline-block" title="Take Attendance for {{ $dt['subject'] }}">
                                        <i class="icon-checkmark3 mr-1"></i> Attendance
                                    </a>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{--Print Button--}}
            <div class="text-center mt-4">
                <a target="_blank" href="{{ route('ttr.print', $ttr->id) }}" class="btn btn-danger btn-lg"><i class="icon-printer mr-2"></i> Print Timetable</a>
                @if(Qs::userIsTeamSAT() && !$ttr->exam_id)
                    <a href="{{ route('attendance.manage', ['class_id' => $my_class->id, 'ttr_id' => $ttr->id]) }}" class="btn btn-primary btn-lg ml-2">
                        <i class="icon-calendar52 mr-2"></i> Open Class Attendance
                    </a>
                @endif
            </div>
        </div>
    </div>

@endsection
