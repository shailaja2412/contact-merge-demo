<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex gap-6">
                <main class="flex-1">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
