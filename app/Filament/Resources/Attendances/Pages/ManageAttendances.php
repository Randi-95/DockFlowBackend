<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Models\Attendance;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Carbon;

class ManageAttendances extends ManageRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
        
            Action::make('markAbsentToday')
                ->label('Mark Absent Today')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Mark Absent — Today')
                ->modalDescription(
                    'This will automatically create an \'Absent\' record for all active crew members '
                    . 'who do NOT yet have an attendance entry for today (' . now()->timezone('Asia/Jakarta')->format('d M Y') . '). '
                    . 'Are you sure you want to continue?'
                )
                ->modalSubmitActionLabel('Yes, Mark Absent')
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->action(function () {
                    $today = Carbon::now('Asia/Jakarta')->toDateString();

                    $alreadyPresentIds = Attendance::whereDate('date', $today)
                        ->pluck('user_id')
                        ->toArray();

                    $absentCrew = User::where('role', '!=', 'warehouse_admin')
                        ->where('is_active', true)
                        ->whereNotIn('id', $alreadyPresentIds)
                        ->get();

                    if ($absentCrew->isEmpty()) {
                        Notification::make()
                            ->title('No action needed')
                            ->body('All active crew members already have an attendance record for today.')
                            ->info()
                            ->send();

                        return;
                    }

                    $now = Carbon::now('Asia/Jakarta');

                    $records = $absentCrew->map(fn (User $user) => [
                        'user_id'    => $user->id,
                        'date'       => $today,
                        'check_in'   => $now->copy()->setTime(0, 0, 0)->toDateTimeString(),
                        'check_out'  => null,
                        'status'     => 'absent',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->toArray();

                    Attendance::insert($records);

                    Notification::make()
                        ->title('Absent records created')
                        ->body($absentCrew->count() . ' crew member(s) have been marked as Absent for today.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
