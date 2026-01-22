<div class="flex items-center justify-between w-full">
    <div class="flex gap-6 p-4">
        <div class="flex items-center gap-2">
            <label 
                for="from" 
                class="text-sm font-medium whitespace-nowrap text-gray-800 dark:text-gray-200"
            >
                Tanggal Awal
            </label>
            <input
                id="from"
                type="date"
                class="border rounded-md px-2 py-1 
                       border-gray-300 text-gray-800 bg-white 
                       dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100
                       focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                wire:model.live="tableFilters.from"
            />
        </div>

        <div class="flex items-center gap-2">
            <label 
                for="until" 
                class="text-sm font-medium whitespace-nowrap text-gray-800 dark:text-gray-200"
            >
                Tanggal Akhir
            </label>
            <input
                id="until"
                type="date"
                class="border rounded-md px-2 py-1 
                       border-gray-300 text-gray-800 bg-white 
                       dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100
                       focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                wire:model.live="tableFilters.until"
            />
        </div>
    </div>

    <div class="flex items-center p-4">
        <input
            type="text"
            placeholder="Cari data..."
            class="border rounded-md px-3 py-1 
                   border-gray-300 text-gray-800 bg-white placeholder-gray-500
                   dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-400
                   focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            wire:model.live="tableSearch"
        />
    </div>
</div>
