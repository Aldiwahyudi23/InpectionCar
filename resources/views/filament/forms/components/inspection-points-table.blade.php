@php
    // Panggil data dari viewData
    $availablePoints = $this->getViewData('availablePoints');
    $selectedPoints = $this->getViewData('selectedPoints');
    $searchQuery = $this->getViewData('searchQuery');
@endphp

<div class="space-y-3">
    <!-- Search Box -->
    <div class="flex justify-between items-center">
        <x-filament::input.wrapper class="max-w-md">
            <x-filament::input
                type="text"
                placeholder="Cari inspection point atau component..."
                wire:model.live="searchQuery"
                class="w-full"
            />
        </x-filament::input.wrapper>
        
        <div class="text-sm text-gray-500">
            <span>{{ count($selectedPoints) }}</span> terpilih
        </div>
    </div>

    <!-- Points Table -->
    <div class="border rounded-lg overflow-hidden max-h-96 overflow-y-auto">
        <table class="w-full">
            <tbody>
                @if(count($availablePoints) > 0)
                    @foreach($availablePoints as $componentName => $points)
                    <tr class="bg-gray-50 sticky top-0 z-10">
                        <td colspan="3" class="px-4 py-2 border-b">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold text-gray-700">
                                    {{ $componentName ?: 'No Component' }}
                                    <span class="text-xs text-gray-500 ml-2">({{ count($points) }})</span>
                                </div>
                                <div class="flex space-x-2">
                                    <button type="button" 
                                        wire:click="selectAllComponent('{{ $componentName }}')"
                                        class="text-xs text-primary-600 hover:text-primary-700">
                                        Pilih Semua
                                    </button>
                                    <button type="button" 
                                        wire:click="deselectAllComponent('{{ $componentName }}')"
                                        class="text-xs text-danger-600 hover:text-danger-700">
                                        Batal Semua
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    @foreach($points as $point)
                    <tr class="hover:bg-gray-50 even:bg-gray-50/50">
                        <td class="px-4 py-2 w-12">
                            <x-filament::input.checkbox
                                value="{{ $point->id }}"
                                wire:model="selectedPoints"
                            />
                        </td>
                        <td class="px-4 py-2 text-sm font-medium text-gray-900">
                            {{ $point->name }}
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-500 text-right">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                                Point
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    
                    <!-- Separator -->
                    <tr>
                        <td colspan="3" class="px-4 py-1 bg-gray-100"></td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                            @if($searchQuery)
                                Tidak ditemukan inspection point untuk "{{ $searchQuery }}"
                            @else
                                Tidak ada inspection points tersedia
                            @endif
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <!-- Hidden input untuk menyimpan data -->
    <input type="hidden" name="inspection_point_ids" value="{{ json_encode($selectedPoints) }}" />
</div>

<style>
.sticky {
    position: sticky;
    top: 0;
    background: white;
    box-shadow: 0 1px 0 #e5e7eb;
}
</style>