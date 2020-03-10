<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Publish;

use App\Entity\Personne\Membre;
use App\Entity\Project\Av;
use App\Entity\Project\Etude;
use App\Entity\Project\Mission;
use App\Entity\Project\ProcesVerbal;
use App\Entity\Publish\Document;
use App\Entity\Treso\BV;
use App\Entity\Treso\Facture;
use App\Entity\Treso\NoteDeFrais;
use App\Form\Publish\DocTypeType;
use App\Service\Project\ChartManager;
use App\Service\Project\EtudePermissionChecker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;
use Webmozart\KeyValueStore\Api\KeyValueStore;

class TraitementController extends AbstractController
{
    const DOCTYPE_SUIVI_ETUDE = 'FSE';

    const DOCTYPE_DEVIS = 'DEVIS';

    const DOCTYPE_AVANT_PROJET = 'AP';

    const DOCTYPE_CONVENTION_CLIENT = 'CC';

    const DOCTYPE_CONVENTION_ETUDE = 'CETUDE'; // backward compatibility CETUDE to keep CE (Convention Etudiant) as CE

    const DOCTYPE_FACTURE_ACOMTE = 'FA';

    const DOCTYPE_FACTURE_INTERMEDIAIRE = 'FI';

    const DOCTYPE_FACTURE_SOLDE = 'FS';

    const DOCTYPE_FACTURE_NOT_ETUDE = 'FNE';

    const DOCTYPE_PROCES_VERBAL_INTERMEDIAIRE = 'PVI';

    const DOCTYPE_PROCES_VERBAL_FINAL = 'PVR';

    const DOCTYPE_RECAPITULATIF_MISSION = 'RM';

    const DOCTYPE_DESCRIPTIF_MISSION = 'DM';

    const DOCTYPE_CONVENTION_ETUDIANT = 'CE';

    const DOCTYPE_BULLETIN_ADHESION = 'BA';

    const DOCTYPE_ACCORD_CONFIDENTIALITE = 'AC';

    const DOCTYPE_DECLARATION_ETUDIANT_ETR = 'DEE';

    const DOCTYPE_NOTE_DE_FRAIS = 'NF';

    const DOCTYPE_BULLETIN_DE_VERSEMENT = 'BV';

    const DOCTYPE_AVENANT = 'AV';

    const ROOTNAME_ETUDE = 'etude';

    const ROOTNAME_PROCES_VERBAL = 'pvr';

    const ROOTNAME_ETUDIANT = 'etudiant';

    const ROOTNAME_MISSION = 'mission';

    const ROOTNAME_NOTE_DE_FRAIS = 'nf';

    const ROOTNAME_FACTURE = 'facture';

    const ROOTNAME_BULLETIN_DE_VERSEMENT = 'bv';

    const ROOTNAME_AVENANT = 'av';

    // On considère que les TAG ont déjà été nettoyé du XML
    const REG_REPEAT_LINE = "#(<w:tr(?:(?!w:tr\s).)*?)(\{\%\s*TRfor[^\%]*\%\})(.*?)(\{\%\s*endforTR\s*\%\})(.*?</w:tr>)#";

    const REG_REPEAT_PARAGRAPH = "#(<w:p(?:(?!<w:p\s).)*?)(\{\%\s*Pfor[^\%]*\%\})(.*?)(\{\%\s*endforP\s*\%\})(.*?</w:p>)#";

    // Champs
    const REG_CHECK_FIELDS = "#\{[^\}%]*?[\{%][^\}%]+?[\}%][^\}%]*?\}#";

    const REG_XML_NODE_IDENTIFICATOR = '#<.*?>#';

    // Images
    const REG_IMAGE_DOC = '#<w:drawing.*?/w:drawing>#';

    const REG_IMAGE_DOC_FIELD = '#wp:extent cx="(\\d+)" cy="(\\d+)".*wp:docPr.*descr="(.*?)".*a:blip r:embed="(rId\\d+)#';

    const REG_IMAGE_REL = '#Id="(rId\\d+)" Type="\\S*" Target="media\\/(image\\d+.(jpeg|jpg|png))"#';

    const IMAGE_FIX = '#imageFIX#';

    const IMAGE_VAR = '#imageVAR#';

    // Autres
    const REG_SPECIAL_CHAR = '{}()[]|><?=;!+*-/';

    const REG_FILE_EXT = "#\.(jpg|png|jpeg)#i";

    /**
     * ID du document temporaire venant d'être traité
     */
    private $idDoc;

    /**
     * nom du document traité puis téléchargé
     */
    private $refDoc;

    /**
     * format du fichier (docx ou odt)
     */
    private $format;

    private $chartManager;

    private $permChecker;

    private $twigEnvironment;

    private $keyValueStore;

    private $kernel;

    public function __construct(ChartManager $chartManager, EtudePermissionChecker $permChecker,
                                Environment $twigEnvironment,
                                KeyValueStore $keyValueStore, KernelInterface $kernel)
    {
        $this->chartManager = $chartManager;
        $this->permChecker = $permChecker;
        $this->twigEnvironment = $twigEnvironment;
        $this->keyValueStore = $keyValueStore;
        $this->kernel = $kernel;
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="publish_publiposter", path="/Documents/Publiposter/{templateName}/{rootName}/{rootObject_id}", methods={"GET","HEAD","POST"}, requirements={"rootObject_id": "\d+", "rootName": "\w+", "templateName": "\w+"})
     *
     * @param $templateName
     * @param $rootName
     * @param $rootObject_id
     *
     * @return RedirectResponse|Response
     */
    public function publiposter($templateName, $rootName, $rootObject_id)
    {
        $this->publipostage($templateName, $rootName, $rootObject_id);

        return $this->telecharger();
    }

    private function publipostage($templateName, $rootName, $rootObject_id, $debug = false)
    {
        $em = $this->getDoctrine()->getManager();

        $errorRootObjectNotFound = $this->createNotFoundException('Le document ne peut être publiposté car l\'objet de référence n\'existe pas !');
        $errorEtudeConfidentielle = new AccessDeniedException('Cette étude est confidentielle');

        switch ($rootName) {
            case self::ROOTNAME_ETUDE:
                if (!$rootObject = $em->getRepository(Etude::class)->find($rootObject_id)) {
                    throw $errorRootObjectNotFound;
                }
                if ($this->permChecker->confidentielRefus($rootObject, $this->getUser())) {
                    throw $errorEtudeConfidentielle;
                }
                break;
            case self::ROOTNAME_ETUDIANT:
                if (!$rootObject = $em->getRepository(Membre::class)->find($rootObject_id)) {
                    throw $errorRootObjectNotFound;
                }
                break;
            case self::ROOTNAME_MISSION:
                if (!$rootObject = $em->getRepository(Mission::class)->find($rootObject_id)) {
                    throw $errorRootObjectNotFound;
                }
                break;
            case self::ROOTNAME_FACTURE:
                if (!$rootObject = $em->getRepository(Facture::class)->find($rootObject_id)) {
                    throw $errorRootObjectNotFound;
                }
                if ($rootObject->getEtude() &&
                    $this->permChecker->confidentielRefus($rootObject->getEtude(), $this->getUser())
                ) {
                    throw $errorEtudeConfidentielle;
                }
                break;
            case self::ROOTNAME_NOTE_DE_FRAIS:
                if (!$rootObject = $em->getRepository(NoteDeFrais::class)->find($rootObject_id)) {
                    throw $errorRootObjectNotFound;
                }
                break;
            case self::ROOTNAME_BULLETIN_DE_VERSEMENT:
                if (!$rootObject = $em->getRepository(BV::class)->find($rootObject_id)) {
                    throw $errorRootObjectNotFound;
                }
                if ($rootObject->getMission() && $rootObject->getMission()->getEtude() &&
                    $this->permChecker->confidentielRefus($rootObject->getMission()->getEtude(), $this->getUser())
                ) {
                    throw $errorEtudeConfidentielle;
                }
                break;
            case self::ROOTNAME_PROCES_VERBAL:
                if (!$rootObject = $em->getRepository(ProcesVerbal::class)->find($rootObject_id)) {
                    throw $errorRootObjectNotFound;
                }
                if ($rootObject->getEtude() &&
                    $this->permChecker->confidentielRefus($rootObject->getEtude(), $this->getUser())
                ) {
                    throw $errorEtudeConfidentielle;
                }
                break;
            case self::ROOTNAME_AVENANT:
                if (!$rootObject = $em->getRepository(Av::class)->find($rootObject_id)) {
                    throw $errorRootObjectNotFound;
                }
                if ($rootObject->getEtude() &&
                    $this->permChecker->confidentielRefus($rootObject->getEtude(), $this->getUser())
                ) {
                    throw $errorEtudeConfidentielle;
                }
                break;
            default:
                throw $this->createNotFoundException('Publipostage invalide ! Pas de bol...');
                break;
        }

        $chemin = $this->getDoctypeAbsolutePathFromName($templateName, $debug);
        $tmp = explode(".", $chemin);
        $format = end($tmp);

        $f =  'word/';
        if($this->isOpenDocument($format)){
            $f = '';
        }

        $templatesXMLtraite = $this->traiterTemplates($chemin, $rootName, $rootObject);

        //Si DM on prend la ref de RM et ont remplace RM par DM
        if (self::DOCTYPE_DESCRIPTIF_MISSION == $templateName) {
            $templateName = 'RM';
            $isDM = true;
        }

        if (self::ROOTNAME_ETUDE == $rootName && $rootObject->getReference()) {
            if ($this->keyValueStore->exists('namingConvention')) {
                $namingConvention = $this->keyValueStore->get('namingConvention');
            } else {
                $namingConvention = 'id';
            }
            if (!$debug) {
                //avoid collision with references using / or other characters.
                $refDoc = $rootObject->getReference($namingConvention) . '-' . $templateName . '-';
            } else {
                $refDoc = '';
            }

        } elseif (self::ROOTNAME_ETUDIANT == $rootName) {
            $refDoc = $templateName . '-' . $rootObject->getIdentifiant();
        } elseif (self::ROOTNAME_FACTURE == $rootName) {
            $refDoc = $rootObject->getReference();
        } elseif (self::ROOTNAME_NOTE_DE_FRAIS == $rootName) {
            $refDoc = $rootObject->getReference();
        } elseif (self::ROOTNAME_PROCES_VERBAL == $rootName) {
            $refDoc = $rootObject->getReference();
        } elseif (self::ROOTNAME_AVENANT == $rootName) {
            $refDoc = $templateName . $rootObject->getReference();
        } else {
            $refDoc = $templateName . '-UNREF';
        }
        /*dump($rootName);
        dump($refDoc);
        throw new AccessDeniedException('Pour les tests');*/

        //On remplace DM par RM si DM
        if (isset($isDM) && $isDM) {
            $refDoc = preg_replace('#RM#', 'DM', $refDoc);
        }
        $repertoireTmp = $this->kernel->getProjectDir() . '' . Document::DOCUMENT_TMP_FOLDER; // tmp folder in web directory
        $idDoc = $refDoc . '-' . ((int) strtotime('now') + rand());
        copy($chemin, "$repertoireTmp/$idDoc");
        $zip = new \ZipArchive();
        $zip->open("$repertoireTmp/$idDoc");

        /*
         * TRAITEMENT INSERT IMAGE
         */
        $images = [];
        //Gantt
        if ('AP' == $templateName || (isset($isDM) && $isDM)) {
            $ob = $this->chartManager->getGantt($rootObject, $templateName);
            if ($this->chartManager->exportGantt($ob, $idDoc)) {
                $image = [];
                $image['fileLocation'] = "$repertoireTmp/$idDoc.png";
                $info = getimagesize("$repertoireTmp/$idDoc.png");
                $image['width'] = $info[0];
                $image['height'] = $info[1];
                $images['imageVARganttAP'] = $image;
            }
        }

        //Intégration temporaire.
        if(!$this->isOpenDocument($format)){
            $imagesInDocx = $this->traiterImages($templatesXMLtraite, $images);
            foreach ($imagesInDocx as $image) {
                $zip->deleteName('word/media/' . $image[2]);
                $zip->addFile("$repertoireTmp/$idDoc.png", 'word/media/' . $image[2]);
            }
        }
        /*****/

        $zip = new \ZipArchive();
        $zip->open("$repertoireTmp/$idDoc");

        foreach ($templatesXMLtraite as $templateXMLName => $templateXMLContent) {
            $zip->deleteName($f . $templateXMLName);
            $zip->addFromString($f . $templateXMLName, $templateXMLContent);
        }

        $zip->close();

        $this->idDoc = $idDoc;
        $this->refDoc = $refDoc;
        $this->format = $format;

        return true;
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="publish_telecharger", path="/publi/publiposter/telecharger", methods={"GET","HEAD","POST"})
     */
    public function telecharger()
    {
        $this->purge();
        if (isset($this->idDoc) && isset($this->refDoc) && isset($this->format)) {
            $templateName = $this->kernel->getProjectDir() . '' . Document::DOCUMENT_TMP_FOLDER . '/' . $this->idDoc;

            $response = new Response();
            switch ($this->format){
                case 'odt':
                    $response->headers->set('Content-Type',
                        'application/vnd.oasis.opendocument.text');
                    break;
                case 'ods':
                    $response->headers->set('Content-Type',
                        'application/vnd.oasis.opendocument.spreadsheet');
                    break;
                default:
                    $response->headers->set('Content-Type',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            }
            $response->headers->set('Content-Length', filesize($templateName));
            $response->headers->set('Content-disposition', 'attachment; filename="' . $this->refDoc . '.' . $this->format);
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->headers->set('Expires', 0);

            $response->setContent(file_get_contents($templateName));

            return $response;
        }

        return $this->redirectToRoute('project_etude_homepage', ['page' => 1]);
    }

    private function arrayPushAssoc(&$array, $key, $value)
    {
        $array[$key] = $value;

        return $array;
    }

    private function getDoctypeAbsolutePathFromName($doc, $debug = false)
    {
        $em = $this->getDoctrine()->getManager();

        // Utilisé pour tester un template lors de l'upload d'un nouveau
        if ($debug) {
            return $doc;
        }

        if (!$documenttype = $em->getRepository(Document::class)->findOneBy(['name' => $doc])) {
            throw $this->createNotFoundException('Le doctype ' . $doc . ' n\'existe pas... C\'est bien balo');
        } else {
            $chemin = $this->kernel->getProjectDir() . '' . Document::DOCUMENT_STORAGE_ROOT . '/' . $documenttype->getPath();
        }

        return $chemin;
    }

    /**
     * Prendre tous les fichiers XML dans le document
     * @param $docxFullPath chemin vers le document
     * @return array Association des noms de fichier dans word/ avec leur contenu
     */
    private function getDocContent($docxFullPath)
    {
        $tmp = explode(".", $docxFullPath);
        $format = end($tmp);

        $zip = new \ZipArchive();
        $templateXML = [];
        if (true === $zip->open($docxFullPath)) {
            for ($i = 0; $i < $zip->numFiles; ++$i) {
                $name = $zip->getNameIndex($i);
                if ($this->isOpenDocument($format)) {
                    if (strstr($name, 'content')) {
                        $this->arrayPushAssoc($templateXML, $name, $zip->getFromIndex($i));
                    }
                } else {
                    if ((strstr($name, 'document') || strstr($name, 'header') || strstr($name, 'footer'))
                        && !strstr($name, 'rels')) {
                        $this->arrayPushAssoc($templateXML, str_replace('word/', '', $name), $zip->getFromIndex($i));
                    }
                }
            }
            $zip->close();
        }
        return $templateXML;
    }

    //prendre le fichier relationShip
    private function getDocxRelationShip($docxFullPath)
    {
        $zip = new \ZipArchive();
        $templateXML = [];
        if (true === $zip->open($docxFullPath)) {
            for ($i = 0; $i < $zip->numFiles; ++$i) {
                $name = $zip->getNameIndex($i);
                if ((strstr($name, 'document.xml.rel'))) {
                    $templateXML = $zip->getFromIndex($i);
                }
            }
            $zip->close();
        }

        return $templateXML;
    }

    private function traiterTemplates($templateFullPath, $rootName, $rootObject)
    {
        $templatesXML = $this->getDocContent($templateFullPath); //récup contenu XML
        $templatesXMLTraite = [];

        foreach ($templatesXML as $templateName => $toTemplateItem) {
            $XMLTemplate = $this->twigEnvironment->createTemplate($toTemplateItem);
            $templatedXML = $XMLTemplate->render([$rootName => $rootObject]);
            $this->arrayPushAssoc($templatesXMLTraite, $templateName, $templatedXML);
        }

        return $templatesXMLTraite;
    }

    private function traiterImages(&$templatesXML, $images)
    {
        $allmatches = [];
        foreach ($templatesXML as $key => $templateXML) {
            $i = preg_match_all('#<!--IMAGE\|(.*?)\|\/IMAGE-->#', $templateXML, $matches);
            while ($i--) {
                $splited = preg_split("#\|#", $matches[1][$i]);
                if (isset($images[$splited[0]])) {
                    if (preg_match('#VAR#', $splited[0])) {
                        $cx = $splited[3];
                        $cy = $images[$splited[0]]['height'] * $cx / $images[$splited[0]]['width'];

                        $cx = round($cx);
                        $cy = round($cy);

                        $replacement = [];
                        preg_match("#wp:extent cx=\"$splited[3]\" cy=\"$splited[4]\".*wp:docPr.*a:blip r:embed=\"$splited[1]\".*a:ext cx=\"$splited[3]\" cy=\"$splited[4]\"#",
                            $templateXML, $replacement);
                        $replacement = $replacement[0];
                        $replacement = preg_replace("#cy=\"$splited[4]\"#", "cy=\"$cy\"", $replacement);
                        $templatesXML[$key] = preg_replace("#wp:extent cx=\"$splited[3]\" cy=\"$splited[4]\".*wp:docPr.*a:blip r:embed=\"$splited[1]\".*a:ext cx=\"$splited[3]\" cy=\"$splited[4]\"#",
                            $replacement, $templateXML);
                    }
                }
                array_push($allmatches, $splited);
            }
        }

        return $allmatches;
    }

    //Nettoie le dossier tmp : efface les fichiers temporaires vieux de plus de 1 jours
    private function purge()
    {
        $oldSec = 86400; // = 1 Jours
        clearstatcache();
        $glob = glob('tmp/*');
        if (false !== $glob) {
            foreach ($glob as $filename) {
                if (filemtime($filename) + $oldSec < time()) {
                    unlink($filename);
                }
            }
        }
    }

    /**
     * Traitement des champs (Nettoyage XML).
     */
    private function cleanDocxFields(&$templateXML)
    {
        $fields = [];
        preg_match_all(self::REG_CHECK_FIELDS, $templateXML, $fields);
        $fields = $fields[0];
        foreach ($fields as $field) {
            $originalField = $field;
            $field = preg_replace('#‘#', '\'', $field); // Peut etre simplifier en une ligne avec un array
            $field = preg_replace('#’#', '\'', $field);
            $field = preg_replace('#«#', '"', $field);
            $field = preg_replace('#»#', '"', $field);
            $field = preg_replace(self::REG_XML_NODE_IDENTIFICATOR, '', $field);
            if ($field == strtoupper($field)) {
                $field = strtolower($field);
            }
            $templateXML = preg_replace('#' . addcslashes(addslashes($originalField), self::REG_SPECIAL_CHAR) . '#',
                html_entity_decode($field), $templateXML);
        }

        return $templateXML;
    }

    /**
     * Traitement des lignes de tableaux.
     */
    private function cleanDocxTableRow(&$templateXML)
    {
        $parts = [];
        $nbr = preg_match_all(self::REG_REPEAT_LINE, $templateXML, $parts);
        $datas = [];
        foreach ($parts as $part) {
            for ($i = 0; $i < $nbr; ++$i) {
                $datas[$i][] = $part[$i];
            }
        }

        foreach ($datas as $data) {
            $forStart = $data[2];
            $forEnd = $data[4];

            $body = preg_replace([
                '#' . addcslashes(addslashes($forStart), self::REG_SPECIAL_CHAR) . '#',
                '#' . addcslashes(addslashes($forEnd), self::REG_SPECIAL_CHAR) . '#',
            ], '', $data[0]);

            $templateXML = preg_replace('#' . addcslashes(addslashes($data[0]), self::REG_SPECIAL_CHAR) . '#',
                preg_replace('#TRfor#', 'for', $forStart) . $body . '{% endfor %}', $templateXML);
        }

        return $templateXML;
    }

    /**
     * Traitement Paragraphe.
     */
    private function cleanDocxParagraph(&$templateXML)
    {
        $parts = [];
        $nbr = preg_match_all(self::REG_REPEAT_PARAGRAPH, $templateXML, $parts);
        $datas = [];
        foreach ($parts as $part) {
            for ($i = 0; $i < $nbr; ++$i) {
                $datas[$i][] = $part[$i];
            }
        }

        foreach ($datas as $data) {
            $forStart = $data[2];
            $forEnd = $data[4];

            $body = preg_replace([
                '#' . addcslashes(addslashes($forStart), self::REG_SPECIAL_CHAR) . '#',
                '#' . addcslashes(addslashes($forEnd), self::REG_SPECIAL_CHAR) . '#',
            ], '', $data[0]);

            $templateXML = preg_replace('#' . addcslashes(addslashes($data[0]), self::REG_SPECIAL_CHAR) . '#',
                preg_replace('#Pfor#', 'for', $forStart) . $body . '{% endfor %}', $templateXML);
        }

        return $templateXML;
    }

    /**
     * Traitement des images.
     */
    private function linkDocxImages(&$templateXML, $relationship)
    {
        $images = [];
        preg_match(self::REG_IMAGE_DOC, $templateXML, $images);

        foreach ($images as $image) {
            $imageInfo = [];
            if (preg_match(self::REG_IMAGE_DOC_FIELD, $image, $imageInfo)) {
                $cx = $imageInfo[1];
                $cy = $imageInfo[2];
                $fileName = explode('\\', $imageInfo[3]);
                $originalFilename = preg_replace(self::REG_FILE_EXT, '', end($fileName));
                $rId = $imageInfo[4];

                if (preg_match(self::IMAGE_VAR, $originalFilename) || preg_match(self::IMAGE_VAR, $originalFilename)) {
                    $relatedImage = [];
                    preg_match(self::REG_IMAGE_REL, $relationship, $relatedImage);
                    $localFilename = $relatedImage[2];

                    $commentsRel = '<!--IMAGE|' . $originalFilename . '|' . $rId . '|' . $localFilename . '|' . $cx . '|' . $cy . '|/IMAGE-->';
                    $templateXML = preg_replace("#(<\?.*?\?>)#", "$0$commentsRel", $templateXML, 1);
                }
            }
        }

        return $templateXML;
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="publish_documenttype_upload", path="/DocumentsType/Upload", methods={"GET","HEAD","POST"})
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function uploadNewDoctype(Request $request)
    {
        $data = [];
        $form = $this->createForm(DocTypeType::class, $data);
        $session = $request->getSession();

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                // Création d'un fichier temporaire
                $file = $data['template'];
                $filename = sha1(uniqid(mt_rand(), true));
                $filename .= '.' . $file->guessExtension();
                $file->move('tmp/', $filename);
                $docFullPath = 'tmp/' . $filename;

                // Extraction des infos XML
                $templatesXML = $this->getDocContent($docFullPath);
                $relationship = $this->getDocxRelationShip($docFullPath);
                // Nettoyage des XML
                $templatesXMLTraite = [];
                foreach ($templatesXML as $templateName => $templateXML) {
                    $this->cleanDocxFields($templateXML);
                    $this->cleanDocxTableRow($templateXML);
                    $this->cleanDocxParagraph($templateXML);
                    $this->linkDocxImages($templateXML, $relationship);
                    $this->arrayPushAssoc($templatesXMLTraite, $templateName, $templateXML);
                }

                // Enregistrement dans le fichier temporaire
                $zip = new \ZipArchive();
                $zip->open($docFullPath);

                $f =  'word/';
                $tmp = explode(".", $filename);
                if($this->isOpenDocument(end($tmp))){
                    $f = '';
                }
                foreach ($templatesXMLTraite as $templateXMLName => $templateXMLContent) {
                    $zip->deleteName($f . $templateXMLName);
                    $zip->addFromString($f . $templateXMLName, $templateXMLContent);
                }
                $zip->close();

                if (array_key_exists('etude', $data)) {
                    $etude = $data['etude'];
                } else {
                    $etude = null;
                }
                // Vérification du template (document étude)
                if ($etude && (self::DOCTYPE_AVANT_PROJET == $data['name'] ||
                        self::DOCTYPE_CONVENTION_CLIENT == $data['name'] ||
                        self::DOCTYPE_CONVENTION_ETUDE == $data['name'] ||
                        self::DOCTYPE_SUIVI_ETUDE == $data['name']) &&
                    $data['verification'] && $this->publipostage($docFullPath, self::ROOTNAME_ETUDE, $etude->getId(),
                        true)
                ) {
                    $session->getFlashBag()->add('success', 'Le template a été vérifié, il ne contient pas d\'erreur');
                }

                if (array_key_exists('etudiant', $data)) {
                    $etudiant = $data['etudiant'];
                } else {
                    $etudiant = null;
                }

                $etudiant = $data['etudiant'];
                // Vérification du template (document étudiant)
                if ($etudiant && (self::DOCTYPE_CONVENTION_ETUDIANT == $data['name'] ||
                        self::DOCTYPE_DECLARATION_ETUDIANT_ETR == $data['name']) &&
                    $data['verification'] && $this->publipostage($docFullPath, self::ROOTNAME_ETUDIANT,
                        $etudiant->getId(), true)
                ) {
                    $session->getFlashBag()->add('success', 'Le template a été vérifié, il ne contient pas d\'erreur');
                }

                // Enregistrement du template
                $em = $this->getDoctrine()->getManager();
                $user = $this->getUser();
                $personne = $user->getPersonne();
                $file = new File($docFullPath);

                $doc = new Document();
                $doc->setAuthor($personne)
                    ->setName($data['name'])
                    ->setFile($file);
                $doc->setProjectDir($this->kernel->getProjectDir());
                $em->persist($doc);
                $docs = $em->getRepository(Document::class)->findBy(['name' => $doc->getName()]);
                foreach ($docs as $doc) {
                    $doc->setProjectDir($this->kernel->getProjectDir());
                    $em->remove($doc);
                }
                $em->flush();

                $session->getFlashBag()->add('success', 'Le document a été mis à jour');

                return $this->redirectToRoute('publish_documenttype_upload');
            }
        }

        return $this->render('Publish/DocType/upload.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @param $format format du fichier
     * @return bool le document est de type OpenDocument
     */
    private function isOpenDocument($format){
        return $format === 'odt' || $format === 'ods';
    }
}
