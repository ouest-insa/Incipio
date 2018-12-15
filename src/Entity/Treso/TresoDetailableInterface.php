<?php

namespace Mgate\TresoBundle\Entity;

/**
 * Common interface for Facture and NoteDeFrais which shares one model:
 *  - One main object.
 *  - several children known as details (which use the TresoDetailInterface).
 */
interface TresoDetailableInterface
{
    /**
     * @return TresoDetailInterface[]
     */
    public function getDetails();

    /**
     * @return \DateTime
     */
    public function getDate();

    /**
     * @return string
     */
    public function getReference();

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
