<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UConn Courses</title>
</head>
<body>
    <h1>UConn Courses</h1>

    @foreach($courses as $course)
        <article>
            <h2>{{ $course->code }} - {{ $course->title }}</h2>
            
            <p>
                @if($course->credits)
                    {{ $course->credits }} credits
                @endif
                @if($course->campus)
                    - {{ $course->campus }}
                @endif
            </p>

            @if($course->description)
                <p>{{ Str::limit($course->description, 200) }}</p>
            @endif

            @php
                $lectures = $course->sections->whereIn('type', ['LEC', 'LSA']);
                $labs = $course->sections->where('type', 'LAB');
                $discussions = $course->sections->where('type', 'DIS');
                $seminars = $course->sections->whereIn('type', ['SEM', 'THE', 'IND']);
            @endphp

            @if($lectures->count() > 0)
                <h3>Lectures ({{ $lectures->count() }})</h3>
                <ul>
                    @foreach($lectures->take(3) as $section)
                        <li>
                            CRN {{ $section->crn }} - {{ $section->section_number }}
                            @if($section->meeting_time_display)
                                - {{ $section->meeting_time_display }}
                            @endif
                            @if($section->max_enrollment > 0)
                                - {{ $section->seats_available }}/{{ $section->max_enrollment }} seats
                            @else
                                - {{ $section->status == 'A' ? 'Open' : 'Full' }}
                            @endif
                        </li>
                    @endforeach
                    @if($lectures->count() > 3)
                        <li><em>+ {{ $lectures->count() - 3 }} more</em></li>
                    @endif
                </ul>
            @endif

            @if($labs->count() > 0)
                <h3>Labs ({{ $labs->count() }})</h3>
                <ul>
                    @foreach($labs->take(3) as $section)
                        <li>
                            CRN {{ $section->crn }} - {{ $section->section_number }}
                            @if($section->meeting_time_display)
                                - {{ $section->meeting_time_display }}
                            @endif
                            @if($section->max_enrollment > 0)
                                - {{ $section->seats_available }}/{{ $section->max_enrollment }} seats
                            @else
                                - {{ $section->status == 'A' ? 'Open' : 'Full' }}
                            @endif
                        </li>
                    @endforeach
                    @if($labs->count() > 3)
                        <li><em>+ {{ $labs->count() - 3 }} more</em></li>
                    @endif
                </ul>
            @endif

            @if($discussions->count() > 0)
                <h3>Discussions ({{ $discussions->count() }})</h3>
                <ul>
                    @foreach($discussions->take(3) as $section)
                        <li>
                            CRN {{ $section->crn }} - {{ $section->section_number }}
                            @if($section->meeting_time_display)
                                - {{ $section->meeting_time_display }}
                            @endif
                            @if($section->max_enrollment > 0)
                                - {{ $section->seats_available }}/{{ $section->max_enrollment }} seats
                            @else
                                - {{ $section->status == 'A' ? 'Open' : 'Full' }}
                            @endif
                        </li>
                    @endforeach
                    @if($discussions->count() > 3)
                        <li><em>+ {{ $discussions->count() - 3 }} more</em></li>
                    @endif
                </ul>
            @endif

            @if($seminars->count() > 0)
                <h3>Seminars ({{ $seminars->count() }})</h3>
                <ul>
                    @foreach($seminars->take(3) as $section)
                        <li>
                            CRN {{ $section->crn }} - {{ $section->section_number }}
                            @if($section->meeting_time_display)
                                - {{ $section->meeting_time_display }}
                            @endif
                            @if($section->max_enrollment > 0)
                                - {{ $section->seats_available }}/{{ $section->max_enrollment }} seats
                            @else
                                - {{ $section->status == 'A' ? 'Open' : 'Full' }}
                            @endif
                        </li>
                    @endforeach
                    @if($seminars->count() > 3)
                        <li><em>+ {{ $seminars->count() - 3 }} more</em></li>
                    @endif
                </ul>
            @endif

            <p><a href="/courses/{{ $course->code }}">View All Sections →</a></p>
        </article>
    @endforeach

    {!! $courses->links() !!}

</body>
</html>
