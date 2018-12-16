<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Publish;

class TwigExtensionManager extends \Twig_Extension
{
    public function getName()
    {
        return 'publish_twigextensionmanager';
    }

    public function getFunctions()
    {
        return [
        ];
    }

    public function getFilters()
    {
        return [
            'nl2wbr' => new \Twig_Filter_Method($this, 'nl2wbr'),
            'money' => new \Twig_Filter_Method($this, 'money'),
            'nbrToLetters' => new \Twig_Filter_Method($this, 'nbrToLetters'),
            'liaison' => new \Twig_Filter_Method($this, 'liaison'),
            'pluriel' => new \Twig_Filter_Method($this, 'pluriel'),
        ];
    }

    public function nl2wbr($input)
    {
        return preg_replace('#(\\r\\n)|(\\n)|(\\r)#', '<w:br />', $input);
    }

    public function money($input, $displayZero = true)
    {
        if (0 == $input && !$displayZero) {
            return '';
        }

        return number_format($input, 2, ',', ' ');
    }

    /**
     * fonction permettant de transformer une valeur numérique en valeur en lettre.
     *
     * @param int $nbr    le nombre a convertir
     * @param int $devise (0 = aucune, 1 = Euro €, 2 = Dollar $)
     * @param int $langue (0 = Français, 1 = Belgique, 2 = Suisse)
     *
     * @return string la chaine
     */
    public function nbrToLetters($nbr, $devise = 0, $langue = 0)
    {
        $cv = new ConversionLettreFormatter();

        return $cv->convNumberLetter($nbr, $devise, $langue);
    }

    public function liaison($mot, $entiere = 'de', $contractee = null)
    {
        if (!$contractee) {
            $contractee = substr($entiere, 0, 1) . "'";
        }

        if (preg_match('#^[aeiouy]#', $mot)) {
            return $contractee . ' ' . $mot;
        } else {
            return $entiere . ' ' . $mot;
        }
    }

    public function pluriel($nbr, $pluriel = 's', $simple = '')
    {
        if ($nbr > 1) {
            return $pluriel;
        } else {
            return $simple;
        }
    }
}
