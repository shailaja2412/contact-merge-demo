@if (isset($customFields) && $customFields->count())
    @foreach ($customFields as $field)
        <tr data-id="{{ $field->id }}"
            class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
            <td class="px-6 py-4 text-lg text-gray-900 dark:text-white">{{ $field->id }}</td>
            <td class="px-6 py-4 text-lg text-gray-900 dark:text-white">{{ $field->field_name }}</td>
            <td class="px-6 py-4 text-lg">{{ $field->field_type }}</td>
            <td class="px-6 py-4 text-center text-lg">
                <button @click.prevent='openEdit(@json($field))'
                    class="text-green-600 hover:opacity-80 me-3" title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>

                <button @click.prevent="deleteOpen = true; deleteId = {{ $field->id }}"
                    class="text-red-600 hover:opacity-80" title="Delete">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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
