<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Support\Facades\Cache;

class TranslationService
{

    public function __construct() {}

    public function load(string $lang)
    {
        // Check if the translations are cached
        $cachedTranslations = Cache::get($lang);
        if ($cachedTranslations) {
            return $cachedTranslations;
        }

        // Query the database for translations
        $translations = Translation::whereHas('langue', function ($query) use ($lang) {
            $query->where('code', $lang);
        })->get();

        $translationArray = [];
        foreach ($translations as $key => $translation) {
            $translationArray[$translation->key] = $translation->value;
        }

        // Cache the translations
        Cache::put($lang, $translationArray, 60); // Adjust cache lifetime as needed

        return $translationArray;
    }
}
