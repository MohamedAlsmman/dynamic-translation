<?php

namespace alsmman\Translation;

use alsmman\Translation\Translation;
use alsmman\Translation\TranslationScope;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder withoutTranslation()
 */
trait HasTranslations
{


    public static function bootHasTranslations()
    {
        static::addGlobalScope(new TranslationScope);

        self::autoTranslate(function (self $model) {
            $config = config('dynamic-translation.models');

            $langs = config('dynamic-translation.languages');
            $class = class_basename($model);
            $requestData = request()->toArray();
            $translationData = [];

            // Check if the model class has translations configured
            if (isset($config[$class])) {
                // Iterate through configured attributes
                foreach ($config[$class] as $attribute) {
                    // Get the request key for the attribute
                    $requestKeyPrefix = $attribute . '_';

                    // Filter request data for keys matching the attribute
                    $attributeData = collect($requestData)->filter(function ($value, $key) use ($requestKeyPrefix) {
                        return strpos($key, $requestKeyPrefix) === 0 && $value !== null;
                    });

                    // Process filtered data for translation
                    $attributeData->each(function ($value, $key) use ($langs, &$translationData, $attribute) {
                        $keyParts = explode('_', $key);
                        $lang = end($keyParts);

                        if (in_array($lang, $langs)) {
                            array_pop($keyParts);
                            $column = implode('_', $keyParts);
                            $translationData[] = ['column' => $column, 'lang' => $lang, 'value' => $value];
                        }
                    });
                }

                // Create translations if data is available
                if (!empty($translationData)) {
                    if (!$model->wasRecentlyCreated) {
                        $model->translations()->delete();
                    }
                    $model->translations()->createMany($translationData);
                }
            }
        });
    }

    /**
     * Register a saved model event with the dispatcher.
     *
     * @param  \Illuminate\Events\QueuedClosure|\Closure|string|array  $callback
     * @return void
     */
    public static function autoTranslate($callback)
    {
        static::registerModelEvent('autoTranslate', $callback);
    }

    // public function initializeHasTranslations()
    // {
    // }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function trans($column, $lang = null)
    {
        $lang = $lang ?? app()->getLocale();

        // Eager loaded translations form database
        $translation = $this->translations
            ->where('column', $column)
            ->where('lang', $lang)
            ->first();

        if ($translation) {
            return $translation->value;
        }

        if (isset($this->attributes[$column])) {
            return $this->attributes[$column];
        }

        return null;
    }

    public function autoTrans()
    {
        $this->fireModelEvent('autoTranslate');
    }
}
