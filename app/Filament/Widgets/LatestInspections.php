<?php

namespace App\Filament\Widgets;

use App\Models\DataInspection\Inspection;
use App\Models\Team\Region;
use App\Models\Team\RegionTeam;
use App\Models\User;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Widgets\TableWidget;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class LatestInspections extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder|Relation|null
    {
        return Inspection::query()
            ->orderBy('inspection_date', 'desc');
    }

    protected function getTableColumns(): array
    {
        return [
          Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
            Tables\Columns\TextColumn::make('car_name')->label('Mobil')->searchable(),
            Tables\Columns\TextColumn::make('user.name')->label('Inspektor')->searchable(),
            Tables\Columns\TextColumn::make('submitted.name')->label('Dibuat')->searchable(),
            Tables\Columns\TextColumn::make('transaction.status')->label('Transaksi')->badge(),
            Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
            Tables\Columns\TextColumn::make('inspection_date')->label('Tanggal')->dateTime(),
        ];
    }

protected function getTableFilters(): array
{
    return [
        Filter::make('region_inspector')
            ->form([
                Select::make('region_id')
                    ->label('Region')
                    ->options(Region::orderBy('name')->pluck('name', 'id')->toArray())
                    ->placeholder('Pilih region')
                    ->reactive(),

                Select::make('user_id')
                    ->label('Inspektur')
                    ->options(function ($get) {
                        // NOTE: $get di sini adalah instance Filament\Forms\Get (invokable),
                        // jadi panggil sebagai callable: $get('field_name')
                        $regionId = $get('region_id');

                        if ($regionId) {
                            $userIds = RegionTeam::where('region_id', $regionId)
                                ->pluck('user_id')
                                ->unique()
                                ->toArray();
                        } else {
                            $userIds = RegionTeam::pluck('user_id')->unique()->toArray();
                        }

                        return User::whereIn('id', $userIds)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->placeholder('Pilih inspektur (opsional)')
                    ->searchable()
                    ->preload(),
            ])
            ->query(function (Builder $query, array $data) {
                // Jika pilih user, prioritaskan user
                if (! empty($data['user_id'])) {
                    $query->where('user_id', $data['user_id']);
                    return;
                }

                // Jika pilih region, filter semua inspection oleh user di region itu
                if (! empty($data['region_id'])) {
                    $userIds = RegionTeam::where('region_id', $data['region_id'])
                        ->pluck('user_id')
                        ->unique();
                    $query->whereIn('user_id', $userIds);
                }
            }),
    ];
}
}
