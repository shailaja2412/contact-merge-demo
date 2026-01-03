<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('User Dashboard') }}
        </h2>
    </x-slot>

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
</x-admin-layout>
