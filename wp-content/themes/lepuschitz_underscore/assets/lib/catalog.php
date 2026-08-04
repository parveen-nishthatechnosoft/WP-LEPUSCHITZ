<?php
include_once ('images.php');
include_once ('product.php');
include_once ('price.php');
include_once ('color.php');
include_once ('technology.php');
include_once ('labeling.php');
include_once ('mandator.php');
include_once ('mapping.php');
include_once ('category.php');
include_once ('reader.php');
include_once ('reader_anda.php');
include_once ('reader_lshop.php');
include_once ('reader_kp.php');
include_once ('reader_ge.php');
include_once ('reader_jung.php');
include_once ('reader_roemer.php');
include_once ('writer.php');
include_once ('delete.php');
include_once ('progress.php');

/**
 * Class LCatalog
 */
class LCatalog {
    public $Id;
    public $Title;
    /**
     * @var LProducts
     */
    public $Products;
    /**
     * @var LCategories
     */
    public $Categories;
    /**
     * @var LTechnologies
     */
    public $Technologies;
    /**
     * @var LLabels
     */
    public $Labelings;
    /**
     * @var LMandator
     */
    public $Mandator;
    /**
     * @var LMapping
     */
    public $Mapping;

    /**
     * LCatalog constructor.
     *
     * @param $aTitle
     */
    public function __construct($aTitle, $aMandator, $aId) {
        $this->Title = $aTitle;
        $this->Mandator = $aMandator;
        $this->Id = $aId;

        $this->Products = new LProducts();
        $this->Categories = new LCategories();
        $this->Technologies = new LTechnologies();
        $this->Labelings = new LLabels();
        $this->Mapping = new LMapping();

        $this->Loadmapping();
    }

    private function Loadmapping() {
        $this->Mapping->LoadMapping($this);
    }
}
