<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed voorbeeldgegevens voor Sportcentrum De Linde.
     * Veilig opnieuw uit te voeren: records worden opgezocht en bijgewerkt,
     * in plaats van bij iedere run opnieuw toegevoegd.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'beheerder@delinde.test'],
            [
                'name' => 'Beheerder De Linde',
                'telefoon' => '0612345678',
                'password' => Hash::make('password'),
                'rol' => 'beheerder',
            ]
        );

        $leden = collect([
            ['name' => 'Sam de Vries', 'email' => 'sam@example.test', 'telefoon' => '0611111111'],
            ['name' => 'Noor Bakker', 'email' => 'noor@example.test', 'telefoon' => '0622222222'],
            ['name' => 'Milan Jansen', 'email' => 'milan@example.test', 'telefoon' => '0633333333'],
            ['name' => 'Sara Visser', 'email' => 'sara@example.test', 'telefoon' => '0644444444'],
            ['name' => 'Luca Smit', 'email' => 'luca@example.test', 'telefoon' => '0655555555'],
        ])->map(fn (array $lid) => User::updateOrCreate(
            ['email' => $lid['email']],
            $lid + ['password' => Hash::make('password'), 'rol' => 'lid']
        ));

        $statusIds = collect(['Bevestigd', 'Geannuleerd', 'Wachtlijst'])
            ->mapWithKeys(function (string $naam): array {
                $id = \Illuminate\Support\Facades\DB::table('status')
                    ->where('name', $naam)
                    ->value('id');

                if (! $id) {
                    $id = \Illuminate\Support\Facades\DB::table('status')->insertGetId(['name' => $naam]);
                }

                return [$naam => $id];
            });

        $activiteiten = collect([
            ['naam' => 'Spinning', 'beschrijving' => 'Energieke indoor cyclingles.'],
            ['naam' => 'Yoga', 'beschrijving' => 'Rustige les met aandacht voor houding en ademhaling.'],
            ['naam' => 'Aquagym', 'beschrijving' => 'Bewegen en trainen in het zwembad.'],
        ])->mapWithKeys(function (array $activiteit) use ($admin): array {
            $id = \Illuminate\Support\Facades\DB::table('activiteiten')
                ->where('naam', $activiteit['naam'])
                ->where('gebruiker_id', $admin->id)
                ->value('id');

            if (! $id) {
                $id = \Illuminate\Support\Facades\DB::table('activiteiten')->insertGetId($activiteit + [
                    'gebruiker_id' => $admin->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [$activiteit['naam'] => $id];
        });

        $lesmomenten = collect([
            ['activiteit' => 'Spinning', 'dag' => 1, 'start' => '18:00:00', 'duur' => 60, 'capaciteit' => 16],
            ['activiteit' => 'Yoga', 'dag' => 2, 'start' => '19:00:00', 'duur' => 60, 'capaciteit' => 12],
            ['activiteit' => 'Aquagym', 'dag' => 3, 'start' => '10:00:00', 'duur' => 45, 'capaciteit' => 20],
        ])->mapWithKeys(function (array $les) use ($activiteiten): array {
            $start = now()->startOfWeek()->addDays($les['dag'])->setTimeFromTimeString($les['start']);
            $eind = $start->copy()->addMinutes($les['duur']);
            $id = \Illuminate\Support\Facades\DB::table('lesmomenten')
                ->where('activiteit_id', $activiteiten[$les['activiteit']])
                ->where('start_tijd', $start)
                ->value('id');

            if (! $id) {
                $id = \Illuminate\Support\Facades\DB::table('lesmomenten')->insertGetId([
                    'activiteit_id' => $activiteiten[$les['activiteit']],
                    'start_tijd' => $start,
                    'eind_tijd' => $eind,
                    'max_deelnemers' => $les['capaciteit'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [$les['activiteit'] => $id];
        });

        foreach ($leden as $index => $lid) {
            $lesId = $lesmomenten->values()[$index % $lesmomenten->count()];

            \Illuminate\Support\Facades\DB::table('reserveringen')->updateOrInsert(
                ['gebruiker_id' => $lid->id, 'lesmoment_id' => $lesId],
                ['status_id' => $statusIds['Bevestigd'], 'updated_at' => now(), 'created_at' => now()]
            );
        }

        // Voorbeeld van een annulering: de gebruiker behoudt de unieke reservering,
        // maar de status laat zien dat de plek weer vrij is.
        \Illuminate\Support\Facades\DB::table('reserveringen')->updateOrInsert(
            ['gebruiker_id' => $leden[0]->id, 'lesmoment_id' => $lesmomenten['Aquagym']],
            ['status_id' => $statusIds['Geannuleerd'], 'updated_at' => now(), 'created_at' => now()]
        );

        // Wachtlijstvoorbeeld voor Spinning. Wachtlijst heeft geen statuskolom.
        \Illuminate\Support\Facades\DB::table('wachtlijst')->updateOrInsert(
            ['gebruiker_id' => $leden[4]->id, 'lesmoment_id' => $lesmomenten['Spinning']],
            ['updated_at' => now(), 'created_at' => now()]
        );
    }
}
