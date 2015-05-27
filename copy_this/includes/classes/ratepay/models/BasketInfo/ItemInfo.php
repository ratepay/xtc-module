<?php

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category  PayIntelligent
 * @package   ratepay
 * @copyright (C) 2012 PayIntelligent GmbH  <http://www.payintelligent.de/>
 * @license   GPLv2
 */

/**
 * ItemInfo model
 */
class rpItemInfo
{

    /**
     * Article name
     * 
     * @var string
     */
    private $_articleName;

    /**
     * Article number
     * 
     * @var string
     */
    private $_articleNumber;

    /**
     * Quantity
     * 
     * @var string
     */
    private $_quantity;

    /**
     * Unit price
     * 
     * @var string
     */
    private $_unitPriceGross;

    /**
     * Tax
     * 
     * @var string
     */
    private $_taxRate;

    /**
     * Get article name
     * 
     * @return string
     */
    public function getArticleName()
    {
        return $this->_articleName;
    }

    /**
     * Set article name
     * 
     * @param string $articleName
     * @return rpItemInfo
     */
    public function setArticleName($articleName)
    {
        $this->_articleName = $articleName;

        return $this;
    }

    /**
     * Get article number
     * 
     * @return string
     */
    public function getArticleNumber()
    {
        return $this->_articleNumber;
    }

    /**
     * Set article number
     * 
     * @param string $articleNumber
     * @return rpItemInfo
     */
    public function setArticleNumber($articleNumber)
    {
        $this->_articleNumber = $articleNumber;

        return $this;
    }

    /**
     * Get quantity
     * 
     * @return string
     */
    public function getQuantity()
    {
        return $this->_quantity;
    }

    /**
     * Set quantity
     * 
     * @param string $quantity
     * @return rpItemInfo
     */
    public function setQuantity($quantity)
    {
        $this->_quantity = $quantity;

        return $this;
    }

    /**
     * Get unit price gross
     * 
     * @return string
     */
    public function getUnitPriceGross()
    {
        return $this->_unitPriceGross;
    }

    /**
     * Set unit price gross
     * 
     * @param string $unitPriceGross
     * @return rpItemInfo
     */
    public function setUnitPriceGross($unitPriceGross)
    {
        $this->_unitPriceGross = $unitPriceGross;

        return $this;
    }

    /**
     * Get tax rate
     * 
     * @return string
     */
    public function getTaxRate()
    {
        return $this->_taxRate;
    }

    /**
     * Set tax rate
     * 
     * @param string $taxRate
     * @return rpItemInfo
     */
    public function setTaxRate($taxRate)
    {
        $this->_taxRate = $taxRate;

        return $this;
    }

    /**
     * Get model data as array
     * 
     * @return array
     */
    public function getData()
    {
        $data = array(
            'articleName'    => $this->_articleName,
            'articleNumber'  => $this->_articleNumber,
            'quantity'       => $this->_quantity,
            'unitPriceGross' => $this->_unitPriceGross,
            'taxRate'        => $this->_taxRate
        );

        return $data;
    }

}
