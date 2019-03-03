<?php
/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Personne;

interface AnonymizableInterface
{
    /** Related to GDPR,
     * Remove all personnal data of the current class.
     */
    public function anonymize(): void;
}
