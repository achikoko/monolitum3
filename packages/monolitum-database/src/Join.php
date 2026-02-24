<?php

namespace monolitum\database;

use monolitum\core\Find;
use monolitum\model\Model;

class Join extends Query
{

    /**
     * @var string[]
     */
    private array $localAttrs;

    public function __construct(DatabaseManager $manager, Model $model, array $attrs)
    {
        parent::__construct($manager, $model);
        $this->localAttrs = $attrs;
    }

    /**
     * @return string[]
     */
    public function getLocalAttrs(): array
    {
        return $this->localAttrs;
    }

    public static function of(string|Model $model, array|string $attrs): Join
    {
        /** @var DatabaseManager $manager */
        $manager = Find::pushAndGet(DatabaseManager::class);
        return $manager->newJoin($model, $attrs);
    }

}
