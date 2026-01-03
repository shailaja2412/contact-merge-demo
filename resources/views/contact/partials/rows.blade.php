@if (isset($contacts) && $contacts->count())
    @foreach ($contacts as $contact)
        <tr data-id="{{ $contact->id }}"
            class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
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
                    class="text-blue-600 hover:opacity-80 me-3" title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 24 24"
                        fill="currentColor">
                        <path d="M12 5C6.5 5 2 8.8 1 12c1 3.2 5.5 7 11 7s10-3.8 11-7c-1-3.2-5.5-7-11-7z" />
                        <circle cx="12" cy="12" r="3" />
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
                    class="text-blue-600 hover:opacity-80 me-3" title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path d="M17.414 2.586a2 2 0 010 2.828L8.828 13H6v-2.828l8.586-8.586a2 2 0 012.828 0z" />
                        <path fill-rule="evenodd" d="M2 15.25V18h2.75l8.207-8.207-2.75-2.75L2 15.25z"
                            clip-rule="evenodd" />
                    </svg>
                </button>


                <button
                    @click.prevent="openMerge({{ $contact->id }})"
                    class="text-purple-600 hover:opacity-80 me-3" title="Merge">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 18h8M8 6h8M13 12h5M11 12H6" />
                        <circle cx="18" cy="12" r="2" />
                        <circle cx="6" cy="12" r="2" />
                    </svg>
                </button>

                <button @click.prevent="deleteOpen = true; deleteId = {{ $contact->id }}"
                    class="text-red-600 hover:opacity-80" title="Delete">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M6 2a1 1 0 00-1 1v1H3a1 1 0 100 2h14a1 1 0 100-2h-2V3a1 1 0 00-1-1H6zm3 6a1 1 0 10-2 0v6a1 1 0 102 0V8zm4 0a1 1 0 10-2 0v6a1 1 0 102 0V8z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </td>
        </tr>
    @endforeach
@else
    <tr id="no-contacts" class="border-t dark:border-gray-700">
        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No contacts yet.</td>
    </tr>
@endif
