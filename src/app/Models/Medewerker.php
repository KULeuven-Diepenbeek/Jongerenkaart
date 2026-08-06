<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Medewerker extends Model
{
    public const COLLECTION = 'medewerkers';

    protected $connection = 'mongodb';

    protected $collection = self::COLLECTION;

    public $timestamps = false;

    protected $guarded = [];

    /**
     * @return array<int, array{regio: string, naam: string, email: string}>
     */
    public static function defaults(): array
    {
        return [
            ['regio' => 'Regio Antwerpen', 'naam' => 'Phebe Devadder', 'email' => 'phebe.devadder@vlaanderen.be'],
            ['regio' => 'Regio Limburg', 'naam' => 'Jan Theys', 'email' => 'jan.theys@vlaanderen.be'],
            ['regio' => 'Regio Oost-Vlaanderen', 'naam' => 'Christophe Pyra', 'email' => 'christophe.pyra@vlaanderen.be'],
            ['regio' => 'Regio Vlaams-Brabant', 'naam' => 'Leen Lauwers', 'email' => 'leen.lauwers@vlaanderen.be'],
            ['regio' => 'Regio Vlaams-Brabant', 'naam' => 'Jan Theys', 'email' => 'jan.theys@vlaanderen.be'],
            ['regio' => 'Regio West-Vlaanderen', 'naam' => 'Sven De Meulenaere', 'email' => 'sven.demeulenaere@vlaanderen.be'],
            ['regio' => 'Brussels Hoofdstedelijk Gewest', 'naam' => 'Germaine Vanderstappen', 'email' => 'germaine.vanderstappen@kenniscentrumwwz.be'],
            ['regio' => 'Ondersteuning', 'naam' => 'Bieke Formesyn', 'email' => 'bieke.formesyn@vlaanderen.be'],
            ['regio' => 'Ondersteuning', 'naam' => 'Karine Olislaegers', 'email' => 'karine.olislaegers@vlaanderen.be'],
            ['regio' => 'Coordinator', 'naam' => 'Patricia Werbrouck', 'email' => 'patricia.werbrouck@vlaanderen.be'],
        ];
    }

    public static function seedDefaultsIfEmpty(): void
    {
        if (self::query()->exists()) {
            return;
        }

        foreach (self::defaults() as $medewerker) {
            self::query()->create($medewerker);
        }
    }

    public static function socialeKaartTeam()
    {
        self::seedDefaultsIfEmpty();

        return self::query()
            ->orderBy('regio')
            ->orderBy('naam')
            ->get();
    }
}
