<!-- VIEW CONTACT MODAL -->
<div x-show="viewOpen" x-transition.opacity x-cloak style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">

    <div @click.outside="viewOpen=false" x-transition.scale
        class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-2xl h-auto max-h-[90vh] flex flex-col overflow-hidden">

        <!-- Header (Fixed) -->
        <div class="flex justify-between items-center p-6 border-b dark:border-gray-700 flex-shrink-0 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-800">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Contact Details</h3>
            <button @click="viewOpen=false" 
                class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Body (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <!-- Profile Section -->
            <div class="text-center pb-6 border-b dark:border-gray-700">
                <div class="relative inline-block">
                    <img
                        :src="viewContact.profile_picture ?
                            `/storage/${viewContact.profile_picture}` :
                            'https://ui-avatars.com/api/?name=' + encodeURIComponent(viewContact.first_name + ' ' + viewContact.last_name) + '&size=128&background=3b82f6&color=ffffff'"
                        class="w-32 h-32 mx-auto rounded-full object-cover border-4 border-blue-200 dark:border-blue-800 shadow-lg">
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-4">
                    <span x-text="viewContact.first_name"></span>
                    <span x-text="' ' + viewContact.last_name"></span>
                </h2>
            </div>

            <!-- Contact Information Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Phone Numbers -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <h3 class="font-semibold text-gray-700 dark:text-gray-300">Phone Numbers</h3>
                    </div>
                    <template x-if="!viewContact.phone_numbers || viewContact.phone_numbers.length === 0">
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">No phone numbers</p>
                    </template>
                    <div class="space-y-2">
                        <template x-for="(phone, index) in (viewContact.phone_numbers ?? [])" :key="index">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">•</span>
                                <a :href="'tel:' + phone" class="text-sm text-blue-600 dark:text-blue-400 hover:underline" x-text="phone"></a>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Email Addresses -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <h3 class="font-semibold text-gray-700 dark:text-gray-300">Email Addresses</h3>
                    </div>
                    <template x-if="!viewContact.emails || viewContact.emails.length === 0">
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic">No email addresses</p>
                    </template>
                    <div class="space-y-2">
                        <template x-for="(email, index) in (viewContact.emails ?? [])" :key="index">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">•</span>
                                <a :href="'mailto:' + email" class="text-sm text-blue-600 dark:text-blue-400 hover:underline break-all" x-text="email"></a>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Gender -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <h3 class="font-semibold text-gray-700 dark:text-gray-300">Gender</h3>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300" 
                       x-text="viewContact.gender == 1 ? 'Male' : viewContact.gender == 2 ? 'Female' : 'Other'">
                    </p>
                </div>
            </div>

            <!-- Documents Section -->
            <template x-if="viewContact.additional_files && viewContact.additional_files.length > 0">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        <h3 class="font-semibold text-gray-700 dark:text-gray-300">Documents</h3>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <template x-for="(file, index) in viewContact.additional_files" :key="index">
                            <div class="relative group">
                                <!-- Image Preview -->
                                <template x-if="file && file.match(/\.(jpg|jpeg|png|gif|webp)$/i)">
                                    <div class="relative">
                                        <img :src="`/storage/${file}`"
                                            class="rounded-lg h-24 w-full object-cover cursor-pointer border-2 border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-400 transition-all shadow-md hover:shadow-lg"
                                            @click="window.open(`/storage/${file}`, '_blank')"
                                            :alt="file.split('/').pop()">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all rounded-lg flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                                            </svg>
                                        </div>
                                    </div>
                                </template>

                                <!-- File Link -->
                                <template x-if="file && !file.match(/\.(jpg|jpeg|png|gif|webp)$/i)">
                                    <a :href="`/storage/${file}`" target="_blank"
                                        class="block p-3 bg-white dark:bg-gray-700 rounded-lg border-2 border-gray-200 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-400 transition-all shadow-md hover:shadow-lg">
                                        <div class="flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span class="text-xs text-gray-700 dark:text-gray-300 truncate" x-text="file.split('/').pop()"></span>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Custom Fields Section -->
            <template x-if="Object.keys(viewCustomFields).length > 0">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="font-semibold text-gray-700 dark:text-gray-300">Custom Fields</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <template x-for="(fieldData, fieldId) in viewCustomFields" :key="fieldId">
                            <div class="bg-white dark:bg-gray-700 rounded-lg p-3 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-start gap-2">
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide" 
                                          x-text="(fieldData.field_name || 'Custom Field') + ':'"></span>
                                </div>
                                <div class="mt-1">
                                    <template x-if="fieldData.field_type === 'date' && fieldData.value">
                                        <p class="text-sm text-gray-700 dark:text-gray-300 font-medium" x-text="fieldData.value"></p>
                                    </template>
                                    <template x-if="fieldData.field_type !== 'date' || !fieldData.value">
                                        <p class="text-sm text-gray-700 dark:text-gray-300 break-words" x-text="fieldData.value || '-'"></p>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Empty State for Custom Fields -->
            <template x-if="Object.keys(viewCustomFields).length === 0">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">No custom fields</p>
                </div>
            </template>
        </div>

        <!-- Footer (Fixed) -->
        <div class="p-4 border-t dark:border-gray-700 flex justify-end flex-shrink-0 bg-white dark:bg-gray-900">
            <button @click="viewOpen=false" 
                class="px-6 py-2.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-medium shadow-md hover:shadow-lg transition-all">
                Close
            </button>
        </div>
    </div>
</div>
