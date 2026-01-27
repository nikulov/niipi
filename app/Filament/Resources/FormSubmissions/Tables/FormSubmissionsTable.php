<?php

namespace App\Filament\Resources\FormSubmissions\Tables;

use App\Enums\FormSubmissionStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FormSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')->label(__('panel.status'))
                    ->badge()
                    ->color(fn (FormSubmissionStatus $state) => $state->color())
                    ->formatStateUsing(fn (FormSubmissionStatus $state) => $state->label())
                    ->sortable(),
                
                TextColumn::make('id')->label(__('panel.id'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                
                TextColumn::make('data_first_two')->label(__('panel.data'))
                    ->state(function ($record) {
                        $data = is_array($record->data) ? $record->data : [];
                        
                        $values = [];
                        foreach ($data as $value) {
                            if ($value === null) {
                                continue;
                            }
                            
                            if (is_string($value) && trim($value) === '') {
                                continue;
                            }
                            
                            $values[] = is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE);
                            if (count($values) >= 2) {
                                break;
                            }
                        }
                        
                        return count($values) ? implode(' · ', $values) : '—';
                    })
                    ->wrap()
                    ->toggleable(),
                
                TextColumn::make('data_email')->label(__('panel.email'))
                    ->state(function ($record) {
                        $data = is_array($record->data) ? $record->data : [];
                        $email = $data['email'] ?? $data['user_email'] ?? null;
                        
                        return is_string($email) && $email !== '' ? $email : '—';
                    })
                    ->searchable(),
                
                TextColumn::make('data_phone')->label(__('panel.phone'))
                    ->state(function ($record) {
                        $data = is_array($record->data) ? $record->data : [];
                        $phone = $data['phone'] ?? $data['tel'] ?? $data['telephone'] ?? null;
                        
                        return is_string($phone) && $phone !== '' ? $phone : '—';
                    })
                    ->toggleable(),
                
                TextColumn::make('form.name')->label(__('panel.form'))
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('created_at')->label(__('panel.created_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
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
