<?php

namespace monolitum\backend\params;

use monolitum\core\Find;
use monolitum\core\Monolitum;

class Link
{

    const HISTORY_BEHAVIOR_PRESERVE = "preserve";
    const HISTORY_BEHAVIOR_PUSH = "push";
    const HISTORY_BEHAVIOR_POP = "pop";

    private Path $path;

    /**
     * @var bool|string[]
     */
    private array|bool $copyParams = false;

    /**
     * @var string[]
     */
    private array $removeParams = [];

    /**
     * @var array<string, string>
     */
    private array $addParams = [];

    private ?string $historyBehavior = self::HISTORY_BEHAVIOR_PRESERVE;

    public function __construct(?Path $path = null)
    {
        if($path === null){
            $this->path = Path::fromRelative(0);
        }else{
            $this->path = $path;
        }
    }

    public static function fromUrl(string $url): Link
    {

        if(strlen($url) > 0){
            $pathStr = parse_url("s://h:0/" . $url, PHP_URL_PATH);
            $query = parse_url("s://h:0/" . $url, PHP_URL_QUERY);
//                $pathStr = $parsed['path'];
//                $query = $parsed['query'];

            $queryResult = [];
            if($query !== null && strlen($query) > 0){
                parse_str($query, $queryResult);
            }

            $path = Path::fromUrl($pathStr);

            $link = Link::from($path);
            $link->addParams($queryResult);

            return $link;
        }else{
            return Link::from();
        }

    }

    public function setCopyParams(string ...$specificParams): self
    {
        $this->copyParams = !$specificParams ? true : $specificParams;
        return $this;
    }

    public function addCopyParams(string ...$specificParams): self
    {
        if($this->copyParams === false){
            $this->setCopyParams(...$specificParams);
        }else if(is_array($this->copyParams)){
            $this->copyParams += $specificParams;
        }
        return $this;
    }

    public function setCopyParamsExcept(string ...$exceptions): self
    {
        $this->copyParams = true;
        $this->removeParams += $exceptions;
        return $this;
    }

    public function setCopyAllParams(): self
    {
        $this->copyParams = true;
        return $this;
    }

    /**
     * @param array<string, string> $addParams
     * @return $this
     */
    public function addParams(array $addParams): self
    {
        foreach($addParams as $param => $value){
            if (($key = array_search($param, $this->removeParams)) !== false) {
                // Remove from remove
                unset($this->removeParams[$key]);
            }

            if (is_array($this->copyParams) && ($key = array_search($param, $this->copyParams)) !== false) {
                // Remove from copy
                unset($this->copyParams[$key]);
            }

            $this->addParams[$param] = $value;
        }
        return $this;
    }

    /**
     * @param array<string> $removeParams
     * @return $this
     */
    public function removeParams(string ...$removeParams): self
    {
        foreach($removeParams as $param){
            if (key_exists($param, $this->addParams)) {
                // Remove from add
                unset($this->addParams[$param]);
            }else if($this->copyParams === true) {
                $this->removeParams += $removeParams;
            }else if(is_array($this->copyParams) && ($key = array_search($param, $this->copyParams)) !== false){
                // Remove from copyParams
                unset($this->copyParams[$key]);
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function setPushHistory(): self
    {
        $this->historyBehavior = self::HISTORY_BEHAVIOR_PUSH;
        return $this;
    }

    /**
     * @return $this
     */
    public function setDontPreserveHistory(): self
    {
        $this->historyBehavior = null;
        return $this;
    }

    /**
     * @return $this
     */
    public function setPopHistory(): self
    {
        $this->historyBehavior = self::HISTORY_BEHAVIOR_POP;
        return $this;
    }

    /**
     * @param Path $path
     */
    public function setPath(Path $path): void
    {
        $this->path = $path;
    }

    /**
     * @return Path
     */
    public function getPath(): Path
    {
        return $this->path;
    }

    /**
     * @return bool|string[]
     */
    public function isCopyParams(): array|bool
    {
        return $this->copyParams;
    }

    /**
     * @return string[]
     */
    public function getAddParams(): array
    {
        return $this->addParams;
    }

    /**
     * @return string[]
     */
    public function getRemoveParams(): array
    {
        return $this->removeParams;
    }

    /**
     * @return string
     */
    public function getHistoryBehavior(): ?string
    {
        return $this->historyBehavior;
    }

    /**
     * @param Path|null $path
     * @return Link
     */
    public static function from(Path $path = null): Link
    {
        return new Link($path);
    }

    public static function fromPopHistory(Link|Path|null $fallbackPath = null): Link
    {
        $h = HistoryManager::findSelf();
        return $h->getTopHistory($fallbackPath)->setPopHistory();
    }

    public function pushAndRedirect(): void
    {
        Monolitum::getInstance()->push(new Request_SetRedirectPath($this));
    }

    public function copy(): Link
    {
        $link = new Link($this->path);
        $link->copyParams = $this->copyParams;
        $link->addParams = $this->addParams;
        $link->removeParams = $this->removeParams;
        $link->historyBehavior = $this->historyBehavior;
        return $link;
    }

}
