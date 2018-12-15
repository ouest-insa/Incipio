<?php
/**
 * Created by PhpStorm.
 * User: Antoine
 * Date: 20/09/2016
 * Time: 14:51.
 */

namespace Mgate\PubliBundle\Manager;

/**
 * Class BaseImporter.
 */
class CsvImporter
{
    /**
     * @param $row array a csv row
     * @param $columnName string a value of EXPECTED_FORMAT
     * Enables to read a siaje export csv row with string index instead of numeric indexes
     * @param bool $clean should string be cleaned (from upper case to ucwords) ? Can be used when the value is in upper case and should be formatted in a more standard way
     *
     * @return string
     *
     * @throws \Exception if a $columnName is not available into EXPECTED_FORMAT
     */
    protected function readArray($row, $columnName, $clean = false)
    {
        if (in_array($columnName, self::EXPECTED_FORMAT)) {
            $result = $row[array_search($columnName, self::EXPECTED_FORMAT)];
            if ($clean) {
                return utf8_encode(ucwords(strtolower($result)));
            } else {
                return $result;
            }
        } else {
            throw new \Exception('Unknown column ' . $columnName);
        }
    }

    /**
     * @param $date string representing a date
     *
     * @return \DateTime|null
     */
    protected function dateManager($date)
    {
        $date = explode('/', $date); //date under d/m/Y format
        if (array_key_exists(2, $date)) {
            return new \DateTime($date['2'] . '-' . $date['1'] . '-' . $date['0']);
        } else {
            return null;
        }
    }

    /**
     * Converts a french formatted string containing a float to a float.
     *
     * @param $float string representing a float
     *
     * @return float
     */
    protected function floatManager($float)
    {
        return floatval(str_replace(' ', '', str_replace(',', '.', $float)));
    }

    /**
     * slugify a text.
     *
     * @param $string
     *
     * @return string
     */
    protected function normalize($string)
    {
        $table = [
            'Š' => 'S', 'š' => 's', 'Đ' => 'Dj', 'đ' => 'dj', 'Ž' => 'Z', 'ž' => 'z', 'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
            'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
            'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
            'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b',
            'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r',
        ];

        return strtr($string, $table);
    }
}
