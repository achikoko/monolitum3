<?php

namespace monolitum\backend\files;

use Closure;
use DateTime;
use monolitum\backend\params\Path;
use monolitum\backend\resources\Request_DownloadFile;
use monolitum\core\MNode;
use monolitum\database\Query;
use monolitum\model\EntitiesManager;
use monolitum\model\Entity;
use monolitum\model\values\File;
use function monolitum\core\m;

class FileUploadManager extends MNode
{

    private Path $basepath;
    private FileUploadDatabaseModel $fileUploadDatabaseModel;

    function __construct(Closure $builder)
    {
        parent::__construct($builder);
    }

    /**
     * @param Path $basepath
     */
    public function setBasepath(Path $basepath): void
    {
        $this->basepath = $basepath;
    }

    /**
     * @param FileUploadDatabaseModel $fileUploadDatabaseModel
     */
    public function setFileUploadDatabaseModel(FileUploadDatabaseModel $fileUploadDatabaseModel): void
    {
        $this->fileUploadDatabaseModel = $fileUploadDatabaseModel;
    }

    public function uploadFile(File $file, string $category): ?Entity
    {

        // File type is trusted (obviously)
        if($file->type === null){
            return null; // TODO throw exception
        }

        $computedExtension = $this->getExtension($file->type);

        if(empty($computedExtension)){
            $newname = date("Ymd_Hisu") . '__' . sha1_file($file->path);
        }else{
            $newname = date("Ymd_Hisu") . '__' . sha1_file($file->path) . $computedExtension;
        }


        $folder = "./" . $this->basepath->extend($category)->writePath(false);

//        var_dump("./" . $folder, realpath("./uploads"), realpath($folder));

        if (!is_dir($folder)) {
            if(!mkdir($folder, 0755, true))
                return null; // TODO throw exception
        }

        if(!move_uploaded_file($file->path, $folder . '/' . $newname)){
            return null; // TODO throw exception
        }

        $uploadedFile = EntitiesManager::findSelf()->instance($this->fileUploadDatabaseModel->model, forInsert: true);

        $uploadedFile->setString($this->fileUploadDatabaseModel->name, $file->name);
        $uploadedFile->setString($this->fileUploadDatabaseModel->type, $file->type);
        $uploadedFile->setString($this->fileUploadDatabaseModel->size, $file->size);
        $uploadedFile->setString($this->fileUploadDatabaseModel->category, $category);

        $uploadedFile->setString($this->fileUploadDatabaseModel->fileName, $newname);

        if($this->fileUploadDatabaseModel->uploadTimestamp !== null){
            $uploadedFile->setDateTime($this->fileUploadDatabaseModel->uploadTimestamp, new DateTime());
        }

        $newId = $uploadedFile->insert();
        $uploadedFile->setInt($this->fileUploadDatabaseModel->id, $newId);

        return $uploadedFile;

        // TODO https://www.educative.io/answers/what-is-php-files-constant
        // TODO https://www.php.net/manual/en/features.file-upload.php
        // TODO https://www.php.net/manual/en/function.move-uploaded-file.php


    }

    public function downloadFileEntity(Entity $entity): void
    {
        M(new Request_DownloadFile(
            $this->basepath->extend(
                $entity->getString($this->fileUploadDatabaseModel->category),
                $entity->getString($this->fileUploadDatabaseModel->name)
            ),
            $entity->getString($this->fileUploadDatabaseModel->type)
        ));
    }

    public function removeFileEntity(Entity $entity, bool $asWellFromFileSystem = true): bool
    {
        $category = $entity->getString($this->fileUploadDatabaseModel->category);
        $name = $entity->getString($this->fileUploadDatabaseModel->fileName);

        $folder = $this->basepath->extend($category)->writePath(false);

        $fullPath = $folder . '/' . $name;

        if(!file_exists($fullPath)){
            return false;
        }

        if($this->fileUploadDatabaseModel->deleteTimestamp !== null){
            $entity->setDateTime($this->fileUploadDatabaseModel->deleteTimestamp, new DateTime());
            $entity->update();
        }else{
            $entity->delete();
        }

        if($asWellFromFileSystem) {
            if (!unlink($fullPath)) {
                $entity->insert();
                return false;
            }
        }

        return true;
    }

    function getExtension($mime): false|string
    {
        if(empty($mime)) return false;
        switch($mime)
        {
            case 'image/bmp': return '.bmp';
            case 'image/cis-cod': return '.cod';
            case 'image/gif': return '.gif';
            case 'image/ief': return '.ief';
            case 'image/jpeg': return '.jpg';
            case 'image/pipeg': return '.jfif';
            case 'image/tiff': return '.tif';
            case 'image/x-cmu-raster': return '.ras';
            case 'image/x-cmx': return '.cmx';
            case 'image/x-icon': return '.ico';
            case 'image/x-portable-anymap': return '.pnm';
            case 'image/x-portable-bitmap': return '.pbm';
            case 'image/x-portable-graymap': return '.pgm';
            case 'image/x-portable-pixmap': return '.ppm';
            case 'image/x-rgb': return '.rgb';
            case 'image/x-xbitmap': return '.xbm';
            case 'image/x-xpixmap': return '.xpm';
            case 'image/x-xwindowdump': return '.xwd';
            case 'image/png': return '.png';
            case 'image/x-jps': return '.jps';
            case 'image/x-freehand': return '.fh';
            case 'application/pdf': return '.pdf';
            default: return false;
        }
    }

    public function findFileById(int $fileId, $writable = false): ?Entity
    {
        return Query::newQuery($this->fileUploadDatabaseModel->model)->filter([
            $this->fileUploadDatabaseModel->id => $fileId,
            $this->fileUploadDatabaseModel->deleteTimestamp => null,
        ])->forUpdate($writable)->execute()->firstAndClose();
    }

}
