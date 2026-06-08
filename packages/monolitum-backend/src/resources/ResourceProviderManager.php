<?php

namespace monolitum\backend\resources;

use Closure;
use monolitum\backend\params\Path;
use monolitum\backend\params\ValidatedValueGetter;
use monolitum\core\MNode;
use monolitum\core\panic\DevPanic;
use monolitum\core\util\ResourceAddressResolver;
use function monolitum\core\m;

class ResourceProviderManager extends MNode
{

    private ValidatedValueGetter $readResourceParam;

    private ?ResourceAddressResolver $resourceAddressResolver = null;

    /**
     * @var array<string, AllowedExtension>
     */
    private array $allowedExtensions = [];

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    /**
     * @param ResourceAddressResolver|null $resourceAddressResolver
     */
    public function setResourceAddressResolver(?ResourceAddressResolver $resourceAddressResolver): void
    {
        $this->resourceAddressResolver = $resourceAddressResolver;
    }

    public function setReadResourceParam(ValidatedValueGetter $readResourceParam): void
    {
        $this->readResourceParam = $readResourceParam;
    }

    /**
     * @param string[] $extensions
     */
    public function addAllowedExtensions(array $extensions, AllowedExtension $allowedExtension = null): void
    {
        foreach ($extensions as $extension){
            $this->allowedExtensions[$extension] = $allowedExtension !== null ? $allowedExtension : new AllowedExtension();
        }
    }

    public function addAllowedExtension(string $extension, AllowedExtension $allowedExtension): void
    {
        $this->allowedExtensions[$extension] = $allowedExtension;
    }

    protected function onAfterBuild(): void
    {

        $validatedValue = $this->readResourceParam->getValidatedValue();
        if($validatedValue->isValid() && !$validatedValue->isNull()){

            $filePath = Path::fromUrl($validatedValue->getValue());
            $pathStrings = $filePath->getStrings();

            $len = count($pathStrings);
            if($len > 0){
                $fileName = $pathStrings[$len-1];

                $fileAllowedExtension = null;
                foreach ($this->allowedExtensions as $string => $allowedExtension){
                    if($this->endsWith($fileName, $string)){
                        $fileAllowedExtension = $allowedExtension;
                        break;
                    }
                }

                if($fileAllowedExtension === null)
                    throw new DevPanic("Resource not found.");

                $resolvedUrl = $filePath->writePath(false);
                $fileName1 = $this->resourceAddressResolver?->resolve($resolvedUrl);

                if($fileName1 === null)
                    throw new ResourceNotFoundPanic("Illegal url.");

                M(new Request_DownloadFile(
                    Path::fromUrl($fileName1),
                    $fileAllowedExtension->mimeType,
                    $fileAllowedExtension->writer ?? new StdoutFileWriter()
                ));

            }


        }else{
            throw new ResourceNotFoundPanic("Resource not found.");
        }

//        parent::afterBuildNode();
    }

    function endsWith( $haystack, $needle ): bool
    {
        $length = strlen( $needle );
        if( !$length ) {
            return true;
        }
        return substr( $haystack, -$length ) === $needle;
    }

}
