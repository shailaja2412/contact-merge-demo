<!-- Header -->
<div class="p-4">
    <!-- Action Bar -->
    <div class="flex flex-wrap gap-4 justify-between items-center mb-4">
        <!-- Search (Legacy - kept for backward compatibility) -->
        <div class="relative w-64">
            <label for="search" class="sr-only">Search contacts</label>
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                🔍
            </span>
            <input type="text" id="search" name="search" placeholder="Quick search..."
                class="w-full pl-10 pr-10 py-2.5
                       bg-gray-100 dark:bg-gray-800
                       border border-gray-300 dark:border-gray-600
                       text-gray-900 dark:text-gray-100
                       rounded-full text-sm
                       focus:ring-2 focus:ring-blue-500
                       focus:border-blue-500
                       transition" />
            <button type="button" id="clear-search"
                class="absolute inset-y-0 right-9 items-center pr-3 text-gray-500 hidden"
                aria-label="Clear search">✕</button>
            <span id="search-spinner" class="absolute inset-y-0 right-4 items-center text-gray-400 hidden"
                aria-hidden="true">
                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
            </span>
        </div>

        <!-- Filter Icon and Add Contact Button -->
        <div class="flex items-center gap-3">
            <button @click="filterOpen = !filterOpen"
                class="px-4 py-2.5 text-sm font-medium
                       rounded-full
                       bg-gray-200 dark:bg-gray-700
                       text-gray-700 dark:text-gray-300
                       hover:bg-gray-300 dark:hover:bg-gray-600
                       shadow-md hover:shadow-lg
                       focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-800
                       transition-all duration-200
                       flex items-center gap-2"
                :class="filterOpen ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : ''"
                title="Toggle Filters">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
            </button>

            <button @click="openCreate()"
                class="px-6 py-2.5 text-sm font-medium text-white
                       rounded-full
                       bg-gradient-to-r from-blue-500 to-indigo-600
                       hover:from-blue-600 hover:to-indigo-700
                       shadow-md hover:shadow-lg
                       focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800
                       transition-all duration-200">
                + Add Contact
            </button>
        </div>
    </div>

    <!-- Filters Section (Toggleable) -->
    <div x-show="filterOpen" style="display: none;" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="mb-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="flex flex-wrap gap-4 items-end">
            <!-- Name Filter -->
            <div class="flex-1 min-w-[200px]">
                <label for="filter-name" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                <input type="text" id="filter-name" name="name" placeholder="Search by name..."
                    class="w-full px-4 py-2 rounded-lg
                           bg-white dark:bg-gray-700
                           border border-gray-300 dark:border-gray-600
                           text-gray-900 dark:text-gray-100
                           text-sm
                           focus:ring-2 focus:ring-blue-500
                           focus:border-blue-500
                           transition" />
            </div>

            <!-- Email Filter -->
            <div class="flex-1 min-w-[200px]">
                <label for="filter-email" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input type="text" id="filter-email" name="email" placeholder="Search by email..."
                    class="w-full px-4 py-2 rounded-lg
                           bg-white dark:bg-gray-700
                           border border-gray-300 dark:border-gray-600
                           text-gray-900 dark:text-gray-100
                           text-sm
                           focus:ring-2 focus:ring-blue-500
                           focus:border-blue-500
                           transition" />
            </div>

            <!-- Gender Filter -->
            <div class="flex-1 min-w-[150px]">
                <label for="filter-gender" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Gender</label>
                <select id="filter-gender" name="gender"
                    class="w-full px-4 py-2 rounded-lg
                           bg-white dark:bg-gray-700
                           border border-gray-300 dark:border-gray-600
                           text-gray-900 dark:text-gray-100
                           text-sm
                           focus:ring-2 focus:ring-blue-500
                           focus:border-blue-500
                           transition">
                    <option value="">All Genders</option>
                    <option value="1">Male</option>
                    <option value="2">Female</option>
                    <option value="3">Other</option>
                </select>
            </div>

            <!-- Custom Fields Filters -->
            @if(isset($customFields) && $customFields->count() > 0)
                @foreach($customFields as $field)
                    <div class="flex-1 min-w-[150px]">
                        <label for="filter-custom-{{ $field->id }}" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $field->field_name }}</label>
                        @if($field->field_type === 'date')
                            <input type="text" 
                                   id="filter-custom-{{ $field->id }}" 
                                   name="custom_fields[{{ $field->id }}]" 
                                   placeholder="dd/mm/yyyy"
                                   pattern="\d{2}/\d{2}/\d{4}"
                                   data-custom-field-id="{{ $field->id }}"
                                   data-field-type="date"
                                   class="w-full px-4 py-2 rounded-lg
                                          bg-white dark:bg-gray-700
                                          border border-gray-300 dark:border-gray-600
                                          text-gray-900 dark:text-gray-100
                                          text-sm
                                          focus:ring-2 focus:ring-blue-500
                                          focus:border-blue-500
                                          transition date-input" />
                        @else
                            <input type="{{ $field->field_type }}" 
                                   id="filter-custom-{{ $field->id }}" 
                                   name="custom_fields[{{ $field->id }}]" 
                                   placeholder="Filter by {{ $field->field_name }}..."
                                   data-custom-field-id="{{ $field->id }}"
                                   class="w-full px-4 py-2 rounded-lg
                                          bg-white dark:bg-gray-700
                                          border border-gray-300 dark:border-gray-600
                                          text-gray-900 dark:text-gray-100
                                          text-sm
                                          focus:ring-2 focus:ring-blue-500
                                          focus:border-blue-500
                                          transition" />
                        @endif
                    </div>
                @endforeach
            @endif

            <!-- Clear Filters Button -->
            <div>
                <button type="button" id="clear-filters"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300
                           bg-white dark:bg-gray-700
                           border border-gray-300 dark:border-gray-600
                           rounded-lg
                           hover:bg-gray-50 dark:hover:bg-gray-600
                           transition">
                    Clear All
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Message Display Area -->
<div id="message-container" class="p-4 hidden">
    <div id="message-content" class="text-sm"></div>
</div>

@if (session('success'))
    <div class="p-4">
        <div class="text-sm text-green-600 dark:text-green-400">{{ session('success') }}</div>
    </div>
@endif

