<?php
class LLabelTechnology {
    public $Code;
    public $Name;
    public $MaxColors;
    public $MaxSize;
}


if (!class_exists("LPosition")) {
class LPosition {
    const DEFAULTLABEL = 'Druckfläche';
    const FRONTSIDELABEL = 'Vorderseite';
    const BACKSIDELABEL = 'Rückseite';

    public $Serial;
    public $PositionName;
    public $PositionImage;
    /**
     * @var LLabelTechnology
     */
    public $Technologies = array();

    public function AddTechnology($aTechnologyCode) {
        $lNewTechnology = new LLabelTechnology();
        $lNewTechnology->Code = $aTechnologyCode;
        $this->Technologies[] = $lNewTechnology;
        return $lNewTechnology;
    }

    public function TechnologyExists($aTechnologyCode) {
        return (array_search($aTechnologyCode, array_column($this->Technologies, 'Code')) !== false);
    }
}

}
class LLabeling {
    public $ItemNumber;
    public $PrintTemplate;
    /**
     * @var LPosition
     */
    public $Positions = array();

    /**
     * @param $aPositionName
     * @return LPosition|mixed
     */
    public function AddPosition ($aPositionName) {
        // Check if position already exists
        $lIndex = array_search($aPositionName, array_column($this->Positions, 'PositionName'));
        if ($lIndex !== false) {
            return $this->Positions[$lIndex];
        }

        $lNewPosition = new LPosition();
        $lNewPosition->PositionName = $aPositionName;
        $this->Positions[] = $lNewPosition;

        return $lNewPosition;
    }
}

class LLabels {
    /**
     * @var LLabeling[]
     */
    public $Labels = array();

    /**
     * @param $aItemNumber
     * @return LLabeling
     */
    public function AddLabel($aItemNumber) {
        // Check if label already exists
        $lIndex = array_search($aItemNumber, array_column($this->Labels, 'ItemNumber'));
        if ($lIndex !== false) {
            return $this->Labels[$lIndex];
        }

        $lNewLabel = new LLabeling();
        $lNewLabel->ItemNumber = $aItemNumber;
        $this->Labels[] = $lNewLabel;

        return $lNewLabel;
    }

    /**
     * @param $aItemNumber
     * @return bool
     */
    public function LabelExists($aItemNumber) {
        return (array_search($aItemNumber, array_column($this->Labels, 'ItemNumber')) !== false);
    }
}
