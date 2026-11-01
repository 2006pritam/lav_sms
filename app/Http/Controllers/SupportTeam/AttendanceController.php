<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\StudentRecord;
use App\Models\Subject;
use App\Models\TimeSlot;
use App\Models\TimeTable;
use App\Models\TimeTableRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    protected $year;

    public function __construct()
    {
        // Resolved per request, once the signed-in user's school is known (a controller
        // is created before the school is set, so reading it here would use the wrong school).
        $this->middleware(function ($request, $next) {
            $this->year = Qs::getCurrentSession();
            return $next($request);
        });
        $this->middleware('teamSAT', ['except' => ['student']]);
    }

    public function index(Request $req)
    {
        $my_class_id = $req->query('class_id') ?: $req->query('my_class_id');
        $section_id = $req->query('section_id');

        if ($my_class_id) {
            return $this->renderAttendancePage($req, (int) $my_class_id, $section_id ? (int) $section_id : null);
        }

        $d['selected'] = false;
        $d['my_classes'] = MyClass::orderBy('name')->get();
        $d['sections'] = Section::orderBy('name')->get();
        $d['subjects'] = Qs::userIsTeacher()
            ? Subject::where('teacher_id', Auth::id())->orderBy('name')->get()
            : Subject::orderBy('name')->get();
        if ($d['subjects']->isEmpty()) {
            $d['subjects'] = Subject::orderBy('name')->get();
        }
        $d['tt_records'] = TimeTableRecord::whereNull('exam_id')
            ->with('my_class')
            ->orderBy('name')
            ->get();
        $d['attendance_date'] = date('Y-m-d');

        return view('pages.support_team.attendance.index', $d);
    }

    public function selector(Request $req)
    {
        $req->validate([
            'my_class_id' => 'required|exists:my_classes,id',
            'attendance_date' => 'required|date',
        ]);

        return redirect()->route('attendance.manage', [
            'class_id' => $req->my_class_id,
            'section_id' => $req->section_id,
            'subject_id' => $req->subject_id,
            'ttr_id' => $req->ttr_id,
            'ts_id' => $req->ts_id,
            'tt_id' => $req->tt_id,
            'attendance_date' => $req->attendance_date,
        ]);
    }

    public function manage(Request $req, $my_class_id, $section_id = null)
    {
        return $this->renderAttendancePage($req, (int) $my_class_id, $section_id ? (int) $section_id : null);
    }

    protected function renderAttendancePage(Request $req, int $my_class_id, ?int $section_id = null)
    {
        $attendance_date = $req->query('attendance_date', date('Y-m-d'));
        $subject_id = $req->query('subject_id') ? (int) $req->query('subject_id') : null;
        $ttr_id = $req->query('ttr_id') ? (int) $req->query('ttr_id') : null;
        $ts_id = $req->query('ts_id') ? (int) $req->query('ts_id') : null;
        $tt_id = $req->query('tt_id') ? (int) $req->query('tt_id') : null;

        $my_class = MyClass::findOrFail($my_class_id);

        if ($tt_id) {
            $selected_tt = TimeTable::with(['subject', 'time_slot', 'tt_record'])->find($tt_id);
            if ($selected_tt) {
                $ttr_id = $ttr_id ?: $selected_tt->ttr_id;
                $ts_id = $ts_id ?: $selected_tt->ts_id;
                $subject_id = $subject_id ?: $selected_tt->subject_id;
            }
        }

        // Find the respected Class Timetable Record (non-exam timetable for this class)
        $ttr = $ttr_id
            ? TimeTableRecord::find($ttr_id)
            : TimeTableRecord::where('my_class_id', $my_class_id)
                ->whereNull('exam_id')
                ->orderByDesc('id')
                ->first();

        if ($ttr && !$ttr_id) {
            $ttr_id = $ttr->id;
        }

        $class_sections = Section::where('my_class_id', $my_class_id)->orderBy('name')->get();
        if (!$section_id && $req->query('section_id')) {
            $section_id = (int) $req->query('section_id');
        }

        $class_subjects = Qs::userIsTeacher()
            ? Subject::where('my_class_id', $my_class_id)->where('teacher_id', Auth::id())->orderBy('name')->get()
            : Subject::where('my_class_id', $my_class_id)->orderBy('name')->get();

        if ($class_subjects->isEmpty()) {
            $class_subjects = Subject::where('my_class_id', $my_class_id)->orderBy('name')->get();
        }

        $time_slots = $ttr ? TimeSlot::where('ttr_id', $ttr->id)->orderBy('timestamp_from')->get() : collect();
        $timetable_entries = $ttr
            ? TimeTable::with(['subject', 'time_slot'])
                ->where('ttr_id', $ttr->id)
                ->orderBy('timestamp_from')
                ->get()
            : collect();

        $studentQuery = StudentRecord::where('my_class_id', $my_class_id)->where('grad', 0);
        if ($section_id) {
            $studentQuery->where('section_id', $section_id);
        }
        $students = $studentQuery->with(['user', 'section'])->get()->sortBy('user.name');

        // Load existing attendance records for this class/date/slot/subject
        $existingQuery = Attendance::where('my_class_id', $my_class_id)
            ->where('attendance_date', $attendance_date)
            ->where('session', $this->year);

        if ($section_id) {
            $existingQuery->where('section_id', $section_id);
        }
        if ($subject_id) {
            $existingQuery->where('subject_id', $subject_id);
        } else {
            $existingQuery->whereNull('subject_id');
        }
        if ($ts_id) {
            $existingQuery->where('ts_id', $ts_id);
        } else {
            $existingQuery->whereNull('ts_id');
        }

        $existing_records = $existingQuery->get()->keyBy('student_id');

        // Compute per-student attendance stats and present/absent class history for this class
        $studentIds = $students->pluck('user_id')->all();
        $allStudentAttendances = Attendance::with(['subject', 'time_slot', 'teacher'])
            ->whereIn('student_id', $studentIds)
            ->where('my_class_id', $my_class_id)
            ->where('session', $this->year)
            ->orderByDesc('attendance_date')
            ->get()
            ->groupBy('student_id');

        $student_stats = [];
        foreach ($students as $sr) {
            $sid = $sr->user_id;
            $records = $allStudentAttendances->get($sid, collect());
            if ($subject_id) {
                $filtered = $records->where('subject_id', $subject_id);
            } else {
                $filtered = $records;
            }
            $total = $filtered->count();
            $present = $filtered->where('status', 'present')->count();
            $absent = $filtered->where('status', 'absent')->count();
            $pct = $total > 0 ? round(($present / $total) * 100, 1) : 0.0;

            $student_stats[$sid] = [
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'percentage' => $pct,
                'present_list' => $filtered->where('status', 'present')->values(),
                'absent_list' => $filtered->where('status', 'absent')->values(),
            ];
        }

        $d = [
            'selected' => true,
            'my_classes' => MyClass::orderBy('name')->get(),
            'sections' => Section::orderBy('name')->get(),
            'class_sections' => $class_sections,
            'subjects' => $class_subjects,
            'my_class' => $my_class,
            'my_class_id' => $my_class_id,
            'section_id' => $section_id,
            'subject_id' => $subject_id,
            'ttr' => $ttr,
            'ttr_id' => $ttr_id,
            'ts_id' => $ts_id,
            'tt_id' => $tt_id,
            'time_slots' => $time_slots,
            'timetable_entries' => $timetable_entries,
            'attendance_date' => $attendance_date,
            'students' => $students,
            'existing_records' => $existing_records,
            'student_stats' => $student_stats,
            'tt_records' => TimeTableRecord::whereNull('exam_id')->with('my_class')->orderBy('name')->get(),
        ];

        return view('pages.support_team.attendance.index', $d);
    }

    public function store(Request $req)
    {
        if (!Qs::userIsTeacher()) {
            return back()->with('pop_error', 'Only a Teacher account can mark or update student attendance.');
        }

        $req->validate([
            'my_class_id' => 'required|exists:my_classes,id',
            'attendance_date' => 'required|date',
            'statuses' => 'required|array',
        ]);

        $my_class_id = (int) $req->my_class_id;
        $section_id = $req->section_id ? (int) $req->section_id : null;
        $subject_id = $req->subject_id ? (int) $req->subject_id : null;
        $ttr_id = $req->ttr_id ? (int) $req->ttr_id : null;
        $ts_id = $req->ts_id ? (int) $req->ts_id : null;
        $tt_id = $req->tt_id ? (int) $req->tt_id : null;
        $attendance_date = $req->attendance_date;
        $remarks = $req->input('remarks', []);

        foreach ($req->statuses as $student_id => $status) {
            $student_id = (int) $student_id;
            $status = ($status === 'absent') ? 'absent' : 'present';
            $student_sr = StudentRecord::where('user_id', $student_id)->where('my_class_id', $my_class_id)->first();
            $sec_id = $section_id ?: ($student_sr ? $student_sr->section_id : null);

            $match = [
                'student_id' => $student_id,
                'my_class_id' => $my_class_id,
                'subject_id' => $subject_id,
                'ts_id' => $ts_id,
                'attendance_date' => $attendance_date,
                'session' => $this->year,
            ];

            Attendance::updateOrCreate($match, [
                'section_id' => $sec_id,
                'ttr_id' => $ttr_id,
                'tt_id' => $tt_id,
                'teacher_id' => Auth::id(),
                'status' => $status,
                'remarks' => $remarks[$student_id] ?? null,
            ]);
        }

        return redirect()->route('attendance.manage', [
            'class_id' => $my_class_id,
            'section_id' => $section_id,
            'subject_id' => $subject_id,
            'ttr_id' => $ttr_id,
            'ts_id' => $ts_id,
            'tt_id' => $tt_id,
            'attendance_date' => $attendance_date,
        ])->with('flash_success', 'Class attendance saved for each student.');
    }

    public function student(Request $req, $student_id = null)
    {
        if ($student_id) {
            $decoded = Qs::decodeHash($student_id);
            $student_id = $decoded ? (int) $decoded : (int) $student_id;
        } else {
            $student_id = Auth::id();
        }

        // Prevent other students/parents from viewing attendance of unrelated students
        if (Auth::id() !== $student_id && !Qs::userIsTeamSAT() && !Qs::userIsMyChild($student_id, Auth::id())) {
            return redirect()->route('dashboard')->with('pop_error', __('msg.denied'));
        }

        $sr = StudentRecord::with(['user', 'my_class', 'section'])->where('user_id', $student_id)->first();
        $data = self::buildStudentAttendanceData($student_id, $this->year, $sr);
        $data['sr'] = $sr;

        return view('pages.support_team.attendance.student', $data);
    }

    public static function buildStudentAttendanceData(int $student_id, string $session, $sr = null): array
    {
        if (!$sr) {
            $sr = StudentRecord::with(['user', 'my_class', 'section'])->where('user_id', $student_id)->first();
        }

        $records = Attendance::with(['subject', 'my_class', 'section', 'time_slot', 'tt_record', 'teacher'])
            ->where('student_id', $student_id)
            ->where('session', $session)
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->get();

        $total = $records->count();
        $present_classes = $records->where('status', 'present')->values();
        $absent_classes = $records->where('status', 'absent')->values();
        $present_count = $present_classes->count();
        $absent_count = $absent_classes->count();
        $percentage = $total > 0 ? round(($present_count / $total) * 100, 1) : 0.0;

        $subject_attendance = [];
        foreach ($records->groupBy(function ($item) {
            return $item->subject ? $item->subject->name : 'General / Homeroom';
        }) as $subName => $group) {
            $subTotal = $group->count();
            $subPresent = $group->where('status', 'present')->count();
            $subAbsent = $group->where('status', 'absent')->count();
            $subject_attendance[] = [
                'subject' => $subName,
                'total' => $subTotal,
                'present' => $subPresent,
                'absent' => $subAbsent,
                'percentage' => $subTotal > 0 ? round(($subPresent / $subTotal) * 100, 1) : 0.0,
            ];
        }

        $student_ttr = null;
        if ($sr && $sr->my_class_id) {
            $student_ttr = TimeTableRecord::where('my_class_id', $sr->my_class_id)
                ->whereNull('exam_id')
                ->orderByDesc('id')
                ->first();
        }

        return [
            'attendance_total' => $total,
            'attendance_present_count' => $present_count,
            'attendance_absent_count' => $absent_count,
            'attendance_percentage' => $percentage,
            'present_classes' => $present_classes,
            'absent_classes' => $absent_classes,
            'subject_attendance' => $subject_attendance,
            'student_ttr' => $student_ttr,
        ];
    }
}
