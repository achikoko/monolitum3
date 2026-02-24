<?php

namespace monolitum\core;

use monolitum\core\panic\DevPanic;

class Find implements MObject
{

    private bool $throwIfNotReceived = true;

    private ?MObject $response = null;
    private bool $fromCache;

    function __construct(public readonly string $class, public readonly bool $wantsToCache)
    {

    }

    public function dontThrowIfNotReceived(): self
    {
        $this->throwIfNotReceived = false;
        return $this;
    }

    function respond(MObject $response, bool $fromCache = false): void
    {
        $this->response = $response;
        $this->fromCache = $fromCache;
    }

    #[\Override]
    public function onNotReceived(): void
    {
        if($this->throwIfNotReceived){
            throw new DevPanic("Active $this->class not found.");
        }
    }

    /**
     * @param class-string $class
     * @param bool $cache
     * @return Find
     */
    static function of(string $class, bool $cache = true, $dontThrowIfNotReceived = false): Find
    {
        $find = new static($class, $cache);
        if($dontThrowIfNotReceived)
            $find->dontThrowIfNotReceived();
        return $find;
    }

    /**
     * Pushes a Find and retrieves itself.
     */
    static function push(string $class, bool $cache = true, $dontThrowIfNotReceived = false): Find
    {
        $find = self::of($class, $cache, $dontThrowIfNotReceived);
        Monolitum::getInstance()->push($find);
        return $find;
    }

    /**
     * Pushes a Find and retrieves the result.
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     */
    static function pushAndGet(string $class, bool $cache = true, $dontThrowIfNotReceived = false, MNode $from = null): mixed
    {
        $find = self::of($class, $cache, $dontThrowIfNotReceived);
        Monolitum::getInstance()->pushFrom($find, $from);
        return $find->getResponse();
    }

    /**
     * Pushes a Find and retrieves the result.
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     */
    static function pushAndGetFrom(string $class, MNode $from = null, bool $cache = true, $dontThrowIfNotReceived = false): mixed
    {
        $find = self::of($class, $cache, $dontThrowIfNotReceived);
        Monolitum::getInstance()->pushFrom($find, $from);
        return $find->getResponse();
    }

    /**
     * @return bool
     */
    public function isFromCache(): bool
    {
        return $this->fromCache;
    }

    public function getResponse(): ?MObject
    {
        return $this->response;
    }


}

