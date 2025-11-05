<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">


        <!-- Search Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" action="{{ route('classes.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Campus -->
                <div>
                    <label for="campus" class="block text-sm font-medium text-gray-700 mb-2">Campus:</label>
                    <select name="campus" id="campus" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        @foreach($campuses as $code => $name)
                            <option value="{{ $code }}" {{ $campus == $code ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Subject -->
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject:</label>
                    <select name="subject" id="subject" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        @foreach($subjects as $code => $name)
                            <option value="{{ $code }}" {{ ($subject ?? '') == $code ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Course Number -->
                <div>
                    <label for="catalog_nbr" class="block text-sm font-medium text-gray-700 mb-2">Course Number:</label>
                    <input type="text" name="catalog_nbr" id="catalog_nbr" value="{{ $catalogNumber ?? '' }}"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g. 1000">
                </div>

                <!-- Search Button -->
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Search Classes
                    </button>
                </div>
            </form>

            <!-- Keyword Search -->
            <div class="mt-4">
                <label for="keyword" class="block text-sm font-medium text-gray-700 mb-2">Keyword Search:</label>
                <input type="text" name="keyword" id="keyword" value="{{ $keyword ?? '' }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Search by course title">
            </div>
        </div>

        <!-- Results -->
        @if(isset($classes['error']))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <p class="font-semibold">Error:</p>
                <p>{{ $classes['error'] }}</p>
            </div>
        @elseif(is_array($classes) && count($classes) > 0)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">
                    Found {{ count($classes) }} classes 
                    @if($subject) in {{ $subjects[$subject] ?? $subject }} @endif
                    at {{ $campuses[$campus] ?? $campus }}
                </h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CRN</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="classes-table">
                            @foreach($classes as $index => $class)
                            <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}" 
                                data-course-key="{{ $class['key'] }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $class['code'] ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $class['title'] ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $class['crn'] ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $class['no'] ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $class['meets'] ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if(($class['stat'] ?? '') == 'A')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Open
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Full
                                        </span>
                                    @endif
                                </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">
                                            {{ $class['seats_available'] ?? '-' }} / {{ $class['seats_total'] ?? '-' }}
                                    </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif(request()->any(['campus', 'subject', 'catalog_nbr', 'keyword']))
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <p class="text-gray-600">No classes found with the current search criteria.</p>
                <p class="text-sm text-gray-500 mt-2">Try selecting a specific subject or course number.</p>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <p class="text-sm text-gray-500 mt-2">Currently searching: Spring 2026</p>
            </div>
        @endif
    </div>
</body>
</html>
