<?php

namespace App\Filament\Resources\FormSubmissions\Tables;

use App\Enums\FormSubmissionStatus;
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

class FormSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')->label(__('panel.status'))
                    ->badge()
                    ->color(fn (FormSubmissionStatus $state) => $state->getColor())
                    ->formatStateUsing(fn (FormSubmissionStatus $state) => $state->getLabel())
                    ->sortable(),
                
                TextColumn::make('id')->label(__('panel.id'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                
                TextColumn::make('data_first_one')->label(__('panel.data'))
                    ->state(function ($record) {
                        $data = is_array($record->data) ? $record->data : [];
                        
                        foreach ($data as $value) {
                            if ($value === null) {
                                continue;
                            }
                            
                            if (is_string($value) && trim($value) === '') {
                                continue;
                            }
                            
                            return is_scalar($value)
                                ? (string) $value
                                : json_encode($value, JSON_UNESCAPED_UNICODE);
                        }
                        
                        return '—';
                    })
                    ->wrap()
                    ->toggleable(),
                
                TextColumn::make('data_email')->label(__('panel.email'))
                    ->state(function ($record) {
                        $data = is_array($record->data) ? $record->data : [];
                        $email = $data['email'] ?? $data['user_email'] ?? null;
                        
                        return is_string($email) && $email !== '' ? $email : '—';
                    })
                    ->searchable(
                        query: function ($query, string $search): void {
                            $query->where(function ($q) use ($search) {
                                $q->where('data->email', 'like', "%{$search}%")
                                    ->orWhere('data->user_email', 'like', "%{$search}%");
                            });
                        }
                    ),
                
                TextColumn::make('data_phone')->label(__('panel.phone'))
                    ->state(function ($record) {
                        $data = is_array($record->data) ? $record->data : [];
                        $phone = $data['phone'] ?? $data['tel'] ?? $data['telephone'] ?? null;
                        
                        return is_string($phone) && $phone !== '' ? $phone : '—';
                    })
                    ->toggleable()
                    ->searchable(
                        query: function ($query, string $search): void {
                            $query->where(function ($q) use ($search) {
                                $q->where('data->phone', 'like', "%{$search}%")
                                    ->orWhere('data->tel', 'like', "%{$search}%")
                                    ->orWhere('data->telephone', 'like', "%{$search}%");
                            });
                        }
                    ),
                
                TextColumn::make('form.name')->label(__('panel.form'))
                    ->sortable()
                    ->searchable(),
                
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
}
