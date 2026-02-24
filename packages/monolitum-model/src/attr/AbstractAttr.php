<?php
namespace monolitum\model\attr;

use monolitum\model\AnonymousModel;
use monolitum\model\AttrExt;

abstract class AbstractAttr implements Attr
{

    private string $id;

    private AnonymousModel $model;

    /**
     * @var array<AttrExt>
     */
    private array $extensions = [];

    public function ext(AttrExt $extension): self
    {
        $this->extensions[] = $extension;
        return $this;
    }

    public function findExtension(string $class): ?AttrExt
    {
        foreach ($this->extensions as $extension) {
            if($extension instanceof $class)
                return $extension;
        }
        return null;
    }

    public function _setModelId(AnonymousModel $model, string $id): void
    {
        $this->model = $model;
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getModel(): AnonymousModel
    {
        return $this->model;
    }

    public function __toString()
    {
        return $this->getModel() . "->" . $this->getId();
    }

    public static function of(): static
    {
        return new static();
    }

}

