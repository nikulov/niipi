<?php

namespace App\Filament\Resources\FormSubmissions\Tables;

use App\Enums\FormSubmissionStatus;
use App\Models\FormField;
use App\Models\FormSubmission;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        $dynamicColumns = self::buildDynamicDataColumns();
        
        return $table
            ->columns([
                TextColumn::make('status')->label(__('panel.status'))
                    ->badge()
                    ->color(fn(FormSubmissionStatus $state) => $state->getColor())
                    ->formatStateUsing(fn(FormSubmissionStatus $state) => $state->getLabel())
                    ->sortable(),
                
                TextColumn::make('id')->label(__('panel.id'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                
                ...$dynamicColumns,
                
                TextColumn::make('form.name')->label(__('panel.form'))
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('form.applicant_type')->label(__('panel.applicant_type'))
                    ->badge()
                    ->sortable(),
                
                TextColumn::make('created_at')->label(__('panel.created_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                
                SelectFilter::make('status')->label(__('panel.status'))
                    ->columnSpan(3)
                    ->options(FormSubmissionStatus::class)
                    ->multiple()
                    ->preload(),
                
                SelectFilter::make('form.name')->label(__('panel.form_name'))
                    ->columnSpan(3)
                    ->relationship('form', 'name')
                    ->multiple()
                    ->preload(),
                
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('created_from')->label(__('panel.created_from'))
                            ->columnSpan(3)
                            ->maxDate(now())
                            ->minDate(fn() => FormSubmission::min('created_at')),
                        DatePicker::make('created_until')->label(__('panel.created_until'))
                            ->columnSpan(3)
                            ->maxDate(now()),
                    ])
                    ->columns(6)
                    ->columnSpan(6)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            
            ], layout: FiltersLayout::AboveContent)->deferFilters(false)
            ->filtersFormColumns(12)
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    private static function buildDynamicDataColumns(): array
    {
        $names = FormField::query()
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->all();
        
        $labelsByName = FormField::query()
            ->whereIn('name', $names)
            ->select(['name', 'label'])
            ->get()
            ->groupBy('name')
            ->map(fn($rows) => (string)($rows->first()->label ?? ''))
            ->all();
        
        $driver = DB::connection()->getDriverName();
        
        $columns = [];
        
        foreach ($names as $name) {
            $key = is_string($name) ? trim($name) : '';
            if ($key === '') {
                continue;
            }
            
            $columnName = 'data__' . Str::slug($key, '_');
            
            $label = $labelsByName[$key] ?? '';
            $label = is_string($label) && $label !== '' ? $label : $key;
            
            $labelText = strip_tags((string)$label);
            $labelText = preg_replace('/\s+/u', ' ', $labelText) ?? $labelText;
            $labelText = trim($labelText);
            
            $label = $labelText !== '' ? Str::limit($labelText, 16) : $key;
            
            $columns[] = TextColumn::make($columnName)
                ->label($label)
                ->state(function ($record) use ($key) {
                    $data = is_array($record->data) ? $record->data : [];
                    
                    if (!array_key_exists($key, $data)) {
                        return '—';
                    }
                    
                    $value = $data[$key];
                    
                    if ($value === null) {
                        return '—';
                    }
                    
                    if (is_string($value) && trim($value) === '') {
                        return '—';
                    }
                    
                    return is_scalar($value)
                        ? (string)$value
                        : json_encode($value, JSON_UNESCAPED_UNICODE);
                })
                ->wrap()
                ->toggleable(isToggledHiddenByDefault: true)
                ->extraHeaderAttributes([
                    'title' => $labelText,
                ])
                ->searchable(query: function (Builder $query, string $search) use ($key, $driver): void {
                    $search = trim($search);
                    
                    if ($search === '') {
                        return;
                    }
                    
                    $query->where(function (Builder $q) use ($key, $search, $driver): void {
                        if ($driver === 'pgsql') {
                            $q->whereRaw("(data->>? ) ILIKE ?", [$key, "%{$search}%"]);
                            return;
                        }
                        
                        // MySQL / MariaDB
                        $jsonPath = '$."' . str_replace('"', '\\"', $key) . '"';
                        $q->whereRaw(
                            "JSON_UNQUOTE(JSON_EXTRACT(`data`, ?)) LIKE ?",
                            [$jsonPath, "%{$search}%"]
                        );
                    });
                });
        }
        
        return $columns;
    }
}
