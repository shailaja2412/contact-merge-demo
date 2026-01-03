<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('User Dashboard') }}
        </h2>
    </x-slot>

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* Prevent Alpine.js elements from flashing before initialization */
        [x-cloak] {
            display: none !important;
        }
        
        .flatpickr-calendar {
            background: white;
            z-index: 9999 !important;
        }
        .dark .flatpickr-calendar {
            background: #1f2937;
            color: #f3f4f6;
        }
        .flatpickr-day.selected {
            background: #3b82f6;
            border-color: #3b82f6;
        }
        .date-picker-input {
            width: 100%;
        }
    </style>

    <div x-data="contactManager()">
        @include('contact.partials.header')
        @include('contact.partials.table')
        
        @include('contact.partials.create-edit-modal')
        @include('contact.partials.view-modal')
        @include('contact.partials.delete-modal')
        @include('contact.partials.merge-modal')
    </div>

    @include('contact.partials.filter-script')
    @include('contact.partials.alpine-component')
    <x-toast-container />
    
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</x-admin-layout>
