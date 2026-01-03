<!-- MERGE MODAL - Master Contact Selection -->
<div x-show="mergeOpen" x-transition.opacity x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">

    <div @click.outside="mergeOpen = false" x-transition.scale
        class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-2xl h-auto max-h-[90vh] flex flex-col">

        <!-- Modal Header -->
        <div class="flex justify-between items-center border-b dark:border-gray-700 p-6 flex-shrink-0">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Merge Contacts</h3>
            <button @click="mergeOpen = false"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-white modal-close">
                ✕
            </button>
        </div>

        <!-- Modal Body -->
        <div class="flex-1 overflow-y-auto p-6">
            <div class="mb-6">
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">
                    You are merging <strong x-text="mergeSecondaryContact.first_name + ' ' + mergeSecondaryContact.last_name"></strong>.
                    Please select the <strong>master contact</strong> that will remain as the primary record.
                </p>

                <!-- Secondary Contact Info -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mb-4">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Secondary Contact (will be merged):</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="mergeSecondaryContact.first_name + ' ' + mergeSecondaryContact.last_name"></p>
                    <p class="text-xs text-gray-600 dark:text-gray-400" x-text="mergeSecondaryContact.email || 'No email'"></p>
                </div>

                <!-- Master Contact Selection -->
                <div>
                    <label class="block text-sm mb-2 text-gray-700 dark:text-gray-300 font-semibold">
                        Select Master Contact <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                            x-model="mergeSearchQuery"
                            @input="filterMergeContacts()"
                            placeholder="Search contacts..."
                            class="w-full px-4 py-2.5 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white mb-3">
                    </div>
                    
                    <div class="max-h-64 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                        <template x-if="filteredMergeContacts.length === 0">
                            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                                <p x-show="mergeContacts.length === 0">Loading contacts...</p>
                                <p x-show="mergeContacts.length > 0 && filteredMergeContacts.length === 0">No contacts found</p>
                            </div>
                        </template>
                        
                        <template x-for="contact in filteredMergeContacts" :key="contact.id">
                            <div @click="selectMasterContact(contact)"
                                class="p-3 border-b dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-gray-800 cursor-pointer transition"
                                :class="mergeMasterContactId == contact.id ? 'bg-blue-100 dark:bg-blue-900' : ''">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white" x-text="contact.first_name + ' ' + contact.last_name"></p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400" x-text="contact.email || 'No email'"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500" x-text="contact.phone_number || 'No phone'"></p>
                                    </div>
                                    <div x-show="mergeMasterContactId == contact.id" class="text-blue-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="border-t dark:border-gray-700 p-6 flex justify-end gap-3 flex-shrink-0 bg-white dark:bg-gray-900">
            <button type="button" @click="mergeOpen = false"
                class="px-6 py-2.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                Cancel
            </button>

            <button type="button" @click="showMergeConfirmation()"
                :disabled="!mergeMasterContactId"
                class="px-6 py-2.5 rounded-full text-white
                       bg-gradient-to-r from-purple-500 to-indigo-600
                       hover:from-purple-600 hover:to-indigo-700
                       disabled:opacity-50 disabled:cursor-not-allowed
                       shadow-md hover:shadow-lg
                       focus:ring-4 focus:ring-purple-300 dark:focus:ring-purple-800
                       transition-all">
                Continue
            </button>
        </div>
    </div>
</div>

<!-- MERGE CONFIRMATION MODAL -->
<div x-show="mergeConfirmOpen" x-transition.opacity x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">

    <div @click.outside="mergeConfirmOpen = false" x-transition.scale
        class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-3xl h-auto max-h-[90vh] flex flex-col">

        <!-- Modal Header -->
        <div class="flex justify-between items-center border-b dark:border-gray-700 p-6 flex-shrink-0">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Confirm Merge</h3>
            <button @click="mergeConfirmOpen = false"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-white modal-close">
                ✕
            </button>
        </div>

        <!-- Modal Body -->
        <div class="flex-1 overflow-y-auto p-6">
            <p class="text-sm text-gray-700 dark:text-gray-300 mb-6">
                Please review the merge details below. The master contact will retain all its data, and data from the secondary contact will be merged where applicable.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- Master Contact -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border-2 border-blue-200 dark:border-blue-800">
                    <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 mb-2">Master Contact (Primary)</p>
                    <p class="font-medium text-gray-900 dark:text-white" x-text="mergeMasterContact.first_name + ' ' + mergeMasterContact.last_name"></p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1" x-text="mergeMasterContact.email || 'No email'"></p>
                    <p class="text-xs text-gray-600 dark:text-gray-400" x-text="mergeMasterContact.phone_number || 'No phone'"></p>
                </div>

                <!-- Secondary Contact -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border-2 border-gray-200 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Secondary Contact (Will be merged)</p>
                    <p class="font-medium text-gray-900 dark:text-white" x-text="mergeSecondaryContact.first_name + ' ' + mergeSecondaryContact.last_name"></p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1" x-text="mergeSecondaryContact.email || 'No email'"></p>
                    <p class="text-xs text-gray-600 dark:text-gray-400" x-text="mergeSecondaryContact.phone_number || 'No phone'"></p>
                </div>
            </div>

            <!-- Merge Preview -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Merge Preview:</p>
                
                <div class="space-y-2 text-sm">
                    <div class="flex items-start">
                        <span class="text-green-600 dark:text-green-400 mr-2">✓</span>
                        <span class="text-gray-700 dark:text-gray-300">Master contact data will be retained</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-blue-600 dark:text-blue-400 mr-2">+</span>
                        <span class="text-gray-700 dark:text-gray-300">Additional emails from secondary contact will be added</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-blue-600 dark:text-blue-400 mr-2">+</span>
                        <span class="text-gray-700 dark:text-gray-300">Additional phone numbers from secondary contact will be added</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-blue-600 dark:text-blue-400 mr-2">+</span>
                        <span class="text-gray-700 dark:text-gray-300">Custom fields from secondary contact will be added (if master doesn't have them)</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-blue-600 dark:text-blue-400 mr-2">+</span>
                        <span class="text-gray-700 dark:text-gray-300">Additional files from secondary contact will be merged</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-orange-600 dark:text-orange-400 mr-2">⚠</span>
                        <span class="text-gray-700 dark:text-gray-300">Secondary contact will be marked as merged (not deleted)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="border-t dark:border-gray-700 p-6 flex justify-end gap-3 flex-shrink-0 bg-white dark:bg-gray-900">
            <button type="button" @click="mergeConfirmOpen = false; mergeOpen = true"
                class="px-6 py-2.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                Back
            </button>

            <form x-bind:action="`{{ url('contacts') }}/${mergeSecondaryContact.id}/merge`" method="POST" class="inline">
                @csrf
                <input type="hidden" name="master_contact_id" x-bind:value="mergeMasterContactId">
                <button type="submit"
                    class="px-6 py-2.5 rounded-full text-white
                           bg-gradient-to-r from-purple-500 to-indigo-600
                           hover:from-purple-600 hover:to-indigo-700
                           shadow-md hover:shadow-lg
                           focus:ring-4 focus:ring-purple-300 dark:focus:ring-purple-800
                           transition-all">
                    Confirm Merge
                </button>
            </form>
        </div>
    </div>
</div>


