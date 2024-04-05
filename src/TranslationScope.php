<?php
namespace alsmman\Translation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Contracts\Database\Eloquent\Builder;

class TranslationScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected $extensions = ['WithoutTranslation'];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->with('translations');
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }


    /**
     * Add the without-translation extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    protected function addWithoutTranslation(Builder $builder)
    {
        $builder->macro('withoutTranslation', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
