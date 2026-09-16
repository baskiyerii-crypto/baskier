<?php

namespace App\Support;

final class FreelancerCategories
{
    /** @return list<string> */
    public static function keys(): array
    {
        return ['logo', 'brochure', 'digital', 'wordpress', 'other'];
    }

    public static function label(string $key): string
    {
        $translated = __('home.freelancer_cat.'.$key);
        if ($translated !== 'home.freelancer_cat.'.$key) {
            return $translated;
        }

        return $key;
    }

    /** @return array<string, array{label:string,seed:string}> */
    public static function catalog(): array
    {
        $seeds = [
            'logo' => 'flogo',
            'brochure' => 'fbrochure',
            'digital' => 'fdigital',
            'wordpress' => 'fwordpress',
            'other' => 'fother',
        ];

        $out = [];
        foreach (self::keys() as $key) {
            $out[$key] = [
                'label' => self::label($key),
                'seed' => $seeds[$key],
            ];
        }

        return $out;
    }
}
