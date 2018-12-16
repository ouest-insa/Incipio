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

/**
 * Description of chiffreEnLettre
 * source : http://www.javascriptfr.com/codes/CONVERSION-CHIFFRE-MONETAIRE-LETTRE_30141.aspx.
 *
 * @author PHP : adrien (http://blog.toolix.net/convertir-un-chiffre-en-lettres-classe-php-pour-facture-ou-cheque.html)
 * Conversion limitée à 999 999 999 999 999 ou 9 999 999 999 999,99
 * si le nombre contient plus de 2 décimales, il est arrondit à 2 décimales
 */
class ConversionLettreFormatter
{
    public function moneyFormat($number)
    {
        return number_format($number, 2, ',', ' ');
    }

    /**
     * fonction permettant de transformer une valeur numérique en valeur en lettre.
     *
     * @param int $Nombre le nombre a convertir
     * @param int $Devise (0 = aucune, 1 = Euro €, 2 = Dollar $)
     * @param int $Langue (0 = Français, 1 = Belgique, 2 = Suisse)
     *
     * @return string la chaine
     */
    public function convNumberLetter($Nombre, $Devise = 0, $Langue = 0)
    {
        $strDev = '';
        $strCentimes = '';
        $bNegatif = false;

        if ($Nombre < 0) {
            $bNegatif = true;
            $Nombre = abs($Nombre);
        }
        $dblEnt = intval($Nombre);
        $byDec = round(($Nombre - $dblEnt) * 100);
        if (0 == $byDec) {
            if ($dblEnt > 999999999999999) {
                return '#TropGrand';
            }
        } else {
            if ($dblEnt > 9999999999999.99) {
                return '#TropGrand';
            }
        }
        switch ($Devise) {
            case 0:
                if ($byDec > 0) {
                    $strDev = ' virgule';
                }
                break;
            case 1:
                $strDev = ' euro';
                if ($byDec > 0) {
                    $strCentimes = $strCentimes . ' centime' . ($byDec > 1 ? 's' : '');
                }
                break;
            case 2:
                $strDev = ' dollar';
                if ($byDec > 0) {
                    $strCentimes = $strCentimes . ' cent';
                }
                break;
        }
        if (($dblEnt > 1) && (0 != $Devise)) {
            $strDev .= 's';
        }
        if (($byDec > 0) && (0 != $Devise)) {
            $strDev .= ' et';
        }
        $NumberLetter = $this->convNumEnt(floatval($dblEnt), $Langue) . $strDev;
        if (($byDec > 0) && (0 != $Devise)) {
            $NumberLetter .= ' ' . $this->convNumDizaine($byDec, $Langue) . $strCentimes;
        }

        if ($bNegatif) {
            $NumberLetter = 'moins ' . $NumberLetter;
        }

        return $NumberLetter;
    }

    private function convNumEnt($Nombre, $Langue)
    {
        $iTmp = $Nombre - (intval($Nombre / 1000) * 1000);
        $NumEnt = $this->convNumCent(intval($iTmp), $Langue);
        $dblReste = intval($Nombre / 1000);
        $iTmp = $dblReste - (intval($dblReste / 1000) * 1000);
        $StrTmp = $this->convNumCent(intval($iTmp), $Langue);
        switch ($iTmp) {
            case 0:
                break;
            case 1:
                $StrTmp = 'mille ';
                break;
            default:
                $StrTmp = $StrTmp . ' mille ';
        }
        $NumEnt = $StrTmp . $NumEnt;
        $dblReste = intval($dblReste / 1000);
        $iTmp = $dblReste - (intval($dblReste / 1000) * 1000);
        $StrTmp = $this->convNumCent(intval($iTmp), $Langue);
        switch ($iTmp) {
            case 0:
                break;
            case 1:
                $StrTmp = $StrTmp . ' million ';
                break;
            default:
                $StrTmp = $StrTmp . ' millions ';
        }
        $NumEnt = $StrTmp . $NumEnt;
        $dblReste = intval($dblReste / 1000);
        $iTmp = $dblReste - (intval($dblReste / 1000) * 1000);
        $StrTmp = $this->convNumCent(intval($iTmp), $Langue);
        switch ($iTmp) {
            case 0:
                break;
            case 1:
                $StrTmp = $StrTmp . ' milliard ';
                break;
            default:
                $StrTmp = $StrTmp . ' milliards ';
        }
        $NumEnt = $StrTmp . $NumEnt;
        $dblReste = intval($dblReste / 1000);
        $iTmp = $dblReste - (intval($dblReste / 1000) * 1000);
        $StrTmp = $this->convNumCent(intval($iTmp), $Langue);
        switch ($iTmp) {
            case 0:
                break;
            case 1:
                $StrTmp = $StrTmp . ' billion ';
                break;
            default:
                $StrTmp = $StrTmp . ' billions ';
        }
        $NumEnt = $StrTmp . $NumEnt;

        return $NumEnt;
    }

    private function convNumDizaine($Nombre, $Langue)
    {
        $TabUnit = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept',
            'huit', 'neuf', 'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze',
            'seize', 'dix-sept', 'dix-huit', 'dix-neuf', ];
        $TabDiz = ['', '', 'vingt', 'trente', 'quarante', 'cinquante',
            'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt', ];
        if (1 == $Langue) {
            $TabDiz[7] = 'septante';
            $TabDiz[9] = 'nonante';
        } elseif (2 == $Langue) {
            $TabDiz[7] = 'septante';
            $TabDiz[8] = 'huitante';
            $TabDiz[9] = 'nonante';
        }
        $byDiz = intval($Nombre / 10);
        $byUnit = $Nombre - ($byDiz * 10);
        $strLiaison = '-';
        if (1 == $byUnit) {
            $strLiaison = ' et ';
        }
        switch ($byDiz) {
            case 0:
                $strLiaison = '';
                break;
            case 1:
                $byUnit = $byUnit + 10;
                $strLiaison = '';
                break;
            case 7:
                if (0 == $Langue) {
                    $byUnit = $byUnit + 10;
                }
                break;
            case 8:
                if (2 != $Langue) {
                    $strLiaison = '-';
                }
                break;
            case 9:
                if (0 == $Langue) {
                    $byUnit = $byUnit + 10;
                    $strLiaison = '-';
                }
                break;
        }
        $NumDizaine = $TabDiz[$byDiz];
        if (8 == $byDiz && 2 != $Langue && 0 == $byUnit) {
            $NumDizaine = $NumDizaine . 's';
        }
        if ('' != $TabUnit[$byUnit]) {
            $NumDizaine = $NumDizaine . $strLiaison . $TabUnit[$byUnit];
        } else {
            $NumDizaine = $NumDizaine;
        }

        return $NumDizaine;
    }

    private function convNumCent($Nombre, $Langue)
    {
        $TabUnit = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix'];

        $byCent = intval($Nombre / 100);
        $byReste = $Nombre - ($byCent * 100);
        $strReste = $this->convNumDizaine($byReste, $Langue);
        switch ($byCent) {
            case 0:
                $NumCent = $strReste;
                break;
            case 1:
                if (0 == $byReste) {
                    $NumCent = 'cent';
                } else {
                    $NumCent = 'cent ' . $strReste;
                }
                break;
            default:
                if (0 == $byReste) {
                    $NumCent = $TabUnit[$byCent] . ' cents';
                } else {
                    $NumCent = $TabUnit[$byCent] . ' cent ' . $strReste;
                }
        }

        return $NumCent;
    }
}
