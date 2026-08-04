<?php

/**
 * Class LCategory
 */
class LCategory {
    /**
     * The ID of the category
     *
     * @var int|string
     */
    public $Id;
    /**
     * @var string The name of the category
     */
    public $Name;
    /**
     * @var LCategory[]
     */
    public $Childs;
    /**
     * @var LMarkupDown[]
     */
    public $MarkupsDowns;
    public $MarkupsDownsRead;

    /**
     * LCategory constructor.
     *
     * @param int|string $aId
     * @param string $aCategoryName
     */
    public function __construct($aId, string $aCategoryName) {
        $this->Id = $aId;
        $this->Name = $aCategoryName;
        $this->Childs = [];
        $this->MarkupsDowns = [];
        $this->MarkupsDownsRead = false;
    }

    /**
     * @param $aCategoryName
     * @return LCategory
     */
    public function AddCategory($aId, $aCategoryName): LCategory {
        // First check if category still exists
        if (!isset($this->Childs[$aId])) {
            $lNewCategory = new LCategory($aId, $aCategoryName);
            $this->Childs[$aId] = $lNewCategory;
        }

        return ($this->Childs[$aId]);
    }

    /**
     * Read the rates out of the database if possible
     */
    public function ReadAndaPrices() {
        if (!$this->MarkupsDownsRead) {
            // Get the price rates from the database
            $lArgs = array(
                'post_type' => 'product_category',
                'meta_query' => array(
                    array(
                        'key' => 'category_id',
                        'value' => $this->Id
                    )
                )
            );
            $lQuery = new WP_Query($lArgs);
            if ($lQuery->have_posts()) {
                $lQuery->the_post();
                $lRates = get_field('anda_pricerates', get_the_ID());
                if (is_array($lRates)) {
                    foreach ($lRates as $lRate) {
                        $this->MarkupsDowns[] = new LMarkupDown((float)$lRate['anda_pricerate_markup'], (int)$lRate['anda_pricerate_from'], (int)$lRate['anda_pricerate_to']);
                    }
                }
            }
            wp_reset_postdata();

            // Mark as read once
            $this->MarkupsDownsRead = true;
        }
    }

    /**
     * @param $aId
     * @return LMarkupDown[]|null
     */
    public function GetAndaPriceRates($aId) {
        // Check on child level
        foreach ($this->Childs as $lChild) {
            $lResult = $lChild->GetAndaPriceRates($aId);
            if ($lResult != null)
                return $lResult;
        }

        // Check if child category and has rates
        if ($this->HasChild($aId)) {
            if (sizeof($this->MarkupsDowns) > 0) {
                return $this->MarkupsDowns;
            }
        }

        // Check if on root level
        if (isset($this->Childs[$aId])) {
            return ($this->Childs[$aId]->MarkupsDowns);
        }

        return null;
    }

    private function HasChild($aId) {
        // Check on child level
        foreach ($this->Childs as $lChild) {
            if ($lChild->HasChild($aId)) {
                return true;
            }
        }

        // Check if on root level
        if (isset($this->Childs[$aId])) {
            return true;
        }

        return false;
    }
}

/**
 * Class LCategories
 */
class LCategories {
    /**
     * @var LCategory[]
     */
    public $Childs;
    /**
     * @var LMarkupDown[]
     */
    public $GlobalAndaRates;

    /**
     * LCategories constructor.
     */
    public function __construct() {
        // Remember levels
        $this->GlobalAndaRates = null;

        // Try to load global ANDA rates
        $lRates = get_field('anda_pricerates', 'option');
        if (is_array($lRates) || sizeof($lRates) > 0) {
            foreach ($lRates as $lRate) {
                $this->GlobalAndaRates[] = new LMarkupDown((float)$lRate['anda_pricerate_markup'], (int)$lRate['anda_pricerate_from'], (int)$lRate['anda_pricerate_to']);
            }
        }
    }

    /**
     * @param $aCategoryName
     * @return LCategory
     */
    public function AddCategory($aId, $aCategoryName): LCategory {
        // Check if childs are set
        if (!is_array($this->Childs))
            $this->Childs = [];

        // First check if category still exists
        if (!isset($this->Childs[$aId])) {
            $lNewCategory = new LCategory($aId, $aCategoryName);
            $this->Childs[$aId] = $lNewCategory;
        }

        return ($this->Childs[$aId]);
    }

    public function GetAndaPriceRates($aId) {
        // Check on child level
        foreach ($this->Childs as $lChild) {
            $lResult = $lChild->GetAndaPriceRates($aId);
            if ($lResult != null)
                return $lResult;
        }

        // Check if on root level
        if (isset($this->Childs[$aId])) {
            return ($this->Childs[$aId]->MarkupsDowns);
        }

        return $this->GlobalAndaRates;
    }
}

