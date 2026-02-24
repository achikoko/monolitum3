<?php

namespace monolitum\database;

class Query_Like
{

    /**
     * @var string
     */
    private string $string;

    /**
     * @var array<string>
     */
    private array $params;

    /**
     * @param string $string
     * @param array $params
     */
    public function __construct(string $string, string ...$params)
    {
        $this->string = $string;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getString(): string
    {
        return $this->string;
    }

    /**
     * @return array|string[]
     */
    public function getParams(): array
    {
        return $this->params;
    }


}
