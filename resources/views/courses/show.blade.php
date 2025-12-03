<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $course->code }} - {{ $course->title }}</title>
</head>
<body>
    <p><a href="/courses">← Back to Courses</a></p>

    <header>
        <h1>{{ $course->title }}</h1>
        <p><strong>Course Code:</strong> {{ $course->code }}</p>

        @if($course->description)
            <p>{{ $course->description }}</p>
        @endif

        <ul>
            @if($course->credits)
                <li><strong>Credits:</strong> {{ $course->credits }}</li>
            @endif
            @if($course->campus)
                <li><strong>Campus:</strong> {{ $course->campus }}</li>
            @endif
            <li><strong>Term:</strong> {{ $course->term }}</li>
            <li><strong>Total Sections:</strong> {{ $course->sections->count() }}</li>
        </ul>

        @if($course->prerequisites)
            <p><strong>Prerequisites:</strong> {{ $course->prerequisites }}</p>
        @endif
    </header>

    <main>
        @if($lectures->count() > 0)
            <h2>Lecture Sections ({{ $lectures->count() }})</h2>
            <table>
                <thead>
                    <tr>
                        <th>CRN</th>
                        <th>Section</th>
                        <th>Meeting Time</th>
                        <th>Status</th>
                        <th>Seats</th>
                        <th>Linked Labs</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lectures as $section)
                    <tr>
                        <td><strong>{{ $section->crn }}</strong></td>
                        <td>{{ $section->type }} {{ $section->section_number }}</td>
                        <td>{{ $section->meeting_time_display ?? 'TBA' }}</td>
                        <td>{{ $section->status == 'A' ? 'Available' : 'Full' }}</td>
                        <td>
                            @if($section->max_enrollment > 0)
                                {{ $section->seats_available }}/{{ $section->max_enrollment }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $section->linked_crns ?? 'None' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($labs->count() > 0)
            <h2>Lab Sections ({{ $labs->count() }})</h2>
            <table>
                <thead>
                    <tr>
                        <th>CRN</th>
                        <th>Section</th>
                        <th>Meeting Time</th>
                        <th>Status</th>
                        <th>Seats</th>
                        <th>Linked To</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($labs as $section)
                    <tr>
                        <td><strong>{{ $section->crn }}</strong></td>
                        <td>{{ $section->type }} {{ $section->section_number }}</td>
                        <td>{{ $section->meeting_time_display ?? 'TBA' }}</td>
                        <td>{{ $section->status == 'A' ? 'Available' : 'Full' }}</td>
                        <td>
                            @if($section->max_enrollment > 0)
                                {{ $section->seats_available }}/{{ $section->max_enrollment }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $section->linked_crns ?? 'None' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($discussions->count() > 0)
            <h2>Discussion Sections ({{ $discussions->count() }})</h2>
            <table>
                <thead>
                    <tr>
                        <th>CRN</th>
                        <th>Section</th>
                        <th>Meeting Time</th>
                        <th>Status</th>
                        <th>Seats</th>
                        <th>Linked To</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($discussions as $section)
                    <tr>
                        <td><strong>{{ $section->crn }}</strong></td>
                        <td>{{ $section->type }} {{ $section->section_number }}</td>
                        <td>{{ $section->meeting_time_display ?? 'TBA' }}</td>
                        <td>{{ $section->status == 'A' ? 'Available' : 'Full' }}</td>
                        <td>
                            @if($section->max_enrollment > 0)
                                {{ $section->seats_available }}/{{ $section->max_enrollment }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $section->linked_crns ?? 'None' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($seminars->count() > 0)
            <h2>Seminar Sections ({{ $seminars->count() }})</h2>
            <table>
                <thead>
                    <tr>
                        <th>CRN</th>
                        <th>Section</th>
                        <th>Meeting Time</th>
                        <th>Status</th>
                        <th>Seats</th>
                        <th>Linked To</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($seminars as $section)
                    <tr>
                        <td><strong>{{ $section->crn }}</strong></td>
                        <td>{{ $section->type }} {{ $section->section_number }}</td>
                        <td>{{ $section->meeting_time_display ?? 'TBA' }}</td>
                        <td>{{ $section->status == 'A' ? 'Available' : 'Full' }}</td>
                        <td>
                            @if($section->max_enrollment > 0)
                                {{ $section->seats_available }}/{{ $section->max_enrollment }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $section->linked_crns ?? 'None' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($lectures->count() == 0 && $labs->count() == 0 && $discussions->count() == 0 && $seminars->count() == 0)
            <p><em>No sections available for this course.</em></p>
        @endif
    </main>

</body>
</html>
