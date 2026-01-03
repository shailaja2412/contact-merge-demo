<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>


    <div
        x-data="{
        open: false,
        deleteOpen: false,
        deleteId: null,

        isEdit: false,
        editId: null,
        field_name: '',
        field_type: '',

        openCreate() {
            this.isEdit = false;
            this.editId = null;
            this.field_name = '';
            this.field_type = '';
            this.open = true;
        },

        openEdit(field) {
            this.isEdit = true;
            this.editId = field.id;
            this.field_name = field.field_name;
            this.field_type = field.field_type;
            this.open = true;
        }
    }">

        <!-- Header -->
        <div class="p-4 flex flex-wrap gap-4 justify-between items-center">
            <!-- Search -->
            <div class="relative w-64">
                <label for="search" class="sr-only">Search custom fields</label>

                <!-- Search Icon -->
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                    🔍
                </span>

                <!-- Input -->
                <input
                    type="text"
                    id="search"
                    name="search"
                    placeholder="Search field name..."
                    class="w-full pl-10 pr-4 py-2.5
               bg-gray-100 dark:bg-gray-800
               border border-gray-300 dark:border-gray-600
               text-gray-900 dark:text-gray-100
               rounded-full text-sm
               focus:ring-2 focus:ring-blue-500
               focus:border-blue-500
               transition" />
            </div>

            <!-- Button -->
            <button
                @click="openCreate()"
                class="px-6 py-2.5 text-sm font-medium text-white
                       rounded-full
                       bg-gradient-to-r from-blue-500 to-indigo-600
                       hover:from-blue-600 hover:to-indigo-700
                       shadow-md hover:shadow-lg
                       focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800
                       transition-all duration-200">
                + Add Custom Field
            </button>
        </div>

        @if(session('success'))
        <div class="p-4">
            <div class="text-sm text-green-600 dark:text-green-400">{{ session('success') }}</div>
        </div>
        @endif
        @if(session('error'))
        <div class="p-4">
            <div class="text-sm text-red-600 dark:text-red-400">{{ session('error') }}</div>
        </div>
        @endif

        <!-- Table -->
        <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                <tr>
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Field</th>
                    <th class="px-6 py-3">Type</th>
                    <th class="px-6 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody id="custom-fields-list">
                @include('admin.custom_fields.partials.rows', ['customFields' => $customFields])
            </tbody>
        </table>

        <!-- MODAL -->
        <div
            x-show="open"
            x-transition.opacity
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

            <div
                @click.outside="open = false"
                x-transition.scale
                class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-lg p-6">

                <!-- Modal Header -->
                <div class="flex justify-between items-center border-b dark:border-gray-700 pb-4">
                    <h3 id="modal-title" class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-text="isEdit ? 'Edit Custom Field' : 'Create Custom Field'"></span>
                    </h3>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white modal-close">
                        ✕
                    </button>
                </div>

                <!-- Form -->
                <form id="custom-field-form" class="mt-6 space-y-4" method="POST" x-bind:action="isEdit
        ? `{{ url('admin/custom_fields') }}/${editId}`
        : `{{ route('admin.custom_fields.store') }}`">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <div>
                        <label class="block text-sm mb-1 text-gray-700 dark:text-gray-300">
                            Field Name
                        </label>
                        <input
                            type="text"
                            name="field_name"
                            x-model="field_name"
                            required
                            class="w-full px-4 py-2.5 rounded-lg
           bg-gray-100 dark:bg-gray-800
           border border-gray-300 dark:border-gray-600
           text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm mb-1 text-gray-700 dark:text-gray-300">
                            Field Type
                        </label>
                        <select
                            name="field_type"
                            x-model="field_type"
                            class="w-full px-4 py-2.5 rounded-lg
           bg-gray-100 dark:bg-gray-800
           border border-gray-300 dark:border-gray-600
           text-gray-900 dark:text-white">
                            <option value="">Select Field Type</option>
                            @foreach($customFieldsTypes as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Actions -->
                    <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                        <button
                            type="button"
                            @click="open = false"
                            class="px-6 py-2.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="px-6 py-2.5 rounded-full text-white
                                   bg-gradient-to-r from-blue-500 to-indigo-600
                                   hover:from-blue-600 hover:to-indigo-700
                                   shadow-md hover:shadow-lg
                                   focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800
                                   transition-all">
                            <span x-text="isEdit ? 'Update' : 'Save'"></span>

                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- DELETE CONFIRM MODAL -->
        <div
            x-show="deleteOpen"
            x-transition.opacity
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

            <div
                @click.outside="deleteOpen = false"
                x-transition.scale
                class="bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-md p-6">

                <div class="flex justify-between items-center border-b dark:border-gray-700 pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Custom Field</h3>
                    <button @click="deleteOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
                </div>

                <form x-bind:action="`{{ url('admin/custom_fields') }}/${deleteId}`" method="POST" class="mt-6">
                    @csrf
                    <input type="hidden" name="_method" value="DELETE">
                    <p class="text-sm text-gray-700 dark:text-gray-300">Are you sure you want to delete this custom field? This action cannot be undone.</p>
                    <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                        <button type="button" @click="deleteOpen = false" class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded bg-red-600 text-white">Delete</button>
                    </div>
                </form>

            </div>
        </div>

    </div>



    <script>
        (function() {
            const searchInput = document.getElementById('search');
            if (!searchInput) return;

            const searchBtn = document.getElementById('search-btn');
            const clearBtn = document.getElementById('clear-search');
            const spinner = document.getElementById('search-spinner');
            const list = document.getElementById('custom-fields-list');

            let debounceTimer = null;
            let controller = null;

            function showSpinner(show) {
                if (!spinner) return;
                if (show) {
                    spinner.classList.remove('hidden');
                    spinner.classList.add('inline-flex');
                } else {
                    spinner.classList.remove('inline-flex');
                    spinner.classList.add('hidden');
                }
            }

            function updateClearBtn() {
                if (!clearBtn) return;
                if (searchInput.value.length > 0) {
                    clearBtn.classList.remove('hidden');
                    clearBtn.classList.add('inline-flex');
                } else {
                    clearBtn.classList.remove('inline-flex');
                    clearBtn.classList.add('hidden');
                }
            }

            async function doSearch(query) {
                console.debug('[search] starting search for:', query);

                if (controller) controller.abort();
                controller = new AbortController();
                const signal = controller.signal;

                showSpinner(true);

                try {
                    const url = new URL("{{ route('admin.custom_fields.index') }}", window.location.origin);
                    url.searchParams.set('search', query);

                    const resp = await fetch(url.toString(), {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        signal,
                    });

                    const text = await resp.text();
                    console.debug('[search] status', resp.status, 'response length', text.length);

                    if (resp.status === 200 && text.indexOf('<tr') !== -1) {
                        list.innerHTML = text;
                        return;
                    }

                    if (resp.status === 401 || resp.status === 419) {
                        console.warn('[search] auth issue, reloading');
                        location.reload();
                        return;
                    }

                    if (text.indexOf('<html') !== -1 || text.indexOf('<!doctype') !== -1) {
                        console.warn('[search] full HTML received, possible redirect');
                        list.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Session may have expired. Please reload the page.</td></tr>';
                        return;
                    }

                    console.warn('[search] unexpected response');
                    list.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No results or unexpected response. Check console/network.</td></tr>';

                } catch (err) {
                    if (err.name === 'AbortError') {
                        console.debug('[search] request aborted');
                        return;
                    }
                    console.error('[search] fetch error', err);
                    list.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Search failed. Try again later.</td></tr>';
                } finally {
                    showSpinner(false);
                }
            }

            // Debounced input
            searchInput.addEventListener('input', function() {
                updateClearBtn();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => doSearch(searchInput.value), 350);
            });

            // Enter key
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(debounceTimer);
                    doSearch(searchInput.value);
                }
            });

            // Search button
            if (searchBtn) {
                searchBtn.addEventListener('click', function() {
                    clearTimeout(debounceTimer);
                    doSearch(searchInput.value);
                });
            }

            // Clear
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    updateClearBtn();
                    doSearch('');
                    searchInput.focus();
                });
            }

            // init
            updateClearBtn();
        })();
    </script>

</x-admin-layout>