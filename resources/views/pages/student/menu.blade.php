{{--Marksheet--}}
@if(Qs::moduleEnabled('exams'))
<li class="nav-item">
    <a href="{{ route('marks.year_select', Qs::hash(Auth::user()->id)) }}" class="nav-link {{ in_array(Route::currentRouteName(), ['marks.show', 'marks.year_selector', 'pins.enter']) ? 'active' : '' }}"><i class="icon-book"></i> <span>Marksheet</span></a>
</li>
@endif

{{--My Attendance--}}
@if(Qs::moduleEnabled('attendance'))
<li class="nav-item">
    <a href="{{ route('attendance.student') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['attendance.student']) ? 'active' : '' }}"><i class="icon-calendar52"></i> <span>My Attendance</span></a>
</li>
@endif
