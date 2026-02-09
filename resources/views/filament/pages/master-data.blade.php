<x-filament-panels::page>
    @vite('resources/css/app.css')
  
<div class="bg-gray-100 p-8 dark:bg-gray-900">
  <div class="max-w-7xl mx-auto bg-white rounded-lg shadow-lg dark:bg-gray-600">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4">
      
        @foreach ($masterData->groupBy('masterData.masterDataGroup.master_data_groupname') as $groupName => $items)
                  <livewire:card-master-data 
                        :title="$groupName"
                        :items="$items->toArray()"
                        
                       />            
       
        @endforeach

    </div>
  </div>
</div>

</x-filament-panels::page>

