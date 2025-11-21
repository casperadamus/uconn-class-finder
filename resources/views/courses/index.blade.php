<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UConn Courses</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { 
            color: #000e2f;
            margin-bottom: 10px;
            font-size: 2.5rem;
        }
        .stats {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .stat-card {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #000e2f;
        }
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        .course-card {
            background: white;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .course-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        .course-title {
            flex: 1;
        }
        .course-code {
            font-size: 1.1rem;
            font-weight: bold;
            color: #000e2f;
        }
        .course-name {
            color: #666;
            margin-top: 5px;
        }
        .course-meta {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
            color: #666;
        }
        .sections-summary {
            display: flex;
            gap: 20px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .section-group {
            flex: 1;
        }
        .section-group h4 {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-item {
            display: flex;
            justify-content: space-between;
            padding: 8px;
            margin: 4px 0;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .section-item.available {
            background: #d4edda;
            color: #155724;
        }
        .section-item.full {
            background: #f8d7da;
            color: #721c24;
        }
        .seats {
            font-weight: bold;
        }
        .seats.seats-none {
            color: #dc3545;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-danger {
            background: #dc3545;
            color: white;
        }
        .pagination {
            display: flex;
            gap: 5px;
            justify-content: center;
            margin-top: 30px;
        }
        .pagination a, .pagination span {
            padding: 8px 12px;
            background: white;
            border-radius: 4px;
            text-decoration: none;
            color: #000e2f;
        }
        .pagination .active {
            background: #000e2f;
            color: white;
        }
        .view-details {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 16px;
            background: #000e2f;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .view-details:hover {
            background: #001f5c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>UConn Course Catalog</h1>
        
        <div class="stats">
            <h2>Database Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">{{ \App\Models\Course::count() }}</div>
                    <div class="stat-label">Total Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ \App\Models\Section::count() }}</div>
                    <div class="stat-label">Total Sections</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ \App\Models\Section::where('status', 'A')->count() }}</div>
                    <div class="stat-label">Available Sections</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ \App\Models\Section::where('seats_available', '>', 0)->count() }}</div>
                    <div class="stat-label">Sections w/ Seats</div>
                </div>
            </div>
        </div>

        @foreach($courses as $course)
        <div class="course-card">
            <div class="course-header">
                <div class="course-title">
                    <div class="course-code">{{ $course->code }}</div>
                    <div class="course-name">{{ $course->title }}</div>
                </div>
                <div class="course-meta">
                    @if($course->credits)
                        <div class="meta-item">💳 {{ $course->credits }} credits</div>
                    @endif
                    @if($course->campus)
                        <div class="meta-item">📍 {{ $course->campus }}</div>
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
