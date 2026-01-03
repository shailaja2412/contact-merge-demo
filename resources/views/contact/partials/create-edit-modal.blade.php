<!-- CREATE/EDIT MODAL -->
<div x-show="open" x-transition.opacity x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">

    <div @click.outside="open = false" x-transition.scale
        class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-2xl h-auto max-h-[90vh] flex flex-col">

        <!-- Modal Header (Fixed) -->
        <div class="flex justify-between items-center border-b dark:border-gray-700 p-6 flex-shrink-0">
            <h3 id="modal-title" class="text-lg font-semibold text-gray-900 dark:text-white">
                <span x-text="isEdit ? 'Edit Contact' : 'Create Contact'"></span>
            </h3>
            <button @click="open = false"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-white modal-close">
                ✕
            </button>
        </div>

        <!-- Form Body (Scrollable) -->
        <form id="contact-form" class="flex-1 overflow-y-auto p-6" method="POST" enctype="multipart/form-data"
            @submit.prevent="submitContactForm">
            @csrf
            <template x-if="isEdit">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="flex flex-col gap-5">
                <!-- Profile Picture (Center, Top) -->
                <div class="flex flex-col items-center">
                    <label class="block text-sm mb-3 text-gray-700 dark:text-gray-300">Profile Picture</label>
                    <div class="relative w-32 h-32 rounded-full overflow-hidden border-4 border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 flex items-center justify-center cursor-pointer hover:border-blue-500 transition"
                        @click="$el.parentElement.querySelector('input[name=profile_picture]').click()">
                        <input type="file" name="profile_picture" accept="image/*" class="hidden"
                            @change="if($event.target.files[0]) { const reader = new FileReader(); reader.onload = (e) => { profilePictureUrl = e.target.result; }; reader.readAsDataURL($event.target.files[0]); }">
                        <img class="w-full h-full object-cover" x-show="profilePictureUrl"
                            x-bind:src="profilePictureUrl" alt="Profile">
                        <svg x-show="!profilePictureUrl" xmlns="http://www.w3.org/2000/svg"
                            class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Click to upload</p>
                </div>

                <!-- All Other Fields Below in Sequence -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- First Name -->
                    <div>
                        <label class="block text-sm mb-1 text-gray-700 dark:text-gray-300">First Name</label>
                        <input type="text" name="first_name" x-model="first_name" required
                            class="w-full px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white">
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label class="block text-sm mb-1 text-gray-700 dark:text-gray-300">Last Name</label>
                        <input type="text" name="last_name" x-model="last_name" required
                            class="w-full px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white">
                    </div>

                    <!-- Email (Multiple) -->
                    <div>
                        <label class="block text-sm mb-1 text-gray-700 dark:text-gray-300">Emails</label>
                        <template x-for="(email, idx) in emails" :key="idx">
                            <div class="flex items-center gap-2 mb-2">
                                <input type="email" :name="'emails[]'" x-model="emails[idx]" required
                                    class="w-full px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white">
                                <button type="button" @click="emails.splice(idx, 1)"
                                    class="px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition"
                                    x-show="emails.length > 1">
                                    −
                                </button>
                            </div>
                        </template>
                        <button type="button" @click="emails.push('')"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium mt-1">+ Add
                            Email</button>
                    </div>

                    <!-- Phone (Multiple) -->
                    <div>
                        <label class="block text-sm mb-1 text-gray-700 dark:text-gray-300">Phone
                            Numbers</label>
                        <template x-for="(phone, idx) in phone_numbers" :key="idx">
                            <div class="flex items-center gap-2 mb-2">
                                <input type="text" :name="'phone_numbers[]'" x-model="phone_numbers[idx]"
                                    class="w-full px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white">
                                <button type="button" @click="phone_numbers.splice(idx, 1)"
                                    class="px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition"
                                    x-show="phone_numbers.length > 1">
                                    −
                                </button>
                            </div>
                        </template>
                        <button type="button" @click="phone_numbers.push('')"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium mt-1">+ Add
                            Phone</button>
                    </div>

                    <!-- Gender -->
                    <div>
                        <label class="block text-sm mb-1 text-gray-700 dark:text-gray-300">Gender</label>
                        <select name="gender" x-model="gender"
                            class="w-full px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white">
                            <option value="">Select</option>
                            <option value="1">Male</option>
                            <option value="2">Female</option>
                            <option value="3">Other</option>
                        </select>
                    </div>

                    <!-- Additional Documents (Multiple) -->
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-sm mb-1 text-gray-700 dark:text-gray-300">Additional
                            Documents</label>

                        <!-- Existing Documents (Edit mode) -->
                        <template x-if="isEdit && additionalFiles.length > 0">
                            <div
                                class="mb-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Existing
                                    Documents:</p>
                                <template x-for="(file, idx) in additionalFiles" :key="idx">
                                    <div
                                        class="flex items-center justify-between py-1 text-sm text-gray-600 dark:text-gray-400">
                                        <a :href="`/storage/${file}`" target="_blank"
                                            class="text-blue-600 hover:underline truncate">
                                            <span x-text="file.split('/').pop()"></span>
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- New Documents Upload -->
                        <template x-for="(doc, idx) in documents" :key="idx">
                            <div class="flex items-center gap-2 mb-2">
                                <input type="file" :name="'additional_files[]'"
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                                    class="w-full px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white">
                                <button type="button" @click="documents.splice(idx, 1)"
                                    class="px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition"
                                    x-show="documents.length > 1">
                                    −
                                </button>
                            </div>
                        </template>
                        <button type="button" @click="documents.push('')"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium mt-1">+ Add
                            Document</button>
                    </div>

                    <!-- Custom Fields (Full Width) -->
                    <div class="addition-custom-fields col-span-1 sm:col-span-2">
                        @foreach ($customFields as $field)
                            <div class="mt-4">
                                <label
                                    class="block text-sm mb-1 text-gray-700 dark:text-gray-300">{{ $field->field_name }}</label>
                                @if($field->field_type === 'date')
                                    <!-- <input type="text"
                                        name="custom_fields[{{ $field->id }}]"
                                        x-bind:value="customFieldValues[{{ $field->id }}] ? formatDateForInput(customFieldValues[{{ $field->id }}]) : ''"
                                        placeholder="dd/mm/yyyy"
                                        autocomplete="off"
                                        inputmode="text"
                                        class="w-full px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white date-input"
                                        data-field-id="{{ $field->id }}"> -->




<div class="relative max-w-sm">
  <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
    <svg class="w-4 h-4 text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 10h16m-8-3V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Zm3-7h.01v.01H8V13Zm4 0h.01v.01H12V13Zm4 0h.01v.01H16V13Zm-8 4h.01v.01H8V17Zm4 0h.01v.01H12V17Zm4 0h.01v.01H16V17Z"/></svg>
  </div>
  <input datepicker id="default-datepicker" type="text" 
  name="custom_fields[{{ $field->id }}]"
                                        x-bind:value="customFieldValues[{{ $field->id }}] ? formatDateForInput(customFieldValues[{{ $field->id }}]) : ''"
                                        placeholder="dd/mm/yyyy"
                                        data-field-id="{{ $field->id }}"
  class="w-full px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white date-input" placeholder="Select date">
</div>

                                @else
                                    <input type="{{ $field->field_type }}"
                                        name="custom_fields[{{ $field->id }}]"
                                        x-bind:value="customFieldValues[{{ $field->id }}] || ''"
                                        class="w-full px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

        </form>

        <!-- Modal Footer (Fixed) -->
        <div
            class="border-t dark:border-gray-700 p-6 flex justify-end gap-3 flex-shrink-0 bg-white dark:bg-gray-900">
            <button type="button" @click="open = false"
                class="px-6 py-2.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                Cancel
            </button>

            <button type="submit" form="contact-form"
                :disabled="isSubmitting"
                class="px-6 py-2.5 rounded-full text-white
                       bg-gradient-to-r from-blue-500 to-indigo-600
                       hover:from-blue-600 hover:to-indigo-700
                       shadow-md hover:shadow-lg
                       focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800
                       transition-all
                       disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-text="isSubmitting ? 'Saving...' : (isEdit ? 'Update' : 'Save')"></span>
            </button>
        </div>

    </div>
</div>

