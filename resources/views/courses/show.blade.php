<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $course->code }} - {{ $course->title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #000e2f;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
        .course-header {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 { 
            color: #000e2f;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        .course-code {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        .meta-row {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .description {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            line-height: 1.6;
            color: #333;
        }
        .sections-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #000e2f;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000e2f;
        }
        .sections-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .sections-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }
        .sections-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .sections-table tr:hover {
            background: #f8f9fa;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .status-available {
            background: #d4edda;
            color: #155724;
        }
        .status-full {
            background: #f8d7da;
            color: #721c24;
        }
        .seats-good {
            color: #28a745;
            font-weight: bold;
        }
        .seats-low {
            color: #ffc107;
            font-weight: bold;
        }
        .seats-none {
            color: #dc3545;
            font-weight: bold;
        }
        .linked-sections {
            font-size: 0.85rem;
            color: #666;
        }
        .type-badge {
            display: inline-block;
            padding: 3px 8px;
            background: #000e2f;
            color: white;
            border-radius: 3px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/courses" class="back-link">← Back to Courses</a>

        <div class="course-header">
            <h1>{{ $course->title }}</h1>
            <div class="course-code">{{ $course->code }}</div>

            @if($course->description)
            <div class="description">
                {{ $course->description }}
            </div>
            @endif

            <div class="meta-row">
                @if($course->credits)
                <div class="meta-item">
                    <strong>Credits:</strong> {{ $course->credits }}
                </div>
                @endif
                @if($course->campus)
                <div class="meta-item">
                    <strong>Campus:</strong> {{ $course->campus }}
                </div>
                @endif
                <div class="meta-item">
                    <strong>Term:</strong> {{ $course->term }}
                </div>
                <div class="meta-item">
                    <strong>Total Sections:</strong> {{ $course->sections->count() }}
                </div>
            </div>

            @if($course->prerequisites)
            <div class="meta-row">
                <div class="meta-item" style="flex-direction: column; align-items: start;">
                    <strong>Prerequisites:</strong>
                    <div style="margin-top: 5px; color: #666;">{{ $course->prerequisites }}</div>
                </div>
            </div>
            @endif
        </div>

        <div class="sections-container">
            @if($lectures->count() > 0)
            <h2>Lecture Sections ({{ $lectures->count() }})</h2>
            <table class="sections-table">
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
                        <td>
                            <span class="type-badge">{{ $section->type }}</span>
                            {{ $section->section_number }}
                        </td>
                        <td>{{ $section->meeting_time_display ?? 'TBA' }}</td>
                        <td>
                            <span class="status-badge status-{{ $section->status == 'A' ? 'available' : 'full' }}">
                                {{ $section->status == 'A' ? 'Available' : 'Full' }}
                            </span>
                        </td>
                        <td>
                            @if($section->max_enrollment > 0)
                                @php
                                    $percentage = ($section->seats_available / $section->max_enrollment) * 100;
                                    $class = $percentage > 30 ? 'seats-good' : ($percentage > 10 ? 'seats-low' : 'seats-none');
                                @endphp
                                <span class="{{ $class }}">
                                    {{ $section->seats_available }}/{{ $section->max_enrollment }}
                                </span>
                            @else
                                <span style="color: #999;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($section->linked_crns)
                                <span class="linked-sections">{{ $section->linked_crns }}</span>
                            @else
                                <span style="color: #999;">None</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            @if($labs->count() > 0)
            <h2>Lab Sections ({{ $labs->count() }})</h2>
            <table class="sections-table">
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
                        <td>
                            <span class="type-badge" style="background: #17a2b8;">{{ $section->type }}</span>
                            {{ $section->section_number }}
                        </td>
                        <td>{{ $section->meeting_time_display ?? 'TBA' }}</td>
                        <td>
                            <span class="status-badge status-{{ $section->status == 'A' ? 'available' : 'full' }}">
                                {{ $section->status == 'A' ? 'Available' : 'Full' }}
                            </span>
                        </td>
                        <td>
                            @if($section->max_enrollment > 0)
                                @php
                                    $percentage = ($section->seats_available / $section->max_enrollment) * 100;
                                    $class = $percentage > 30 ? 'seats-good' : ($percentage > 10 ? 'seats-low' : 'seats-none');
                                @endphp
                                <span class="{{ $class }}">
                                    {{ $section->seats_available }}/{{ $section->max_enrollment }}
                                </span>
                            @else
                                <span style="color: #999;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($section->linked_crns)
                                <span class="linked-sections">{{ $section->linked_crns }}</span>
                            @else
                                <span style="color: #999;">None</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            @if($discussions->count() > 0)
            <h2>Discussion Sections ({{ $discussions->count() }})</h2>
            <table class="sections-table">
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
                        <td>
                            <span class="type-badge" style="background: #6f42c1;">{{ $section->type }}</span>
                            {{ $section->section_number }}
                        </td>
                        <td>{{ $section->meeting_time_display ?? 'TBA' }}</td>
                        <td>
                            <span class="status-badge status-{{ $section->status == 'A' ? 'available' : 'full' }}">
                                {{ $section->status == 'A' ? 'Available' : 'Full' }}
                            </span>
                        </td>
                        <td>
                            @if($section->max_enrollment > 0)
                                @php
                                    $percentage = ($section->seats_available / $section->max_enrollment) * 100;
                                    $class = $percentage > 30 ? 'seats-good' : ($percentage > 10 ? 'seats-low' : 'seats-none');
                                @endphp
                                <span class="{{ $class }}">
                                    {{ $section->seats_available }}/{{ $section->max_enrollment }}
                                </span>
                            @else
                                <span style="color: #999;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($section->linked_crns)
                                <span class="linked-sections">{{ $section->linked_crns }}</span>
                            @else
                                <span style="color: #999;">None</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            @if($seminars->count() > 0)
            <h2>Seminar Sections ({{ $seminars->count() }})</h2>
            <table class="sections-table">
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
                        <td>
                            <span class="type-badge" style="background: #28a745;">{{ $section->type }}</span>
                            {{ $section->section_number }}
                        </td>
                        <td>{{ $section->meeting_time_display ?? 'TBA' }}</td>
                        <td>
                            <span class="status-badge status-{{ $section->status == 'A' ? 'available' : 'full' }}">
                                {{ $section->status == 'A' ? 'Available' : 'Full' }}
                            </span>
                        </td>
                        <td>
                            @if($section->max_enrollment > 0)
                                @php
                                    $percentage = ($section->seats_available / $section->max_enrollment) * 100;
                                    $class = $percentage > 30 ? 'seats-good' : ($percentage > 10 ? 'seats-low' : 'seats-none');
                                @endphp
                                <span class="{{ $class }}">
                                    {{ $section->seats_available }}/{{ $section->max_enrollment }}
                                </span>
                            @else
                                <span style="color: #999;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($section->linked_crns)
                                <span class="linked-sections">{{ $section->linked_crns }}</span>
                            @else
                                <span style="color: #999;">None</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            @if($lectures->count() == 0 && $labs->count() == 0 && $discussions->count() == 0 && $seminars->count() == 0)
            <div class="no-data">
                No sections available for this course.
            </div>
            @endif
        </div>
    </div>
</body>
</html>
