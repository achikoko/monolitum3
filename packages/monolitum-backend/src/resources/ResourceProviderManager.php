<?php

namespace monolitum\backend\resources;

use Closure;
use Exception;
use monolitum\backend\params\Path;
use monolitum\backend\params\ValidatedValueGetter;
use monolitum\core\MNode;
use monolitum\core\Monolitum;
use monolitum\core\panic\DevPanic;

class ResourceProviderManager extends MNode
{

    private ValidatedValueGetter $readResourceParam;

    /**
     * @var array<string, AllowedExtension>
     */
    private array $allowedExtensions = [];

    private Path $filePath;

    private string|false|null $fileMime;

    private string|false $fileContents;

    private ?string $fileName;

    private int|false $fileLastModified;

    private string $fileLastModifiedString;

    private bool $notModifiedFlag = false;

    private ?AllowedExtension $fileAllowedExtension = null;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    public function setReadResourceParam(ValidatedValueGetter $readResourceParam): void
    {
        $this->readResourceParam = $readResourceParam;
    }

    /**
     * @param string[] $allowedExtensions
     */
    public function addAllowedExtensions(array $allowedExtensions): void
    {
        foreach ($allowedExtensions as $allowedExtension){
            $this->allowedExtensions[$allowedExtension] = new AllowedExtension();
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

            $this->filePath = Path::fromUrl($validatedValue->getValue());
            $pathStrings = $this->filePath->getStrings();

            $len = count($pathStrings);
            if($len > 0){
                $fileName = $pathStrings[$len-1];

                foreach ($this->allowedExtensions as $string => $allowedExtension){
                    if($this->endsWith($fileName, $string)){
                        $this->fileAllowedExtension = $allowedExtension;
                        break;
                    }
                }

                if($this->fileAllowedExtension === null)
                    throw new DevPanic("Resource not found.");

                $this->fileAllowedExtension->prepare($this);

                $resolvedUrl = $this->filePath->writePath(false);
                $this->fileName = Monolitum::getInstance()->getResourcesAddressResolver()->resolve($resolvedUrl);

                if($this->fileName === null)
                    throw new ResourceNotFoundPanic("Illegal url.");

                try{

                    $this->fileMime = $this->fileAllowedExtension->getMimeType();
                    if($this->fileMime === null)
                        $this->fileMime = mime_content_type($this->fileName);

                    $this->fileLastModified = filemtime($this->fileName);

                    if($this->fileLastModified == null)
                        throw new ResourceNotFoundPanic("Resource not found.");

                    $this->fileLastModifiedString = gmdate('D, d M Y H:i:s ',  $this->fileLastModified) . 'GMT';

                    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                        //echo 'set modified header';
                        if(intval($this->fileLastModified) >= intval(strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']))) {
                            $this->notModifiedFlag = true;
                        }
                    }

                }catch (Exception $e){
                    throw new ResourceNotFoundPanic("Exception.");
                }

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

    public function doExecute(): void
    {
        $etag = md5($this->fileLastModified);

        header("Last-Modified: $this->fileLastModifiedString");
        header("ETag: \"{$etag}\"");

        // Expires must be set, because browser flashes when a css is request and it's load after html.
        $expires = gmdate('D, d M Y H:i:s ', time() + 3600 * 24) . 'GMT';
        header("Expires: $expires");

        if ($this->notModifiedFlag) {
            header('HTTP/1.1 304 Not Modified');
            return;
        }

        $mimeType = $this->fileAllowedExtension->getMimeType();

        if ($mimeType !== null) {
            header('Content-Type: ' . $mimeType);
        } else if ($this->fileMime) {
            header('Content-Type: ' . $this->fileMime);
        }
        //echo $this->fileContents;

        $rewriter = $this->fileAllowedExtension->getRewriter($this->filePath);

        if($rewriter === null){
            readfile($this->fileName);
        }else if($this->fileAllowedExtension->readLineByLine()){

            $handle = fopen($this->fileName, "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    // process the line read.
                    echo $rewriter($line);
                }

                fclose($handle);
            }

        }else{
            echo $rewriter(file_get_contents($this->fileName));
        }

        parent::doExecute();
    }

}
