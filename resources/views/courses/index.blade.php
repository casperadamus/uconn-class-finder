<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UConn Courses</title>
    
</head>
<body>
    <div class="container">

        @foreach($courses as $course)
        <div class="course-card">
            <div class="course-header">
                <div class="course-title">
                    <div class="course-code">{{ $course->code }}</div>
                    <div class="course-name">{{ $course->title }}</div>
                </div>
                <div class="course-meta">
                    @if($course->credits)
                        <div class="meta-item"> {{ $course->credits }} credits</div>
                    @endif
                    @if($course->campus)
                        <div class="meta-item"> {{ $course->campus }}</div>
                    @endif
                </div>
            </div>

            @if($course->description)
            <div style="color: #666; font-size: 0.9rem; margin-bottom: 10px; line-height: 1.5;">
                {{ Str::limit($course->description, 200) }}
            </div>
            @endif

            <div class="sections-summary">
                @php
                    $lectures = $course->sections->whereIn('type', ['LEC', 'LSA']); // Include standalone lectures
                    $labs = $course->sections->where('type', 'LAB');
                    $discussions = $course->sections->where('type', 'DIS');
                    $seminars = $course->sections->whereIn('type', ['SEM', 'THE', 'IND']); // Other types
                @endphp

                @if($lectures->count() > 0)
                <div class="section-group">
                    <h4>Lectures ({{ $lectures->count() }})</h4>
                    @foreach($lectures->take(3) as $section)
                    <div class="section-item {{ $section->status == 'A' ? 'available' : 'full' }}">
                        <span>
                            CRN {{ $section->crn }} - {{ $section->section_number }}
                            @if($section->meeting_time_display)
                                <br><small>{{ $section->meeting_time_display }}</small>
                            @endif
                        </span>
                        @if($section->max_enrollment > 0)
                        <span class="seats {{ $section->seats_available == 0 ? 'seats-none' : '' }}">
                            {{ $section->seats_available }}/{{ $section->max_enrollment }}
                        </span>
                        @else
                        <span class="badge {{ $section->status == 'A' ? 'badge-success' : 'badge-danger' }}">
                            {{ $section->status == 'A' ? 'Open' : 'Full' }}
                        </span>
                        @endif
                    </div>
                    @endforeach
                    @if($lectures->count() > 3)
                        <small style="color: #666;">+ {{ $lectures->count() - 3 }} more</small>
                    @endif
                </div>
                @endif

                @if($labs->count() > 0)
                <div class="section-group">
                    <h4>Labs ({{ $labs->count() }})</h4>
                    @foreach($labs->take(3) as $section)
                    <div class="section-item {{ $section->status == 'A' ? 'available' : 'full' }}">
                        <span>
                            CRN {{ $section->crn }} - {{ $section->section_number }}
                            @if($section->meeting_time_display)
                                <br><small>{{ $section->meeting_time_display }}</small>
                            @endif
                        </span>
                        @if($section->max_enrollment > 0)
                        <span class="seats {{ $section->seats_available == 0 ? 'seats-none' : '' }}">
                            {{ $section->seats_available }}/{{ $section->max_enrollment }}
                        </span>
                        @else
                        <span class="badge {{ $section->status == 'A' ? 'badge-success' : 'badge-danger' }}">
                            {{ $section->status == 'A' ? 'Open' : 'Full' }}
                        </span>
                        @endif
                    </div>
                    @endforeach
                    @if($labs->count() > 3)
                        <small style="color: #666;">+ {{ $labs->count() - 3 }} more</small>
                    @endif
                </div>
                @endif

                @if($discussions->count() > 0)
                <div class="section-group">
                    <h4>Discussions ({{ $discussions->count() }})</h4>
                    @foreach($discussions->take(3) as $section)
                    <div class="section-item {{ $section->status == 'A' ? 'available' : 'full' }}">
                        <span>
                            CRN {{ $section->crn }} - {{ $section->section_number }}
                            @if($section->meeting_time_display)
                                <br><small>{{ $section->meeting_time_display }}</small>
                            @endif
                        </span>
                        @if($section->max_enrollment > 0)
                        <span class="seats {{ $section->seats_available == 0 ? 'seats-none' : '' }}">
                            {{ $section->seats_available }}/{{ $section->max_enrollment }}
                        </span>
                        @else
                        <span class="badge {{ $section->status == 'A' ? 'badge-success' : 'badge-danger' }}">
                            {{ $section->status == 'A' ? 'Open' : 'Full' }}
                        </span>
                        @endif
                    </div>
                    @endforeach
                    @if($discussions->count() > 3)
                        <small style="color: #666;">+ {{ $discussions->count() - 3 }} more</small>
                    @endif
                </div>
                @endif

                @if($seminars->count() > 0)
                <div class="section-group">
                    <h4>Seminars ({{ $seminars->count() }})</h4>
                    @foreach($seminars->take(3) as $section)
                    <div class="section-item {{ $section->status == 'A' ? 'available' : 'full' }}">
                        <span>
                            CRN {{ $section->crn }} - {{ $section->section_number }}
                            @if($section->meeting_time_display)
                                <br><small>{{ $section->meeting_time_display }}</small>
                            @endif
                        </span>
                        @if($section->max_enrollment > 0)
                        <span class="seats {{ $section->seats_available == 0 ? 'seats-none' : '' }}">
                            {{ $section->seats_available }}/{{ $section->max_enrollment }}
                        </span>
                        @else
                        <span class="badge {{ $section->status == 'A' ? 'badge-success' : 'badge-danger' }}">
                            {{ $section->status == 'A' ? 'Open' : 'Full' }}
                        </span>
                        @endif
                    </div>
                    @endforeach
                    @if($seminars->count() > 3)
                        <small style="color: #666;">+ {{ $seminars->count() - 3 }} more</small>
                    @endif
                </div>
                @endif
            </div>

            <a href="/courses/{{ $course->code }}" class="view-details">View All Sections →</a>
        </div>
        @endforeach

        <div class="pagination">
            {!! $courses->links() !!}
        </div>
    </div>
</body>
</html>
