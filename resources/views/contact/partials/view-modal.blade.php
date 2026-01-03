<!-- VIEW CONTACT MODAL -->
<div x-show="viewOpen" x-transition.opacity x-cloak style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">

    <div @click.outside="viewOpen=false"
        class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-md overflow-hidden">

        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
            <h3 class="text-lg font-semibold">Contact Details</h3>
            <button @click="viewOpen=false">✕</button>
        </div>

        <!-- Body -->
        <div class="p-6 text-center space-y-6">

            <!-- Profile Image -->
            <img
                :src="viewContact.profile_picture ?
                    `/storage/${viewContact.profile_picture}` :
                    'https://ui-avatars.com/api/?name=' + viewContact.first_name"
        class="w-32 h-32 mx-auto rounded-full object-cover border">

    <!-- Name -->
    <h2 class="text-xl font-semibold">
        <span x-text="viewContact.first_name"></span>
        <span x-text="viewContact.last_name"></span>
    </h2>

    <!-- Phones -->
    <div class="text-left">
        <p class="font-medium text-gray-600">📞 Phone</p>
        <template x-for="phone in viewContact.phone_numbers ?? []">
            <p x-text="phone"></p>
        </template>
    </div>

    <!-- Emails -->
    <div class="text-left">
        <p class="font-medium text-gray-600">📧 Email</p>
        <template x-for="email in viewContact.emails ?? []">
            <p x-text="email"></p>
        </template>
    </div>

    <!-- Gender -->
    <div class="text-left">
        <p class="font-medium text-gray-600">⚧ Gender</p>
        <p x-text="viewContact.gender == 1 ? 'Male ' :
                viewContact.gender == 2 ? 'Female' : 'Other'">
            </p>
        </div>

        <!-- Documents -->
        <div class="text-left">
            <p class="font-medium text-gray-600 mb-2">📎 Documents</p>

            <div class="grid grid-cols-3 gap-3">
                <template x-for="file in viewContact.additional_files ?? []">
                    <div>
                        <!-- Image -->
                        <template x-if="file.match(/\.(jpg|jpeg|png)$/i)">
                            <img :src="`/storage/${file}`"
                                class="rounded-lg h-24 w-full object-cover cursor-pointer"
                                @click="window.open(`/storage/${file}`)">
                        </template>

                        <!-- File -->
                        <template x-if="!file.match(/\.(jpg|jpeg|png)$/i)">
                            <a :href="`/storage/${file}`" target="_blank"
                                class="block text-xs text-blue-600 underline truncate">
                                <span x-text="file.split('/').pop()"></span>
                            </a>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <!-- Custom Fields -->
        <template x-if="Object.keys(viewCustomFields).length > 0">
            <div class="text-left">
                <p class="font-medium text-gray-600 mb-2">📋 Custom Fields</p>
                <div class="space-y-2">
                    <template x-for="(fieldData, fieldId) in viewCustomFields" :key="fieldId">
                        <div class="flex flex-col">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400" x-text="fieldData.field_name || 'Custom Field'"></span>
                            <span class="text-sm text-gray-700 dark:text-gray-300" x-text="fieldData.value || '-'"></span>
                        </div>
                    </template>
                </div>
            </div>
        </template>

    </div>

    <!-- Footer -->
    <div class="p-4 border-t text-center">
        <button @click="viewOpen=false" class="px-6 py-2 rounded-full bg-gray-200 dark:bg-gray-700">
            Close
        </button>
    </div>

</div>
</div>

