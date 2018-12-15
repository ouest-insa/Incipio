<?php

namespace Mgate\PubliBundle\Manager;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Interface for all services which imports data from an external source.
 *
 * Interface ImporterInterface.
 */
interface FileImporterInterface
{
    /**
     * @return array an array containing expected fields in form. Controller will iterate
     *               that array to display it as a table in form view
     */
    public function expectedFormat();

    /**
     * Process Import.
     *
     * @param UploadedFile $upload resources file contzaining data to import
     *
     * @return mixed
     */
    public function run(UploadedFile $upload);
}
