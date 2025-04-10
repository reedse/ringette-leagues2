use App\Models\PenaltyCode;

class PenaltyCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PenaltyCode::create([
            'code' => 'TRIP',
            'name' => 'Tripping',
            'default_minutes' => 2,
        ]);

        PenaltyCode::create([
            'code' => 'HOOK',
            'name' => 'Hooking',
            'default_minutes' => 2,
        ]);

        PenaltyCode::create([
            'code' => 'SLASH',
            'name' => 'Slashing',
            'default_minutes' => 2,
        ]);

        // Add other penalty codes similarly, ensuring 'name' is used
        // ... example ...

        // If using a factory or loop, ensure the 'name' field is targeted:
        // Example assuming a factory:
        // PenaltyCode::factory()->count(10)->create();
        // Ensure the factory definition (database/factories/PenaltyCodeFactory.php) uses 'name'
        // Example assuming a loop with an array:
        // $penaltyCodes = [
        //     ['code' => 'INT', 'name' => 'Interference', 'default_minutes' => 2], // Use 'name' here
        // ];
        // foreach ($penaltyCodes as $penalty) {
        //    PenaltyCode::create($penalty);
        // }

        // Replace any other instances of 'description' => ... with 'name' => ...
    }
} 