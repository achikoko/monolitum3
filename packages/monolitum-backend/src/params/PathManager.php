<?php

namespace monolitum\backend\params;

use Closure;
use monolitum\core\MNode;
use monolitum\core\MObject;
use monolitum\core\Monolitum;
use monolitum\model\ValidatedValue;

class PathManager extends MNode
{

    private string|false $writeAsParam = false;

    private array $currentPath = [];

    private int $nextIdx = 0;

    public function __construct(
        private readonly ValidatedValueGetter $readPathParam,
        private readonly string $baseUrl,
        ?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    public function setWriteAsParam(string $paramName): PathManager
    {
        $this->writeAsParam = $paramName;
        return $this;
    }

    public function getCurrentPathCopy(): array
    {
        return array_slice($this->currentPath, 0, $this->nextIdx);
    }

    public function getPathTopValidatedValue(bool $shift): ValidatedValue
    {
        if($shift){

            if(count($this->currentPath) > $this->nextIdx){
                $strValue = $this->currentPath[$this->nextIdx];
                $this->nextIdx++;
                return $this->validatedValueFromStr(Abstract_Request_ValidatedValue::TYPE_STRING, $strValue);
            }else{
                return new ValidatedValue(false);
            }

        }else{

            if($this->nextIdx == 0){
                return new ValidatedValue(false);
            }else if($this->nextIdx > 0 && count($this->currentPath) >= $this->nextIdx){
                $strValue = $this->currentPath[$this->nextIdx-1];
                return $this->validatedValueFromStr(Abstract_Request_ValidatedValue::TYPE_STRING, $strValue);
            }else{
                return new ValidatedValue(false);
            }

        }
    }

    protected function onBuild(): void
    {
        $validatedPath = $this->readPathParam->getValidatedValue();
        if($validatedPath->isValid() && !$validatedPath->isNull()){

            /** @var string $path */
            $path = $validatedPath->getValue();

            if(strlen($path) > 0){
                $this->currentPath = explode("/", $path);
            }

        }

        parent::onBuild();
    }

    public function doReceive(MObject $object): bool
    {
        if($object instanceof Request_PathTop_ValidatedValue){
            $object->setValidatedValue($this->getPathTopValidatedValue($object->shift));
            return true;
        }else if($object instanceof Request_MakeUrlString) {

            $writeAsParam = $object->getWriteAsParam();
            if($writeAsParam === null)
                $writeAsParam = $this->writeAsParam;

            $isAppendUrlPrefix = $object->isAppendUrlPrefix();
            $priorityParamsAlone = [];

            if($object->link instanceof Path){
                $path = $object->link;
            }else{
                /** @var Path $url */
                $path = $object->link->getPath();
            }

            $url = "";
            $querySign = false;

            if($isAppendUrlPrefix)
                $url .= $this->baseUrl;

            $stringPath = $path?->writePath();
            if($stringPath != null){
                if($writeAsParam){
                    if($object->obtainParamsAlone){
                        $priorityParamsAlone[$writeAsParam] = $stringPath;
                    }else{
                        $url .= '/?' . $writeAsParam . "=" . urlencode($stringPath);
                        $querySign = true;
                    }
                }else{
                    $url .= '/' . $stringPath;
                }
            }else{
                $url .= '/';
            }

            $paramsAlone = [];
            if($object->link instanceof Link){

                $copy = $object->link->isCopyParams();

                if($copy !== false){

                    $activeGetParams = new Request_FindParameters(
                        Request_FindParameters::CATEGORY_GET,
                        is_bool($copy) ? null : $copy,
                        $object->link->getRemoveParams()
                    );
                    Monolitum::getInstance()->push($activeGetParams);
                    $paramsAlone = $activeGetParams->getFoundParams();

                }

                foreach($object->link->getRemoveParams() as $key => $value){
                    unset($paramsAlone[$key]);
                }

                foreach($object->link->getAddParams() as $key => $value){
                    $paramsAlone[$key] = $value;
                }

                if($object->obtainParamsAlone){
                    foreach ($priorityParamsAlone as $key => $value) {
                        $paramsAlone[$key] = $value;
                    }
                }else{

                    foreach ($paramsAlone as $key => $value){
                        if($key === $writeAsParam || $value === null)
                            continue;

                        if(!$querySign){
                            $url .= '/?';
                            $querySign = true;
                        }else{
                            $url .= '&';
                        }
                        $url .= urlencode($key);
                        $url .= "=";
                        $url .= urlencode($value);
                    }

                }

            }

            // TODO unique changing key?

            $object->setUrl($url);
            if($object->obtainParamsAlone)
                $object->setAloneParamValues($paramsAlone);

            return true;
        }else if($object instanceof Request_CurrentPath) {

            $currentLength = count($this->currentPath) - $object->backParentsToStrip;

            if($currentLength > 0){
                $object->path = Path::from(...array_slice($this->currentPath, 0, $currentLength));
            }

            return true;
        }

        return parent::doReceive($object);
    }

    /**
     * @param array<string, string> $params
     * @return string
     */
    public static function encodeParams(array $params): string
    {

        $first = true;
        $url = "";

        foreach ($params as $key => $value){

            if($first)
                $first = false;
            else
                $url .= '&';
            $url .= urlencode($key);
            $url .= "=";
            $url .= urlencode($value);
        }

        return $url;

    }

    /**
     * @param string $activeType
     * @param string $strValue
     * @return ValidatedValue
     */
    private function validatedValueFromStr(string $activeType, string $strValue): ValidatedValue
    {

        switch ($activeType){
            case Abstract_Request_ValidatedValue::TYPE_STRING:
                return new ValidatedValue(true, true, $strValue, null, $strValue);
            case Abstract_Request_ValidatedValue::TYPE_INT:
                // TODO Dangerous code, it will parse anything. If it fails, a 0 is returned.
                // Better use https://hashids.org/php/ instead of ids
                $intValue = intval($strValue);
                return new ValidatedValue(true, true, $intValue, null, $strValue);
            default:
                return new ValidatedValue(false);
        }
    }

}
