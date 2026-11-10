<?php

namespace App\Filament\Resources\Loans\Tables;

use App\Models\Loan;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class LoansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('book.title')
                    ->searchable(),
                TextColumn::make('loan_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('returned_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => $state === 'dipinjam' ? 'warning' : 'success'),
                TextColumn::make('fine')
                    ->numeric()
                    ->sortable()
                    ->prefix('Rp '),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('kembalikan')
                    ->label('Kembalikan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Loan $record) => $record->status === 'dipinjam')
                    ->action(function (Loan $record) {
                        DB::transaction(function () use ($record) {
                            $loan = Loan::whereKey($record->id)
                                ->lockForUpdate()
                                ->firstOrFail();

                            if ($loan->status !== 'dipinjam') {
                                return;
                            }

                            $telat = max(
                                0,
                                (int) $loan->due_date->startOfDay()
                                    ->diffInDays(now()->startOfDay(), false)
                            );

                            $loan->update([
                                'returned_at' => now(),
                                'status' => 'dikembalikan',
                                'fine' => $telat * 1000,
                            ]);

                            $loan->book()->increment('stock');
                        });

                        Notification::make()
                            ->title('Buku sudah dikembalikan')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->isAdmin()),
                ]),
            ]);
    }
}