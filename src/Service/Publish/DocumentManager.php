<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @brief         Document Manager
 *
 * @author        Florian Lefèvre
 * @date          21 avril 2014
 *
 * @copyright (c) 2014, Florian Lefèvre
 *
 * Manager pour l'upload de Documents (Aucun document ne doit être persisté sans utiliser ces méthodes)
 */

namespace App\Service\Publish;

use App\Entity\Publish\Document;
use App\Entity\Publish\RelatedDocument;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DocumentManager
{
    protected $em;

    protected $tokenStorage;

    protected $kernel;

    protected $junior_authorizedStorageSize;

    protected $junior_id;

    /**
     * @param ObjectManager         $em
     * @param                       $juniorId
     * @param                       $authorizedStorageSize
     * @param TokenStorageInterface $tokenStorage
     * @param KernelInterface       $kernel
     */
    public function __construct(ObjectManager $em, $juniorId, $authorizedStorageSize, TokenStorageInterface $tokenStorage,
                                KernelInterface $kernel)
    {
        $this->em = $em;
        $this->junior_id = $juniorId;
        $this->junior_authorizedStorageSize = $authorizedStorageSize ?? 512000;
        $this->tokenStorage = $tokenStorage;
        $this->kernel = $kernel;
    }

    private function persistAndFlush($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

    /**
     * Upload un document sur le serveur depuis une ressource distante via HTTP.
     *
     * @param string $url
     * @param array  $authorizedMIMEType
     * @param string $name
     * @param string $relatedDocument
     * @param bool   $deleteIfExist
     *
     * @return Document
     *
     * @throws \Exception
     */
    public function uploadDocumentFromUrl($url, array $authorizedMIMEType, $name, $relatedDocument = null,
                                          $deleteIfExist = false)
    {
        $tempStorage = 'tmp/' . sha1(uniqid(mt_rand(), true));

        if (false === ($handle = @fopen($url, 'r'))) { // Erreur
            throw new \Exception('La ressource demandée ne peut être lue.');
        }

        file_put_contents($tempStorage, $handle);
        fclose($handle);
        // MIME-type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tempStorage);
        $extension = substr(strrchr($mime, '\\'), 1);

        // le dernier true indique de ne pas vérifier si le fichier à été téléchargé en HTTP
        $file = new UploadedFile($tempStorage, $name . '.' . $extension, $mime, filesize($tempStorage), null, true);

        return $this->uploadDocumentFromFile($file, $authorizedMIMEType, $name, $relatedDocument, $deleteIfExist);
    }

    /**
     * Upload un fichier de type UploadedFile sur le serveur.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param array                                               $authorizedMIMEType
     * @param string                                              $name
     * @param RelatedDocument                                     $relatedDocument
     * @param bool                                                $deleteIfExist
     *
     * @return Document
     *
     * @throws \Exception
     */
    public function uploadDocumentFromFile(UploadedFile $file, array $authorizedMIMEType, $name,
                                           RelatedDocument $relatedDocument = null, $deleteIfExist = false)
    {
        $document = new Document();

        // MIME-type Check
        if (!in_array($file->getMimeType(), $authorizedMIMEType)) { // Erreur
            throw new \Exception('Le type de fichier n\'est pas autorisé.');
        }

        // Author
        $user = $this->tokenStorage->getToken()->getUser();
        $personne = $user->getPersonne();
        $document->setAuthor($personne);

        // File
        $document->setFile($file);
        $document->setName($name);

        return $this->uploadDocument($document, $relatedDocument, $deleteIfExist);
    }

    /**
     * uploadDocument has to be the only one fonction used to persist Document.
     *
     * @param Document        $document
     * @param RelatedDocument $relatedDocument
     * @param bool            $deleteIfExist
     *
     * @return Document
     *
     * @throws \Exception
     * @throws UploadException
     */
    public function uploadDocument(Document $document, RelatedDocument $relatedDocument = null, $deleteIfExist = false)
    {
        // Relations
        if ($relatedDocument) {
            $document->setRelation($relatedDocument);
            $relatedDocument->setDocument($document);
        }

        // Store each Junior documents in a distinct subdirectory
        $juniorId = $this->junior_id;
        $document->setSubdirectory($juniorId);
        $document->setProjectDir($this->kernel->getProjectDir());

        // Authorized Storage Size Overflow
        $totalSize = $document->getSize() + $this->em->getRepository(Document::class)->getTotalSize();
        if ($totalSize > $this->junior_authorizedStorageSize) {
            throw new UploadException('Vous n\'avez plus d\'espace disponible ! Vous pouvez en demander plus à dsi@n7consulting.fr.');
        }

        // Delete every document with the same name
        if ($deleteIfExist) {
            $docs = $this->em->getRepository(Document::class)->findBy(['name' => $document->getName()]);
            foreach ($docs as $doc) {
                if ($doc->getRelation()) {
                    $relation = $doc->getRelation();
                    $doc->setRelation();
                    $this->em->remove($relation);
                }
                $this->em->remove($doc);
            }
            //persistence de tout à la fin des actions.
            $this->em->flush();
        }
        $this->persistAndFlush($document);

        return $document;
    }
}
