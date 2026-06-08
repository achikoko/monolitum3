<?php

namespace monolitum\backend\resources;

use monolitum\backend\params\Path;
use monolitum\core\MNode;
use monolitum\core\Monolitum;
use monolitum\core\util\ResourceAddressResolver;

class StdoutFileWriter_CSSUrlSubstitution extends StdoutFileWriter
{

    public function __construct(private ?ResourceAddressResolver $resourceAddressResolver = null)
    {

    }

    public function write(MNode $caller, Path $fullPath, ?string $mimeType): void
    {
        $fileToOpen = $fullPath->writePath(false);

        $handle = fopen($fileToOpen, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                // process the line read.
                echo preg_replace_callback(

                    '/url\((["\']?)(((?!(:\/\/)|"|\'|\)).)*)(["\']?)\)/',
                    function ($matches) use ($caller, $fullPath) {

                        $matchedString = $matches[2];

                        if(str_starts_with($matchedString, "data:")){
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
                                $currentPathStrings = $fullPath->getStrings();
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

                            if($this->resourceAddressResolver !== null){
                                $stringUrl = Path::from(...$currentPathStrings)?->writePath();
                                $stringUrl = $this->resourceAddressResolver->resolve($stringUrl, backwards: true);
                                $pathToResolver = Path::fromUrl($stringUrl);
                            }else{
                                $pathToResolver = Path::from(...$currentPathStrings);
                            }

                            // Danger: This is an execute time code, it should not fail, but if it fails, Monolitum breaks
                            $request = new Request_ResResolver($pathToResolver);
                            $request->setEncodeUrl(false);
                            Monolitum::getInstance()->pushFrom($request, $caller);
                            $resolvedString = $request->getResResolver()->resolve();


                            return 'url(' . $matches[1] . $resolvedString . $matches[5] . ')';
                        }

                    },
                    $line
                );
            }

            fclose($handle);
        }

    }

}
