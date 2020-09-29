<?php

namespace Database\Seeders;

use App\Models\Organization\Organization;
use App\Models\Rehearsal;
use App\Models\User;
use Belamov\PostgresRange\Ranges\TimestampRange;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;

class RehearsalsForStatisticsSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @param  Organization  $organization
     * @param  CarbonInterface  $date
     * @param  int  $years
     * @param  int  $months
     * @param  int  $days
     * @param  int  $price
     * @param  int  $perDay
     * @return void
     */
    public function run(
        Organization $organization,
        CarbonInterface $date,
        int $years,
        int $months,
        int $days,
        int $price,
        int $perDay
    ): void {
        $user = User::factory()->create();

        for ($i = 0; $i < $years; $i++) {
            for ($j = 0; $j < $months; $j++) {
                for ($k = 0; $k < $days; $k++) {
                    for ($l = 0; $l < $perDay; $l++) {
                        Rehearsal::factory()->create([
                            'organization_id' => $organization->id,
                            'user_id' => $user->id,
                            'price' => $price,
                            'time' => new TimestampRange($date, $date->clone()->addHour()),
                        ]);
                        $date = $date->addHours(3);
                    }
                    $date = $date->addDay()->setHour(10);
                }
                $date = $date->addMonth()->setDay(1);
            }
            $date = $date->addYear()->setMonth(1);
        }
    }
}
