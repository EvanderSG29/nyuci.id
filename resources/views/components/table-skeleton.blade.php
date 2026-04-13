@props([
    'columns' => [],
    'rows' => 5,
    'detailColumns' => [],
    'avatarColumns' => [],
    'badgeColumns' => [],
    'actionColumn' => null,
])

@php
    $detailColumns = collect($detailColumns)->map(fn ($column) => (int) $column)->all();
    $avatarColumns = collect($avatarColumns)->map(fn ($column) => (int) $column)->all();
    $badgeColumns = collect($badgeColumns)->map(fn ($column) => (int) $column)->all();
    $actionColumn = $actionColumn !== null ? (int) $actionColumn : null;

    $lineWidths = [92, 84, 71, 88, 76, 64];
    $subLineWidths = [56, 48, 61, 52, 45];
    $shortLineWidths = [64, 57, 49, 61, 53];
@endphp

<flux:skeleton.group animate="shimmer">
    <flux:table>
        <flux:table.columns>
            @foreach ($columns as $column)
                <flux:table.column>{{ $column }}</flux:table.column>
            @endforeach
        </flux:table.columns>

        <flux:table.rows>
            @foreach (range(1, $rows) as $rowIndex)
                <flux:table.row>
                    @foreach ($columns as $columnIndex => $column)
                        <flux:table.cell :align="$actionColumn === $columnIndex ? 'end' : null">
                            @if (in_array($columnIndex, $detailColumns, true))
                                <div class="flex items-center gap-3">
                                    @if (in_array($columnIndex, $avatarColumns, true))
                                        <flux:skeleton class="size-5 rounded-full" />
                                    @endif

                                    <div class="min-w-0 flex-1 space-y-2">
                                        <flux:skeleton.line style="width: {{ $lineWidths[($rowIndex + $columnIndex) % count($lineWidths)] }}%" />
                                        <flux:skeleton.line style="width: {{ $subLineWidths[($rowIndex + $columnIndex) % count($subLineWidths)] }}%" />
                                    </div>
                                </div>
                            @elseif (in_array($columnIndex, $badgeColumns, true))
                                <flux:skeleton class="h-6 w-20 rounded-full" />
                            @elseif ($actionColumn === $columnIndex)
                                <div class="flex justify-end">
                                    <flux:skeleton class="size-8 rounded-xl" />
                                </div>
                            @else
                                <flux:skeleton.line style="width: {{ $shortLineWidths[($rowIndex + $columnIndex) % count($shortLineWidths)] }}%" />
                            @endif
                        </flux:table.cell>
                    @endforeach
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</flux:skeleton.group>
