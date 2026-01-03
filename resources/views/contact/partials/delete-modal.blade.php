<!-- DELETE CONFIRM MODAL -->
<div x-show="deleteOpen" x-transition.opacity x-cloak style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

    <div @click.outside="deleteOpen = false" x-transition.scale
        class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-md p-6">

        <div class="flex justify-between items-center border-b dark:border-gray-700 pb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Contact</h3>
            <button @click="deleteOpen = false"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
        </div>

        <div class="mt-6">
            <p class="text-sm text-gray-700 dark:text-gray-300">Are you sure you want to delete this contact?
                This action cannot be undone.</p>
            <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                <button type="button" @click="deleteOpen = false"
                    class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200">Cancel</button>
                <button type="button" @click="deleteContact()"
                    class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Delete</button>
            </div>
        </div>

    </div>
</div>

