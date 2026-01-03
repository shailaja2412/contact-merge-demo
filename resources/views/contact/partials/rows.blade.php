@if (isset($contacts) && $contacts->count())
    @foreach ($contacts as $contact)
        <tr data-id="{{ $contact->id }}"
            class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
            <td class="px-6 py-4 font-medium= text-gray-900 dark:text-white">
                {{ ucfirst($contact->first_name) . ' ' . ucfirst($contact->last_name) }}</td>
            <td class="px-6 py-4">{{ $contact->phone_number }}</td>
            <td class="px-6 py-4 text-center">

                <button
                    @click.prevent="openView({
                id: {{ $contact->id }},
                first_name: '{{ addslashes($contact->first_name) }}',
                last_name: '{{ addslashes($contact->last_name) }}',
                email: '{{ addslashes($contact->email ?? '') }}',
                phone_number: '{{ addslashes($contact->phone_number ?? '') }}',
                emails: {{ json_encode(is_array($contact->emails) ? $contact->emails : json_decode($contact->emails, true) ?? []) }},
                phone_numbers: {{ json_encode(is_array($contact->phone_numbers) ? $contact->phone_numbers : json_decode($contact->phone_numbers, true) ?? []) }},
                additional_files: {{ json_encode(is_array($contact->additional_files) ? $contact->additional_files : json_decode($contact->additional_files, true) ?? []) }},
                profile_picture: '{{ $contact->profile_picture ?? '' }}',
                gender: '{{ $contact->gender }}'
            })"
                    class="text-blue-600 hover:opacity-80 me-3" title="View">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
                <button
                    @click.prevent="openEdit({
                id: {{ $contact->id }},
                first_name: '{{ addslashes($contact->first_name) }}',
                last_name: '{{ addslashes($contact->last_name) }}',
                email: '{{ addslashes($contact->email ?? '') }}',
                phone_number: '{{ addslashes($contact->phone_number ?? '') }}',
                emails: {{ json_encode(is_array($contact->emails) ? $contact->emails : json_decode($contact->emails, true) ?? []) }},
                phone_numbers: {{ json_encode(is_array($contact->phone_numbers) ? $contact->phone_numbers : json_decode($contact->phone_numbers, true) ?? []) }},
                additional_files: {{ json_encode(is_array($contact->additional_files) ? $contact->additional_files : json_decode($contact->additional_files, true) ?? []) }},
                profile_picture: '{{ $contact->profile_picture ?? '' }}',
                gender: '{{ $contact->gender }}'
            })"
                    class="text-green-600 hover:opacity-80 me-3" title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>

                <button
                    @click.prevent="openMerge({{ $contact->id }})"
                    class="text-purple-600 hover:opacity-80 me-3" title="Merge">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </button>

                <button @click.prevent="deleteOpen = true; deleteId = {{ $contact->id }}"
                    class="text-red-600 hover:opacity-80" title="Delete">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </td>
        </tr>
    @endforeach
@else
    <tr id="no-contacts" class="border-t dark:border-gray-700">
        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No contacts yet.</td>
    </tr>
@endif
