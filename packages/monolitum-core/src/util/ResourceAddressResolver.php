<?php

namespace monolitum\core\util;

class ResourceAddressResolver
{

    private bool $strictMode = true;

    /**
     * @var array<callable>
     */
    private array $prefixes = [];

    /**
     * @var array<callable>
     */
    private array $backwardPrefixes = [];

    /**
     * When encountering a url that starts with $prefix, append $additionalPrefix to it.
     * (No matter slashes)
     * @param string $prefix
     * @param string $additionalPrefix
     * @return ResourceAddressResolver
     */
    public function prefix(string $prefix, string $additionalPrefix): self
    {
        $this->prefixes[$prefix] = function ($url) use ($additionalPrefix) {
            return $additionalPrefix . $url;
        };
        $this->backwardPrefixes[$additionalPrefix . $prefix] = function ($url) use ($prefix) {
            return $prefix . $url;
        };
        return $this;
    }

    /**
     * When encountering a url that starts with $prefix, replace the prefix with $replacePrefix.
     * (No matter slashes)
     * @param string $prefix
     * @param string $replacePrefix
     * @return ResourceAddressResolver
     */
    public function replacePrefix(string $prefix, string $replacePrefix): self
    {
        $this->prefixes[$prefix] = function (string $url) use ($prefix, $replacePrefix) {
            return $replacePrefix . substr($url, strlen($prefix));
        };
        $this->backwardPrefixes[$replacePrefix] = function (string $url) use ($prefix, $replacePrefix) {
            return $prefix . substr($url, strlen($replacePrefix));
        };
        return $this;
    }

    /**
     * Strict mode fails resolve if no prefix was matched. Default: true.
     * @param bool $strictMode
     * @return $this
     */
    public function setStrictMode(bool $strictMode = true): self
    {
        $this->strictMode = $strictMode;
        return $this;
    }

    public function resolve(string $url, $backwards=false): ?string
    {
        // Split url into parts and instafail if it has illegal terms
        $split_res = preg_split("/\//", $url, -1);
        foreach ($split_res as $part) {
            if($part === '.' || $part === '..' || trim($part) === '' || substr($part, 0, 1) === '$') {
                return null;
            }
        }
        if($backwards){
            foreach ($this->backwardPrefixes as $prefix => $callable){
                if(str_starts_with($url, $prefix)){
                    return $callable($url);
                }
            }
        }else{
            foreach ($this->prefixes as $prefix => $callable){
                if(str_starts_with($url, $prefix)){
                    return $callable($url);
                }
            }
        }
        return $this->strictMode ? null : $url;
    }

    public static function ofIdle(): ResourceAddressResolver
    {
        $rar = new ResourceAddressResolver();
        return $rar;
    }

    public static function fromPrefix(string $prefix, string $additionalPrefix): ResourceAddressResolver
    {
        $rar = new ResourceAddressResolver();
        $rar->prefix($prefix, $additionalPrefix);
        return $rar;
    }

}
