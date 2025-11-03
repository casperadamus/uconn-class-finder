<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UConn Class Finder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .course-details-popup {
            animation: fadeIn 0.2s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Smooth scrolling for the popup */
        .popup-content {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e0 #f7fafc;
        }

        .popup-content::-webkit-scrollbar {
            width: 6px;
        }

        .popup-content::-webkit-scrollbar-track {
            background: #f7fafc;
        }

        .popup-content::-webkit-scrollbar-thumb {
            background-color: #cbd5e0;
            border-radius: 3px;
        }
        
        .clickable-row:hover {
            background-color: #dbeafe !important;
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center mb-2 text-blue-800">UConn Class Finder</h1>
        <p class="text-center text-gray-600 mb-8">Currently searching: Spring 2026</p>

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
                            <tr class="clickable-row {{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}" 
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
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                        Click row
                                    </span>
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
                <p class="text-gray-600">Select a campus and optionally a subject to search for classes.</p>
                <p class="text-sm text-gray-500 mt-2">Currently searching: Spring 2026</p>
            </div>
        @endif
    </div>

    <script>
    // Add click event listeners to all class rows
    document.addEventListener('DOMContentLoaded', function() {
        const classRows = document.querySelectorAll('.clickable-row');
        
        classRows.forEach(row => {
            row.addEventListener('click', function() {
                const courseKey = this.getAttribute('data-course-key');
                if (courseKey) {
                    showCourseDetails(courseKey);
                }
            });
        });
    });

    async function showCourseDetails(courseKey) {
        console.log('Loading details for course:', courseKey);
        
        // Show loading popup
        const loadingPopup = createPopup(`
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-4 text-gray-600">Loading course details...</p>
            </div>
        `);
        
        try {
            const response = await fetch(`/course-details/${courseKey}`);
            const data = await response.json();
            
            // Close loading popup
            loadingPopup.remove();
            
            if (data.details && data.details.error) {
                alert('Error loading course details: ' + data.details.error);
                return;
            }
            
            const details = data.details;
            const seatInfo = data.seat_info;
            
            // Create detailed popup
            createDetailedPopup(details, seatInfo);
            
        } catch (error) {
            console.error('Error:', error);
            loadingPopup.remove();
            alert('Error loading course details. Check console for details.');
        }
    }

    function createPopup(content) {
        const popup = document.createElement('div');
        popup.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        popup.innerHTML = `
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                ${content}
            </div>
        `;
        
        // Close on background click
        popup.addEventListener('click', function(e) {
            if (e.target === popup) {
                popup.remove();
            }
        });
        
        document.body.appendChild(popup);
        return popup;
    }

    function createDetailedPopup(details, seatInfo) {
        // Clean HTML from text content
        function cleanHtml(html) {
            if (!html) return '';
            return html.replace(/<[^>]*>/g, '').trim();
        }

        // Format seat information
        let seatInfoHtml = '';
        if (seatInfo) {
            seatInfoHtml = `
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-900 mb-2">📊 Seat Information</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium">Max Enrollment:</span>
                            <span class="ml-2 text-blue-700">${seatInfo.max_enrollment}</span>
                        </div>
                        <div>
                            <span class="font-medium">Seats Available:</span>
                            <span class="ml-2 ${seatInfo.available > 0 ? 'text-green-600 font-semibold' : 'text-red-600'}">
                                ${seatInfo.available}
                            </span>
                        </div>
                    </div>
                    ${details.status ? `<p class="mt-2 text-sm text-blue-700"><strong>Status:</strong> ${cleanHtml(details.status)}</p>` : ''}
                </div>
            `;
        } else {
            seatInfoHtml = '<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-800">No seat information available</div>';
        }

        // Format description
        let descriptionHtml = '';
        if (details.description) {
            descriptionHtml = `
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">📝 Description</h4>
                    <p class="text-sm text-gray-700">${cleanHtml(details.description)}</p>
                </div>
            `;
        }

        // Format requirements
        let requirementsHtml = '';
        if (details.registration_restrictions) {
            requirementsHtml = `
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">🎓 Requirements</h4>
                    <p class="text-sm text-gray-700">${cleanHtml(details.registration_restrictions)}</p>
                </div>
            `;
        }

        // Format consent
        let consentHtml = '';
        if (details.consent_req_code) {
            consentHtml = `
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">✅ Consent Required</h4>
                    <p class="text-sm text-gray-700">${cleanHtml(details.consent_req_code)}</p>
                </div>
            `;
        }

        // Format campus
        let campusHtml = '';
        if (details.camp_html) {
            campusHtml = `
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">🏫 Campus</h4>
                    <p class="text-sm text-gray-700">${cleanHtml(details.camp_html)}</p>
                </div>
            `;
        }

        // Format schedule type
        let scheduleHtml = '';
        if (details.schd_html) {
            scheduleHtml = `
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">📚 Schedule Type</h4>
                    <p class="text-sm text-gray-700">${cleanHtml(details.schd_html)}</p>
                </div>
            `;
        }

        // Format instruction mode
        let instructionHtml = '';
        if (details.instmode) {
            instructionHtml = `
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">💻 Instruction Mode</h4>
                    <p class="text-sm text-gray-700">${cleanHtml(details.instmode)}</p>
                </div>
            `;
        }

        // Format credits
        let creditsHtml = '';
        if (details.hours_html) {
            creditsHtml = `
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">⏰ Credits</h4>
                    <p class="text-sm text-gray-700">${cleanHtml(details.hours_html)}</p>
                </div>
            `;
        }

        // Format reserved seats
        let reservedSeatsHtml = '';
        if (details.reserved_seats) {
            reservedSeatsHtml = `
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-2">🎯 Reserved Seats</h4>
                    <div class="text-sm text-gray-700">${cleanHtml(details.reserved_seats)}</div>
                </div>
            `;
        }

        // Format attributes
        let attributesHtml = '';
        if (details.section_attributes) {
            attributesHtml = `
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h4 class="font-semibold text-purple-900 mb-2">🏷️ Attributes</h4>
                    <p class="text-sm text-purple-700">${cleanHtml(details.section_attributes)}</p>
                </div>
            `;
        }

        // Format all sections
        let sectionsHtml = '';
        if (details.allInGroup && details.allInGroup.length > 1) {
            const sections = details.allInGroup.map(section => `
                <tr class="border-t">
                    <td class="px-4 py-2">${section.no || 'N/A'}</td>
                    <td class="px-4 py-2">${section.crn || 'N/A'}</td>
                    <td class="px-4 py-2">${section.meets || 'N/A'}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 text-xs rounded-full ${section.stat === 'A' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${section.stat === 'A' ? 'Open' : 'Full'}
                        </span>
                    </td>
                </tr>
            `).join('');
            
            sectionsHtml = `
                <div class="border border-gray-200 rounded-lg">
                    <h4 class="font-semibold text-gray-900 p-4 border-b">📋 All Sections (${details.allInGroup.length})</h4>
                    <div class="max-h-60 overflow-y-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Section</th>
                                    <th class="px-4 py-2 text-left">CRN</th>
                                    <th class="px-4 py-2 text-left">Schedule</th>
                                    <th class="px-4 py-2 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${sections}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        const popupContent = `
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-gray-900">
                            ${details.code || 'N/A'} - ${details.title || 'N/A'}
                        </h3>
                        <button onclick="closePopup()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- Seat Information -->
                        ${seatInfoHtml}
                        
                        <!-- Course Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div class="space-y-4">
                                ${descriptionHtml}
                                ${requirementsHtml}
                                ${consentHtml}
                            </div>
                            
                            <!-- Right Column -->
                            <div class="space-y-4">
                                ${campusHtml}
                                ${scheduleHtml}
                                ${instructionHtml}
                                ${creditsHtml}
                            </div>
                        </div>
                        
                        <!-- Additional Sections -->
                        ${reservedSeatsHtml}
                        ${attributesHtml}
                        ${sectionsHtml}
                    </div>
                    
                    <div class="sticky bottom-0 bg-gray-50 px-6 py-4 border-t flex justify-end">
                        <button onclick="closePopup()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        const popup = document.createElement('div');
        popup.innerHTML = popupContent;
        popup.id = 'course-details-popup';
        document.body.appendChild(popup);

        // Add event listener to close button
        const closeButton = popup.querySelector('button[onclick="closePopup()"]');
        closeButton.onclick = closePopup;

        // Close when clicking outside
        popup.addEventListener('click', function(e) {
            if (e.target === popup) {
                closePopup();
            }
        });
    }

    function closePopup() {
        const popup = document.getElementById('course-details-popup');
        if (popup) {
            popup.remove();
        }
    }

    // Close popup with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePopup();
        }
    });
    </script>
</body>
</html>