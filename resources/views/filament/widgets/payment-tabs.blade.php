
<div class="dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 mb-6">
    <div class="fi-wi-stats-overview-stats-ctn grid gap-6 md:grid-cols-2">
        <div class="col-span-full">
            <div class="fi-tabs flex overflow-x-auto">
                <div class="fi-tabs-items flex gap-x-1 rounded-lg bg-blue-50 p-1 dark:bg-blue-900/20">
                    @foreach($tabs as $tab)
                        <a href="{{ $tab['url'] }}" 
                           class="fi-tabs-item group flex items-center gap-x-2 rounded-md px-4 py-3 text-sm font-medium outline-none transition duration-75 
                                  {{ $tab['active'] 
                                      ? 'bg-blue-600 text-primary-600 shadow-sm ring-1 ring-blue-600/20 dark:bg-blue-500 dark:text-white' 
                                      : 'text-blue-600 hover:text-blue-800 hover:bg-blue-100 focus:text-blue-800 focus:bg-blue-100 dark:text-blue-400 dark:hover:text-blue-200 dark:hover:bg-blue-800/50' 
                                  }}">
                            {{ $tab['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.querySelectorAll('.fi-tabs-item').forEach(el => {
        if (el.dataset.tab === '{{ $active }}') {
            el.classList.add('bg-blue-600','text-white','shadow-sm','ring-1','ring-blue-600/20');
        }
    });
</script>
@endpush
