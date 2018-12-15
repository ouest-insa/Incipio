<?php

namespace Mgate\SuiviBundle\Manager;

use Mgate\PersonneBundle\Entity\Membre;
use Webmozart\KeyValueStore\Api\KeyValueStore;

class EmailEtuManager
{
    private $emailEtu;

    private $emailAncien;

    public function __construct(KeyValueStore $keyValueStore)
    {
        $this->emailEtu = $keyValueStore->exists('domaineEmailEtu') ? '@' . $keyValueStore->get('domaineEmailEtu') : '@';
        $this->emailAncien = $keyValueStore->exists('domaineEmailAncien') ? '@' . $keyValueStore->exists('domaineEmailAncien') : '@';
    }

    /**
     * Get Adresse Mail Etu.
     *
     * @return string format@etu.emse.fr
     */
    public function getEmailEtu(Membre $membre)
    {
        $now = new \DateTime('now');
        $now = (int) $now->format('Y');

        if ($promo = $membre->getPromotion() && $membre->getPersonne()) {
            if ($promo < $now) {
                return preg_replace('#[^a-zA-Z.0-9_]#', '', $this->enMinusculeSansAccent($membre->getPersonne()->getPrenom() . '.' . $membre->getPersonne()->getNom())) . $this->emailAncien;
            } else {
                return preg_replace('#[^a-zA-Z.0-9_]#', '', $this->enMinusculeSansAccent($membre->getPersonne()->getPrenom() . '.' . $membre->getPersonne()->getNom())) . $this->emailEtu;
            }
        } elseif ($membre->getPersonne()) {
            return preg_replace('#[^a-zA-Z.0-9_]#', '', $this->enMinusculeSansAccent($membre->getPersonne()->getPrenom() . '.' . $membre->getPersonne()->getNom())) . $this->emailEtu;
        } else {
            return '';
        }
    }

    private function enMinusculeSansAccent($texte)
    {
        $texte = mb_strtolower($texte, 'UTF-8');
        $texte = str_replace(
            [
                'à', 'â', 'ä', 'á', 'ã', 'å',
                'î', 'ï', 'ì', 'í',
                'ô', 'ö', 'ò', 'ó', 'õ', 'ø',
                'ù', 'û', 'ü', 'ú',
                'é', 'è', 'ê', 'ë',
                'ç', 'ÿ', 'ñ',
            ],
            [
                'a', 'a', 'a', 'a', 'a', 'a',
                'i', 'i', 'i', 'i',
                'o', 'o', 'o', 'o', 'o', 'o',
                'u', 'u', 'u', 'u',
                'e', 'e', 'e', 'e',
                'c', 'y', 'n',
            ],
            $texte
        );

        return $texte;
    }
}
