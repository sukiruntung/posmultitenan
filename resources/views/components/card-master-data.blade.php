<div class="bg-white rounded-lg shadow-md shadow-lg flex flex-col ">
        <div class="text-white text-center py-3 font-bold text-sm rounded-t-lg bg-blue-600">{{$title}}</div>
        <div class="flex flex-col gap-2 p-2">
            @foreach ($items as $index => $item)
            
                <div class="flex items-center gap-2 bg-blue-400 text-white p-2 rounded-md shadow-sm text-sm cursor-pointer" >
            <span class="bg-white text-blue-600 rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold">{{$index+1}}</span>
            {{$item}}
          </div>
            @endforeach 
       
        </div>
</div>