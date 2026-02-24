<?php

namespace monolitum\backend\resources;

use monolitum\backend\params\Path;
use monolitum\core\Monolitum;

/**
 * Matches the pattern "url(...)" to replace the content with the return of the resource resolver available.
 *
 * Typically used in .css files when refer to other files such as font files.
 */
class AllowedExtension_RewriteLocalPaths extends AllowedExtension
{

    public function readLineByLine(): true
    {
        return true;
    }

    private function startsWith(string $string, string $prefix): bool
    {
        $len = strlen($prefix);
        return (substr($string, 0, $len) === $prefix);
    }

    /**
     * @param Path $path
     * @return callable|null
     */
    public function getRewriter(Path $path): ?callable
    {
        return function ($line) use ($path) {

            return preg_replace_callback(

                    '/url\((["\']?)(((?!(:\/\/)|"|\'|\)).)*)(["\']?)\)/',
                    function ($matches) use ($path) {

                        $matchedString = $matches[2];

                        if($this->startsWith($matchedString, "data:")){
                            // Do nothing
                            return 'url(' . $matchedString . ')';
                        }else{

                            if(!$matches[1]){
                                $matches[1] = '"';
                                $matches[5] = '"';
                            }

                            $matchedStringSplitBySlash = preg_split('/\//', $matchedString);

                            if($matchedStringSplitBySlash[0] === ""){
                                $currentPathStrings = [];
                            }else{
                                $currentPathStrings = $path->getStrings();
                                // Remove the file name
                                array_pop($currentPathStrings);
                            }

                            foreach ($matchedStringSplitBySlash as $s){
                                if($s !== ""){
                                    if($s === ".."){
                                        if(count($currentPathStrings) > 0)
                                            array_pop($currentPathStrings);
                                    }else{
                                        $currentPathStrings[] = $s;
                                    }
                                }
                            }

                            $active = new Request_ResResolver(Path::from(...$currentPathStrings));
                            $active->setEncodeUrl(false);
                            Monolitum::getInstance()->pushFrom($active, $this->getManager());
                            $resolvedString = $active->getResResolver()->resolve();

                            return 'url(' . $matches[1] . $resolvedString . $matches[5] . ')';
                        }

                    },
                $line
            );

            /*$htmlString = preg_replace_callback_array(
                [
                    '/(href="?)(\S+)("?)/i' => function (&$matches) {
                        return $matches[1] . urldecode($matches[2]) . $matches[3];
                    },
                    '/(href="?\S+)(%24)(\S+)?"?/i' => function (&$matches) {
                        return urldecode($matches[1] . '$' . $matches[3]);
                    }
                ],
                $htmlString
            );*/


        };
    }

}
