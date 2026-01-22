
@if ($canCreate)
    <div class="flex justify-end">
        @if(isset($createUrl) && isset($buttonLabel))
            <a href="{{ $createUrl }}" 
            class="fi-btn fi-btn-color-primary fi-btn-size-md inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-500 focus:bg-primary-500 transition duration-75">
                <svg class="fi-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ $buttonLabel }}
            </a>
        @endif
    </div>
@endif
