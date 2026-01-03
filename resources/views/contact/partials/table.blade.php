<!-- Table -->
<table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
    <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
        <tr>
            <th class="px-6 py-3">Name</th>
            <th class="px-6 py-3">Mobile</th>
            <th class="px-6 py-3 text-center">Actions</th>
        </tr>
    </thead>

    <tbody id="custom-fields-list">
        @include('contact.partials.rows', ['contacts' => $contacts])
    </tbody>
</table>

