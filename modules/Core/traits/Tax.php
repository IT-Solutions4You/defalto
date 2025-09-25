<?php
/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

trait Core_Tax_Trait
{
    public function retrieveDefaultData(): void
    {
        $this->setDefaultPercentage((float)$this->getPercentage());
    }

    public function setDefaultPercentage(float $value): void
    {
        $this->set('default_percentage', $value);
    }

    public function getDefaultPercentage(): float
    {
        return (float)$this->get('default_percentage');
    }

    public function setPercentage(float $value): void
    {
        $this->set('percentage', $value);
    }

    public function getPercentage(): float {
        return (float)$this->get('percentage');
    }
}
