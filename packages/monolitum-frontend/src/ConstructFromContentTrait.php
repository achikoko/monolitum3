<?php


namespace monolitum\frontend;

use Closure;
use monolitum\i18n\TS;

trait ConstructFromContentTrait
{

    public static function of(Renderable_Node|Renderable|string|TS|array|null $name, ?Closure $builder = null): static
    {
        return new static(function (Renderable_Node $it) use ($builder, $name) {
            $it->append($name);
            if ($builder !== null) {
                $builder($it);
            }
        });
    }

}
