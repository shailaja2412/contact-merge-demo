@if(isset($customFields) && $customFields->count())
@foreach($customFields as $field)
<tr data-id="{{ $field->id }}" class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $field->id }}</td>
    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $field->field_name }}</td>
    <td class="px-6 py-4">{{ $field->field_type }}</td>
    <td class="px-6 py-4 text-center">
        <button
            @click.prevent='openEdit(@json($field))'
            class="text-blue-600 hover:opacity-80 me-3"
            title="Edit">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                <path d="M17.414 2.586a2 2 0 010 2.828L8.828 13H6v-2.828l8.586-8.586a2 2 0 012.828 0z" />
                <path fill-rule="evenodd" d="M2 15.25V18h2.75l8.207-8.207-2.75-2.75L2 15.25z" clip-rule="evenodd" />
            </svg>
        </button>


        <button @click.prevent="deleteOpen = true; deleteId = {{ $field->id }}" class="text-red-600 hover:opacity-80" title="Delete">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H3a1 1 0 100 2h14a1 1 0 100-2h-2V3a1 1 0 00-1-1H6zm3 6a1 1 0 10-2 0v6a1 1 0 102 0V8zm4 0a1 1 0 10-2 0v6a1 1 0 102 0V8z" clip-rule="evenodd" />
            </svg>
        </button>
    </td>
</tr>
@endforeach
@else
<tr id="no-fields" class="border-t dark:border-gray-700">
    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No custom fields yet.</td>
</tr>
@endif