<!-- CREATE/EDIT MODAL -->
<div x-show="open" x-transition.opacity x-cloak style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    @click.self="if (!$event.target.closest('.flatpickr-calendar') && !$event.target.closest('.flatpickr-monthDropdown-months') && !$event.target.closest('.flatpickr-monthDropdown-month')) { open = false; }">

    <div x-transition.scale
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
                            class="w-full px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white"
                            :class="fieldErrors['first_name'] ? 'border-red-500 dark:border-red-500' : ''">
                        <template x-if="fieldErrors['first_name']">
                            <p class="text-sm text-red-600 dark:text-red-400 mt-1" x-text="fieldErrors['first_name']"></p>
                        </template>
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label class="block text-sm mb-1 text-gray-700 dark:text-gray-300">Last Name</label>
                        <input type="text" name="last_name" x-model="last_name" required
                            class="w-full px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white"
                            :class="fieldErrors['last_name'] ? 'border-red-500 dark:border-red-500' : ''">
                        <template x-if="fieldErrors['last_name']">
                            <p class="text-sm text-red-600 dark:text-red-400 mt-1" x-text="fieldErrors['last_name']"></p>
                        </template>
                    </div>

                    <!-- Email (Multiple) -->
                    <div>
                        <label class="block text-sm mb-1 text-gray-700 dark:text-gray-300">Emails</label>
                        <template x-for="(email, idx) in emails" :key="idx">
                            <div class="mb-2">
                                <div class="flex items-center gap-2">
                                    <input type="email" :name="'emails[]'" x-model="emails[idx]" required
                                        class="w-full px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white"
                                        :class="fieldErrors['emails.' + idx] ? 'border-red-500 dark:border-red-500' : ''">
                                    <button type="button" @click="emails.splice(idx, 1)"
                                        class="px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition"
                                        x-show="emails.length > 1">
                                        −
                                    </button>
                                </div>
                                <template x-if="fieldErrors['emails.' + idx]">
                                    <p class="text-sm text-red-600 dark:text-red-400 mt-1" x-text="fieldErrors['emails.' + idx]"></p>
                                </template>
                            </div>
                        </template>
                        <template x-if="fieldErrors['emails']">
                            <p class="text-sm text-red-600 dark:text-red-400 mt-1" x-text="fieldErrors['emails']"></p>
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
                            <div class="mb-2">
                                <div class="flex items-center gap-2">
                                    <input type="text" 
                                        :name="'phone_numbers[]'" 
                                        x-model="phone_numbers[idx]"
                                        @input="phone_numbers[idx] = phone_numbers[idx].replace(/\D/g, '').slice(0, 10)"
                                        maxlength="10"
                                        pattern="[0-9]{10}"
                                        placeholder="10 digits only"
                                        class="w-full px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white"
                                        :class="fieldErrors['phone_numbers.' + idx] ? 'border-red-500 dark:border-red-500' : ''">
                                    <button type="button" @click="phone_numbers.splice(idx, 1)"
                                        class="px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition"
                                        x-show="phone_numbers.length > 1">
                                        −
                                    </button>
                                </div>
                                <template x-if="fieldErrors['phone_numbers.' + idx]">
                                    <p class="text-sm text-red-600 dark:text-red-400 mt-1" x-text="fieldErrors['phone_numbers.' + idx]"></p>
                                </template>
                            </div>
                        </template>
                        <template x-if="fieldErrors['phone_numbers']">
                            <p class="text-sm text-red-600 dark:text-red-400 mt-1" x-text="fieldErrors['phone_numbers']"></p>
                        </template>
                        <button type="button" @click="phone_numbers.push('')"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium mt-1">+ Add
                            Phone</button>
                    </div>

                    <!-- Gender -->
                    <div>
                        <label class="block text-sm mb-1 text-gray-700 dark:text-gray-300">Gender</label>
                        <select name="gender" x-model="gender"
                            class="w-full px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white"
                            :class="fieldErrors['gender'] ? 'border-red-500 dark:border-red-500' : ''">
                            <option value="">Select</option>
                            <option value="1">Male</option>
                            <option value="2">Female</option>
                            <option value="3">Other</option>
                        </select>
                        <template x-if="fieldErrors['gender']">
                            <p class="text-sm text-red-600 dark:text-red-400 mt-1" x-text="fieldErrors['gender']"></p>
                        </template>
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
                                    <input type="text"
                                        name="custom_fields[{{ $field->id }}]"
                                        x-model="customFieldValues[{{ $field->id }}]"
                                        x-init="
                                            $nextTick(() => {
                                                if (typeof flatpickr !== 'undefined') {
                                                    const fp = flatpickr($el, {
                                                        dateFormat: 'd/m/Y',
                                                        allowInput: true,
                                                        clickOpens: true,
                                                        appendTo: document.body,
                                                        static: false,
                                                        locale: {
                                                            firstDayOfWeek: 1
                                                        },
                                                        onChange: function(selectedDates, dateStr, instance) {
                                                            customFieldValues[{{ $field->id }}] = dateStr;
                                                        },
                                                        onOpen: function(selectedDates, dateStr, instance) {
                                                            // Prevent modal from closing when calendar opens
                                                            const calendar = instance.calendarContainer;
                                                            if (calendar) {
                                                                calendar.style.zIndex = '9999';
                                                                // Prevent clicks on calendar from closing modal
                                                                calendar.addEventListener('click', function(e) {
                                                                    e.stopPropagation();
                                                                });
                                                            }
                                                        }
                                                    });
                                                    // Set initial value if exists
                                                    if (customFieldValues[{{ $field->id }}]) {
                                                        const dateValue = customFieldValues[{{ $field->id }}];
                                                        if (dateValue.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                                                            // Already in dd/mm/yyyy format
                                                            fp.setDate(dateValue, false, 'd/m/Y');
                                                        } else if (dateValue.match(/^\d{4}-\d{2}-\d{2}$/)) {
                                                            // Convert from Y-m-d to dd/mm/yyyy
                                                            const formatted = formatDateForInput(dateValue);
                                                            fp.setDate(formatted, false, 'd/m/Y');
                                                        }
                                                    }
                                                }
                                            });
                                        "
                                        placeholder="dd/mm/yyyy"
                                        autocomplete="off"
                                        class="w-full px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white date-picker-input"
                                        data-field-id="{{ $field->id }}"
                                        :class="fieldErrors['custom_fields.{{ $field->id }}'] ? 'border-red-500 dark:border-red-500' : ''">
                                        
                                @else
                                    <input type="{{ $field->field_type }}"
                                        name="custom_fields[{{ $field->id }}]"
                                        x-bind:value="customFieldValues[{{ $field->id }}] || ''"
                                        class="w-full px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white"
                                        :class="fieldErrors['custom_fields.{{ $field->id }}'] ? 'border-red-500 dark:border-red-500' : ''">
                                @endif
                                <template x-if="fieldErrors['custom_fields.{{ $field->id }}']">
                                    <p class="text-sm text-red-600 dark:text-red-400 mt-1" x-text="fieldErrors['custom_fields.{{ $field->id }}']"></p>
                                </template>
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

