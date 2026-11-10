<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Judul Buku', Book::count())
                ->description('Total koleksi buku'),

            Stat::make('Anggota', User::where('role', 'siswa')->count())
                ->description('Total akun siswa'),

            Stat::make('Sedang Dipinjam', Loan::where('status', 'dipinjam')->count())
                ->description('Total peminjaman aktif'),
        ];
    }
}