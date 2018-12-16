<?php

namespace App\Entity\Treso;

/**
 * Common interface between NoteDeFraisDetail & FactureDetail.
 */
interface TresoDetailInterface
{
    /**
     * @return float
     */
    public function getTauxTVA();

    /**
     * @return float
     */
    public function getMontantTVA();

    /**
     * @return float
     */
    public function getMontantHT();

    /**
     * @return float
     */
    public function getMontantTTC();
}
