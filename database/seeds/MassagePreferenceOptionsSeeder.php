<?php

use Illuminate\Database\Seeder;
use App\MassagePreferenceOption;

class MassagePreferenceOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MassagePreferenceOption::create([
            'name' => 'Muscles',
            'massage_preference_id' => 4,
        ]);
        MassagePreferenceOption::create([
            'name' => 'Joints',
            'massage_preference_id' => 4,
        ]);
        MassagePreferenceOption::create([
            'name' => 'Tendons',
            'massage_preference_id' => 4,
        ]);
        MassagePreferenceOption::create([
            'name' => 'Discs',
            'massage_preference_id' => 4,
        ]);
        MassagePreferenceOption::create([
            'name' => 'Bones',
            'massage_preference_id' => 4,
        ]);
        MassagePreferenceOption::create([
            'name' => 'Nerve',
            'massage_preference_id' => 4,
        ]);
        MassagePreferenceOption::create([
            'name' => 'surgeries',
            'massage_preference_id' => 5,
        ]);
        MassagePreferenceOption::create([
            'name' => 'fractures',
            'massage_preference_id' => 5,
        ]);
        MassagePreferenceOption::create([
            'name' => 'accidents',
            'massage_preference_id' => 5,
        ]);
        MassagePreferenceOption::create([
            'name' => 'Skin',
            'massage_preference_id' => 6,
        ]);
        MassagePreferenceOption::create([
            'name' => 'Hair',
            'massage_preference_id' => 6,
        ]);
        MassagePreferenceOption::create([
            'name' => 'Nail',
            'massage_preference_id' => 6,
        ]);
        MassagePreferenceOption::create([
            'name' => 'Other',
            'massage_preference_id' => 6,
        ]);

    }
}
