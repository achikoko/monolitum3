<?php

namespace monolitum\backend\params;

use monolitum\core\Find;
use monolitum\core\Monolitum;

class Path
{

    /**
     * @var string[]
     */
    private array $strings;

    /**
     * @return string[]
     */
    public function getStrings(): array
    {
        return $this->strings;
    }

    /**
     * @param string ...$strings
     * @return Path
     */
    public static function from(string ...$strings): Path
    {
        $path = new Path();
        $path->strings = $strings; // TODO let strings have "/" and ".." and normalize them before setting the to here
        return $path;
    }

    public static function fromRelative(int $back = 0, string ...$strings): Path
    {

        /** @var PathManager $m */
        $m = Find::pushAndGet(PathManager::class);
        $currentPath = $m->getCurrentPathCopy();

        $currentPathSize = sizeof($currentPath);

        if($back > $currentPathSize)
            $back = $currentPathSize;

        array_splice(
                $currentPath,
                $currentPathSize-$back,
                $back,
                $strings
        );

        return self::from(...$currentPath);
    }

    /**
     * @param class-string $class
     * @param string ...$strings
     * @return Path
     */
    public static function fromRelativeToClass(string $class, string ...$strings): Path
    {
        $currentPath = explode('\\', $class);

        array_splice(
            $currentPath,
            count($currentPath)-1,
            1,
            $strings
        );

        return self::from(...$currentPath);
    }

    public function pushAndRedirect(): void
    {
        Monolitum::getInstance()->push(new Request_SetRedirectPath($this));
    }


    public function writePath(bool $encodeUrl = true, string $separator="/"): ?string
    {

        if($this->strings){
            $path = "";
            $first = true;
            foreach ($this->strings as $string) {
                if($first){
                    $first = false;
                }else{
                    $path .= $separator;
                }
                $path .= $encodeUrl ? urlencode($string) : $string;
            }
            return $path;
        }else{
            return null;
        }
    }

    public static function fromUrl(string $url, string $pathSeparator="/"): Path
    {

        if(strlen($url) > 0){
            $newPath = [];
            foreach (explode($pathSeparator, $url) as $value){
                if(strlen($value) > 0){
                    $newPath[] = $value;
                }
            }

            if(count($newPath) > 0){
                return self::from(...$newPath);
            }
        }

        return self::from();
    }


}
