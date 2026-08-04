<?php

class LColor {
    const NOCOLOR = 'nocolor';
    const NOCOLOR_DE = 'Keine Farbe';

    public $ColorName;
    public $ColorCode;
    /**
     * @var string|null The location of an optional image file for this specific color
     */
    public $ImageUrl;
    /**
     * @var string|null
     */
    public $ProductCode;

    /**
     * LColor constructor.
     *
     * @param string $aColorNameOrCode
     * @param string|null $aImageUrlNameOrCode
     * @param string|null $aColorProductCode
     */
    public function __construct(string $aColorNameOrCode, string $aImageUrlNameOrCode = null, string $aColorProductCode = null) {
        // Try to interpret the color as hex code
        $lColor = LColorConvert::GetColor($aColorNameOrCode);
        if ($lColor) {
            $this->ColorCode = $lColor['hex'];
            $this->ColorName = $lColor['de'];
        }

        $this->ImageUrl = $aImageUrlNameOrCode;
        $this->ProductCode = $aColorProductCode;
    }
}

class LBiColor extends LColor {
    public $Color2Name;
    public $Color2Code;

    /**
     * @param string $aColor1NameOrCode
     * @param string $aColor2NameOrCode
     * @param string|null $aImageUrl
     * @param string|null $aColorProductCode
     */
    public function __construct(string $aColor1NameOrCode, string $aColor2NameOrCode, string $aImageUrl = null, string $aColorProductCode = null) {
        parent::__construct($aColor1NameOrCode, $aImageUrl, $aColorProductCode);

        // Try to interpret the color as hex code
        $lColor = LColorConvert::GetColor($aColor2NameOrCode);
        if ($lColor) {
            $this->Color2Code = $lColor['hex'];
            $this->Color2Name = $lColor['de'];
        }
    }
}

class LColorConvert {
    private static $Colors = array(
        LColor::NOCOLOR => array(
            "red" => 0xF7,
            "green" => 0xF7,
            "blue" => 0xF7,
            "hue" => 0,
            "saturation" => 0,
            "lightness" => 97,
            "hex" => "#F7F7F7",
            "rgb" => "rgb(247,247,247)",
            "hsl" => "hsl(0,0%,97%)",
            "de" => LColor::NOCOLOR_DE
        ),
        "transparent" => array(
            "red" => 0xFF,
            "green" => 0xFA,
            "blue" => 0xF0,
            "hue" => 40,
            "saturation" => 100,
            "lightness" => 97,
            "hex" => "#FFFAF0",
            "rgb" => "rgb(255,250,240)",
            "hsl" => "hsl(40,100%,97%)",
            "de" => "Transparent"
        ),
        "red" => array(
            "red" => 0xFF,
            "green" => 0x00,
            "blue" => 0x00,
            "hue" => 0,
            "saturation" => 100,
            "lightness" => 50,
            "hex" => "#FF0000",
            "rgb" => "rgb(255,0,0)",
            "hsl" => "hsl(0,100%,50%)",
            "de" => "Rot"
        ),
        "darkred" => array(
            "red" => 0x8B,
            "green" => 0x00,
            "blue" => 0x00,
            "hue" => 0,
            "saturation" => 100,
            "lightness" => 27,
            "hex" => "#8B0000",
            "rgb" => "rgb(139,0,0)",
            "hsl" => "hsl(0,100%,27%)",
            "de" => "Dunkelrot"
        ),
        "lightcoral" => array(
            "red" => 0xF0,
            "green" => 0x80,
            "blue" => 0x80,
            "hue" => 0,
            "saturation" => 79,
            "lightness" => 72,
            "hex" => "#F08080",
            "rgb" => "rgb(240,128,128)",
            "hsl" => "hsl(0,79%,72%)",
            "de" => "Helles Korallrot"
        ),
        "firebrick" => array(
            "red" => 0xB2,
            "green" => 0x22,
            "blue" => 0x22,
            "hue" => 0,
            "saturation" => 68,
            "lightness" => 42,
            "hex" => "#B22222",
            "rgb" => "rgb(178,34,34)",
            "hsl" => "hsl(0,68%,42%)",
            "de" => "Feuerrot"
        ),
        "brown" => array(
            "red" => 0xA5,
            "green" => 0x2A,
            "blue" => 0x2A,
            "hue" => 0,
            "saturation" => 59,
            "lightness" => 41,
            "hex" => "#A52A2A",
            "rgb" => "rgb(165,42,42)",
            "hsl" => "hsl(0,59%,41%)",
            "de" => "Braun"
        ),
        "indianred" => array(
            "red" => 0xCD,
            "green" => 0x5C,
            "blue" => 0x5C,
            "hue" => 0,
            "saturation" => 53,
            "lightness" => 58,
            "hex" => "#CD5C5C",
            "rgb" => "rgb(205,92,92)",
            "hsl" => "hsl(0,53%,58%)",
            "de" => "Indianerrot"
        ),
        "rosybrown" => array(
            "red" => 0xBC,
            "green" => 0x8F,
            "blue" => 0x8F,
            "hue" => 0,
            "saturation" => 25,
            "lightness" => 65,
            "hex" => "#BC8F8F",
            "rgb" => "rgb(188,143,143)",
            "hsl" => "hsl(0,25%,65%)",
            "de" => "Dunkles Rosa"
        ),
        "maroon" => array(
            "red" => 0x80,
            "green" => 0x00,
            "blue" => 0x00,
            "hue" => 0,
            "saturation" => 100,
            "lightness" => 25,
            "hex" => "#800000",
            "rgb" => "rgb(128,0,0)",
            "hsl" => "hsl(0,100%,25%)",
            "de" => "Kastanienbraun"
        ),
        "mistyrose" => array(
            "red" => 0xFF,
            "green" => 0xE4,
            "blue" => 0xe1,
            "hue" => 6,
            "saturation" => 100,
            "lightness" => 94,
            "hex" => "#FFE4e1",
            "rgb" => "rgb(255,228,225)",
            "hsl" => "hsl(6,100%,94%)",
            "de" => "Helles Rosa"
        ),
        "salmon" => array(
            "red" => 0xFA,
            "green" => 0x80,
            "blue" => 0x72,
            "hue" => 6,
            "saturation" => 93,
            "lightness" => 71,
            "hex" => "#FA8072",
            "rgb" => "rgb(250,128,114)",
            "hsl" => "hsl(6,93%,71%)",
            "de" => "Lachsrosa"
        ),
        "tomato" => array(
            "red" => 0xFF,
            "green" => 0x63,
            "blue" => 0x47,
            "hue" => 9,
            "saturation" => 100,
            "lightness" => 64,
            "hex" => "#FF6347",
            "rgb" => "rgb(255,99,71)",
            "hsl" => "hsl(9,100%,64%)",
            "de" => "Tomatenrot"
        ),
        "darksalmon" => array(
            "red" => 0xE9,
            "green" => 0x96,
            "blue" => 0x7A,
            "hue" => 15,
            "saturation" => 72,
            "lightness" => 70,
            "hex" => "#E9967A",
            "rgb" => "rgb(233,150,122)",
            "hsl" => "hsl(15,72%,70%)",
            "de" => "Dunkles Lachsrosa"
        ),
        "coral" => array(
            "red" => 0xFF,
            "green" => 0x7F,
            "blue" => 0x50,
            "hue" => 16,
            "saturation" => 100,
            "lightness" => 66,
            "hex" => "#FF7F50",
            "rgb" => "rgb(255,127,80)",
            "hsl" => "hsl(16,100%,66%)",
            "de" => "Korallenrot"
        ),
        "orangered" => array(
            "red" => 0xFF,
            "green" => 0x45,
            "blue" => 0x00,
            "hue" => 16,
            "saturation" => 100,
            "lightness" => 50,
            "hex" => "#FF4500",
            "rgb" => "rgb(255,69,0)",
            "hsl" => "hsl(16,100%,50%)",
            "de" => "Orangerot"
        ),
        "lightsalmon" => array(
            "red" => 0xFF,
            "green" => 0xA0,
            "blue" => 0x7A,
            "hue" => 17,
            "saturation" => 100,
            "lightness" => 74,
            "hex" => "#FFA07A",
            "rgb" => "rgb(255,160,122)",
            "hsl" => "hsl(17,100%,74%)",
            "de" => "Helles Lachsrosa"
        ),
        "sienna" => array(
            "red" => 0xA0,
            "green" => 0x52,
            "blue" => 0x2D,
            "hue" => 19,
            "saturation" => 56,
            "lightness" => 40,
            "hex" => "#A0522D",
            "rgb" => "rgb(160,82,45)",
            "hsl" => "hsl(19,56%,40%)",
            "de" => "Siennabraun"
        ),
        "chocolate" => array(
            "red" => 0xD2,
            "green" => 0x69,
            "blue" => 0x1E,
            "hue" => 25,
            "saturation" => 75,
            "lightness" => 47,
            "hex" => "#D2691E",
            "rgb" => "rgb(210,105,30)",
            "hsl" => "hsl(25,75%,47%)",
            "de" => "Schokoladenbraun"
        ),
        "saddlebrown" => array(
            "red" => 0x8B,
            "green" => 0x45,
            "blue" => 0x13,
            "hue" => 25,
            "saturation" => 76,
            "lightness" => 31,
            "hex" => "#8B4513",
            "rgb" => "rgb(139,69,19)",
            "hsl" => "hsl(25,76%,31%)",
            "de" => "Umbra"
        ),
        "seashell" => array(
            "red" => 0xFF,
            "green" => 0xF5,
            "blue" => 0xEE,
            "hue" => 25,
            "saturation" => 100,
            "lightness" => 97,
            "hex" => "#FFF5EE",
            "rgb" => "rgb(255,245,238)",
            "hsl" => "hsl(25,100%,97%)",
            "de" => "Muschelschale"
        ),
        "peachpuff" => array(
            "red" => 0xFF,
            "green" => 0xDA,
            "blue" => 0xB9,
            "hue" => 28,
            "saturation" => 100,
            "lightness" => 86,
            "hex" => "#FFDAB9",
            "rgb" => "rgb(255,218,185)",
            "hsl" => "hsl(28,100%,86%)",
            "de" => "Puder, Pfirsichrosa"
        ),
        "sandybrown" => array(
            "red" => 0xF4,
            "green" => 0xA4,
            "blue" => 0x60,
            "hue" => 28,
            "saturation" => 87,
            "lightness" => 67,
            "hex" => "#F4A460",
            "rgb" => "rgb(244,164,96)",
            "hsl" => "hsl(28,87%,67%)",
            "de" => "Sandbraun"
        ),
        "linen" => array(
            "red" => 0xFA,
            "green" => 0xF0,
            "blue" => 0xE6,
            "hue" => 30,
            "saturation" => 67,
            "lightness" => 94,
            "hex" => "#FAF0E6",
            "rgb" => "rgb(250,240,230)",
            "hsl" => "hsl(30,67%,94%)",
            "de" => "Leinen"
        ),
        "peru" => array(
            "red" => 0xCD,
            "green" => 0x85,
            "blue" => 0x3F,
            "hue" => 30,
            "saturation" => 59,
            "lightness" => 53,
            "hex" => "#CD853F",
            "rgb" => "rgb(205,133,63)",
            "hsl" => "hsl(30,59%,53%)",
            "de" => "Braun"
        ),
        "bisque" => array(
            "red" => 0xFF,
            "green" => 0xE4,
            "blue" => 0xC4,
            "hue" => 33,
            "saturation" => 100,
            "lightness" => 88,
            "hex" => "#FFE4C4",
            "rgb" => "rgb(255,228,196)",
            "hsl" => "hsl(33,100%,88%)",
            "de" => "Biskuit"
        ),
        "darkorange" => array(
            "red" => 0xFF,
            "green" => 0x8C,
            "blue" => 0x00,
            "hue" => 33,
            "saturation" => 100,
            "lightness" => 50,
            "hex" => "#FF8C00",
            "rgb" => "rgb(255,140,0)",
            "hsl" => "hsl(33,100%,50%)",
            "de" => "Dunkles Orange"
        ),
        "antiquewhite" => array(
            "red" => 0xFA,
            "green" => 0xEB,
            "blue" => 0xd7,
            "hue" => 34,
            "saturation" => 78,
            "lightness" => 91,
            "hex" => "#FAEBd7",
            "rgb" => "rgb(250,235,215)",
            "hsl" => "hsl(34,78%,91%)",
            "de" => "Altweiß"
        ),
        "tan" => array(
            "red" => 0xD2,
            "green" => 0xB4,
            "blue" => 0x8C,
            "hue" => 34,
            "saturation" => 44,
            "lightness" => 69,
            "hex" => "#D2B48C",
            "rgb" => "rgb(210,180,140)",
            "hsl" => "hsl(34,44%,69%)",
            "de" => "Hautfarben"
        ),
        "burlywood" => array(
            "red" => 0xDE,
            "green" => 0xB8,
            "blue" => 0x87,
            "hue" => 34,
            "saturation" => 57,
            "lightness" => 70,
            "hex" => "#DEB887",
            "rgb" => "rgb(222,184,135)",
            "hsl" => "hsl(34,57%,70%)",
            "de" => "Blasses Orange"
        ),
        "blanchedalmond" => array(
            "red" => 0xFF,
            "green" => 0xEB,
            "blue" => 0xCD,
            "hue" => 36,
            "saturation" => 100,
            "lightness" => 90,
            "hex" => "#FFEBCD",
            "rgb" => "rgb(255,235,205)",
            "hsl" => "hsl(36,100%,90%)",
            "de" => "Mandelfarben"
        ),
        "navajowhite" => array(
            "red" => 0xFF,
            "green" => 0xdE,
            "blue" => 0xAD,
            "hue" => 36,
            "saturation" => 100,
            "lightness" => 84,
            "hex" => "#FFdEAD",
            "rgb" => "rgb(255,222,173)",
            "hsl" => "hsl(36,100%,84%)",
            "de" => "Blasses Orangegelb"
        ),
        "papayawhip" => array(
            "red" => 0xFF,
            "green" => 0xEF,
            "blue" => 0xd5,
            "hue" => 37,
            "saturation" => 100,
            "lightness" => 92,
            "hex" => "#FFEFd5",
            "rgb" => "rgb(255,239,213)",
            "hsl" => "hsl(37,100%,92%)",
            "de" => "Helles Orangegelb"
        ),
        "moccasin" => array(
            "red" => 0xFF,
            "green" => 0xE4,
            "blue" => 0xB5,
            "hue" => 38,
            "saturation" => 100,
            "lightness" => 85,
            "hex" => "#FFE4B5",
            "rgb" => "rgb(255,228,181)",
            "hsl" => "hsl(38,100%,85%)",
            "de" => "Helles Orange"
        ),
        "oldlace" => array(
            "red" => 0xFd,
            "green" => 0xF5,
            "blue" => 0xE6,
            "hue" => 39,
            "saturation" => 85,
            "lightness" => 95,
            "hex" => "#FdF5E6",
            "rgb" => "rgb(253,245,230)",
            "hsl" => "hsl(39,85%,95%)",
            "de" => "Altgold"
        ),
        "wheat" => array(
            "red" => 0xF5,
            "green" => 0xDE,
            "blue" => 0xB3,
            "hue" => 39,
            "saturation" => 97,
            "lightness" => 83,
            "hex" => "#F5DEB3",
            "rgb" => "rgb(245,222,179)",
            "hsl" => "hsl(39,97%,83%)",
            "de" => "Weizengelb"
        ),
        "orange" => array(
            "red" => 0xFF,
            "green" => 0xA5,
            "blue" => 0x00,
            "hue" => 39,
            "saturation" => 100,
            "lightness" => 50,
            "hex" => "#FFA500",
            "rgb" => "rgb(255,165,0)",
            "hsl" => "hsl(39,100%,50%)",
            "de" => "Orange"
        ),
        "floralwhite" => array(
            "red" => 0xFF,
            "green" => 0xFA,
            "blue" => 0xF0,
            "hue" => 40,
            "saturation" => 100,
            "lightness" => 97,
            "hex" => "#FFFAF0",
            "rgb" => "rgb(255,250,240)",
            "hsl" => "hsl(40,100%,97%)",
            "de" => "Blütenweiß"
        ),
        "goldenrod" => array(
            "red" => 0xDA,
            "green" => 0xA5,
            "blue" => 0x20,
            "hue" => 43,
            "saturation" => 74,
            "lightness" => 49,
            "hex" => "#DAA520",
            "rgb" => "rgb(218,165,32)",
            "hsl" => "hsl(43,74%,49%)",
            "de" => "Goldrute"
        ),
        "darkgoldenrod" => array(
            "red" => 0xB8,
            "green" => 0x86,
            "blue" => 0x0B,
            "hue" => 43,
            "saturation" => 89,
            "lightness" => 38,
            "hex" => "#B8860B",
            "rgb" => "rgb(184,134,11)",
            "hsl" => "hsl(43,89%,38%)",
            "de" => "Dunkle Goldrute"
        ),
        "cornsilk" => array(
            "red" => 0xFF,
            "green" => 0xF8,
            "blue" => 0xDC,
            "hue" => 48,
            "saturation" => 100,
            "lightness" => 93,
            "hex" => "#FFF8DC",
            "rgb" => "rgb(255,248,220)",
            "hsl" => "hsl(48,100%,93%)",
            "de" => "Helles Maisgelb"
        ),
        "gold" => array(
            "red" => 0xFF,
            "green" => 0xD7,
            "blue" => 0x00,
            "hue" => 51,
            "saturation" => 100,
            "lightness" => 50,
            "hex" => "#FFD700",
            "rgb" => "rgb(255,215,0)",
            "hsl" => "hsl(51,100%,50%)",
            "de" => "Gold"
        ),
        "khaki" => array(
            "red" => 0xF0,
            "green" => 0xE6,
            "blue" => 0x8C,
            "hue" => 54,
            "saturation" => 77,
            "lightness" => 75,
            "hex" => "#F0E68C",
            "rgb" => "rgb(240,230,140)",
            "hsl" => "hsl(54,77%,75%)",
            "de" => "Khaki, Erdfarben"
        ),
        "lemonchiffon" => array(
            "red" => 0xFF,
            "green" => 0xFA,
            "blue" => 0xCD,
            "hue" => 54,
            "saturation" => 100,
            "lightness" => 90,
            "hex" => "#FFFACD",
            "rgb" => "rgb(255,250,205)",
            "hsl" => "hsl(54,100%,90%)",
            "de" => "Zartes Zitronengelb"
        ),
        "palegoldenrod" => array(
            "red" => 0xEE,
            "green" => 0xE8,
            "blue" => 0xAA,
            "hue" => 55,
            "saturation" => 67,
            "lightness" => 80,
            "hex" => "#EEE8AA",
            "rgb" => "rgb(238,232,170)",
            "hsl" => "hsl(55,67%,80%)",
            "de" => "Blasse Goldrute"
        ),
        "darkkhaki" => array(
            "red" => 0xBD,
            "green" => 0xB7,
            "blue" => 0x6B,
            "hue" => 56,
            "saturation" => 38,
            "lightness" => 58,
            "hex" => "#BDB76B",
            "rgb" => "rgb(189,183,107)",
            "hsl" => "hsl(56,38%,58%)",
            "de" => "Dunkler Erdton"
        ),
        "beige" => array(
            "red" => 0xF5,
            "green" => 0xF5,
            "blue" => 0xDC,
            "hue" => 60,
            "saturation" => 56,
            "lightness" => 91,
            "hex" => "#F5F5DC",
            "rgb" => "rgb(245,245,220)",
            "hsl" => "hsl(60,56%,91%)",
            "de" => "Beige"
        ),
        "lightgoldenrodyellow" => array(
            "red" => 0xFA,
            "green" => 0xFA,
            "blue" => 0xD2,
            "hue" => 60,
            "saturation" => 80,
            "lightness" => 90,
            "hex" => "#FAFAD2",
            "rgb" => "rgb(250,250,210)",
            "hsl" => "hsl(60,80%,90%)",
            "de" => "Helles blasses Gelb"
        ),
        "ivory" => array(
            "red" => 0xFF,
            "green" => 0xFF,
            "blue" => 0xF0,
            "hue" => 60,
            "saturation" => 100,
            "lightness" => 97,
            "hex" => "#FFFFF0",
            "rgb" => "rgb(255,255,240)",
            "hsl" => "hsl(60,100%,97%)",
            "de" => "Elfenbein"
        ),
        "lightyellow" => array(
            "red" => 0xFF,
            "green" => 0xFF,
            "blue" => 0xe0,
            "hue" => 60,
            "saturation" => 100,
            "lightness" => 94,
            "hex" => "#FFFFe0",
            "rgb" => "rgb(255,255,224)",
            "hsl" => "hsl(60,100%,94%)",
            "de" => "Hellgelb"
        ),
        "yellow" => array(
            "red" => 0xFF,
            "green" => 0xFF,
            "blue" => 0x00,
            "hue" => 60,
            "saturation" => 100,
            "lightness" => 50,
            "hex" => "#FFFF00",
            "rgb" => "rgb(255,255,0)",
            "hsl" => "hsl(60,100%,50%)",
            "de" => "Gelb"
        ),
        "olive" => array(
            "red" => 0x80,
            "green" => 0x80,
            "blue" => 0x00,
            "hue" => 60,
            "saturation" => 100,
            "lightness" => 25,
            "hex" => "#808000",
            "rgb" => "rgb(128,128,0)",
            "hsl" => "hsl(60,100%,25%)",
            "de" => "Olive"
        ),
        "yellowgreen" => array(
            "red" => 0x9a,
            "green" => 0xCd,
            "blue" => 0x32,
            "hue" => 80,
            "saturation" => 61,
            "lightness" => 50,
            "hex" => "#9aCd32",
            "rgb" => "rgb(154,205,50)",
            "hsl" => "hsl(80,61%,50%)",
            "de" => "Gelbgrün"
        ),
        "olivedrab" => array(
            "red" => 0x6B,
            "green" => 0x8e,
            "blue" => 0x23,
            "hue" => 80,
            "saturation" => 60,
            "lightness" => 35,
            "hex" => "#6B8e23",
            "rgb" => "rgb(107,142,35)",
            "hsl" => "hsl(80,60%,35%)",
            "de" => "Graugrün"
        ),
        "darkolivegreen" => array(
            "red" => 0x55,
            "green" => 0x6B,
            "blue" => 0x2F,
            "hue" => 82,
            "saturation" => 39,
            "lightness" => 30,
            "hex" => "#556B2F",
            "rgb" => "rgb(85,107,47)",
            "hsl" => "hsl(82,39%,30%)",
            "de" => "Dunkles Olivegrün"
        ),
        "greenyellow" => array(
            "red" => 0xAD,
            "green" => 0xFF,
            "blue" => 0x2F,
            "hue" => 84,
            "saturation" => 100,
            "lightness" => 59,
            "hex" => "#ADFF2F",
            "rgb" => "rgb(173,255,47)",
            "hsl" => "hsl(84,100%,59%)",
            "de" => "Grüngelb"
        ),
        "chartreuse" => array(
            "red" => 0x7F,
            "green" => 0xFF,
            "blue" => 0x00,
            "hue" => 90,
            "saturation" => 100,
            "lightness" => 50,
            "hex" => "#7FFF00",
            "rgb" => "rgb(127,255,0)",
            "hsl" => "hsl(90,100%,50%)",
            "de" => "Chartreuse"
        ),
        "lawngreen" => array(
            "red" => 0x7C,
            "green" => 0xFC,
            "blue" => 0x00,
            "hue" => 90,
            "saturation" => 100,
            "lightness" => 49,
            "hex" => "#7CFC00",
            "rgb" => "rgb(124,252,0)",
            "hsl" => "hsl(90,100%,49%)",
            "de" => "Grasgrün"
        ),
        "honeydew" => array(
            "red" => 0xF0,
            "green" => 0xFF,
            "blue" => 0xF0,
            "hue" => 120,
            "saturation" => 100,
            "lightness" => 97,
            "hex" => "#F0FFF0",
            "rgb" => "rgb(240,255,240)",
            "hsl" => "hsl(120,100%,97%)",
            "de" => "Honigmelone"
        ),
        "lightgreen" => array(
            "red" => 0x90,
            "green" => 0xEE,
            "blue" => 0x90,
            "hue" => 120,
            "saturation" => 73,
            "lightness" => 75,
            "hex" => "#90EE90",
            "rgb" => "rgb(144,238,144)",
            "hsl" => "hsl(120,73%,75%)",
            "de" => "Hellgrün"
        ),
        "palegreen" => array(
            "red" => 0x98,
            "green" => 0xFB,
            "blue" => 0x98,
            "hue" => 120,
            "saturation" => 93,
            "lightness" => 79,
            "hex" => "#98FB98",
            "rgb" => "rgb(152,251,152)",
            "hsl" => "hsl(120,93%,79%)",
            "de" => "Blasses Grün"
        ),
        "darkseagreen" => array(
            "red" => 0x8F,
            "green" => 0xBC,
            "blue" => 0x8F,
            "hue" => 120,
            "saturation" => 25,
            "lightness" => 65,
            "hex" => "#8FBC8F",
            "rgb" => "rgb(143,188,143)",
            "hsl" => "hsl(120,25%,65%)",
            "de" => "Blasses Grün"
        ),
        "limegreen" => array(
            "red" => 0x32,
            "green" => 0xCd,
            "blue" => 0x32,
            "hue" => 120,
            "saturation" => 61,
            "lightness" => 50,
            "hex" => "#32Cd32",
            "rgb" => "rgb(50,205,50)",
            "hsl" => "hsl(120,61%,50%)",
            "de" => "Limonengrün"
        ),
        "lime" => array(
            "red" => 0x00,
            "green" => 0xFF,
            "blue" => 0x00,
            "hue" => 120,
            "saturation" => 100,
            "lightness" => 50,
            "hex" => "#00FF00",
            "rgb" => "rgb(0,255,0)",
            "hsl" => "hsl(120,100%,50%)",
            "de" => "Limone"
        ),
        "forestgreen" => array(
            "red" => 0x22,
            "green" => 0x8B,
            "blue" => 0x22,
            "hue" => 120,
            "saturation" => 61,
            "lightness" => 34,
            "hex" => "#228B22",
            "rgb" => "rgb(34,139,34)",
            "hsl" => "hsl(120,61%,34%)",
            "de" => "Waldgrün"
        ),
        "green" => array(
            "red" => 0x00,
            "green" => 0x80,
            "blue" => 0x00,
            "hue" => 120,
            "saturation" => 100,
            "lightness" => 25,
            "hex" => "#008000",
            "rgb" => "rgb(0,128,0)",
            "hsl" => "hsl(120,100%,25%)",
            "de" => "Grün"
        ),
        "darkgreen" => array(
            "red" => 0x00,
            "green" => 0x64,
            "blue" => 0x00,
            "hue" => 120,
            "saturation" => 100,
            "lightness" => 20,
            "hex" => "#006400",
            "rgb" => "rgb(0,100,0)",
            "hsl" => "hsl(120,100%,20%)",
            "de" => "Dunkelgrün"
        ),
        "seagreen" => array(
            "red" => 0x2E,
            "green" => 0x8B,
            "blue" => 0x57,
            "hue" => 146,
            "saturation" => 50,
            "lightness" => 36,
            "hex" => "#2E8B57",
            "rgb" => "rgb(46,139,87)",
            "hsl" => "hsl(146,50%,36%)",
            "de" => "Seegrün"
        ),
        "mediumseagreen" => array(
            "red" => 0x3C,
            "green" => 0xB3,
            "blue" => 0x71,
            "hue" => 147,
            "saturation" => 50,
            "lightness" => 47,
            "hex" => "#3CB371",
            "rgb" => "rgb(60,179,113)",
            "hsl" => "hsl(147,50%,47%)",
            "de" => "Mittleres Seegrün"
        ),
        "mintcream" => array(
            "red" => 0xF5,
            "green" => 0xFF,
            "blue" => 0xFa,
            "hue" => 150,
            "saturation" => 100,
            "lightness" => 98,
            "hex" => "#F5FFFa",
            "rgb" => "rgb(245,255,250)",
            "hsl" => "hsl(150,100%,98%)",
            "de" => "Mintgrün"
        ),
        "springgreen" => array(
            "red" => 0x00,
            "green" => 0xFF,
            "blue" => 0x7F,
            "hue" => 150,
            "saturation" => 100,
            "lightness" => 50,
            "hex" => "#00FF7F",
            "rgb" => "rgb(0,255,127)",
            "hsl" => "hsl(150,100%,50%)",
            "de" => "Frühlingsgrün"
        ),
        "mediumspringgreen" => array(
            "red" => 0x00,
            "green" => 0xFa,
            "blue" => 0x9a,
            "hue" => 157,
            "saturation" => 100,
            "lightness" => 49,
            "hex" => "#00Fa9a",
            "rgb" => "rgb(0,250,154)",
            "hsl" => "hsl(157,100%,49%)",
            "de" => "Mittleres Frühlingsgrün"
        ),
        "aquamarine" => array(
            "red" => 0x7F,
            "green" => 0xFF,
            "blue" => 0xd4,
            "hue" => 160,
            "saturation" => 100,
            "lightness" => 75,
            "hex" => "#7FFFd4",
            "rgb" => "rgb(127,255,212)",
            "hsl" => "hsl(160,100%,75%)",
            "de" => "Aquamarin"
        ),
        "mediumaquamarine" => array(
            "red" => 0x66,
            "green" => 0xCd,
            "blue" => 0xaa,
            "hue" => 160,
            "saturation" => 51,
            "lightness" => 60,
            "hex" => "#66Cdaa",
            "rgb" => "rgb(102,205,170)",
            "hsl" => "hsl(160,51%,60%)",
            "de" => "Mittleres Aquamarin"
        ),
        "turquoise" => array(
            "red" => 0x40,
            "green" => 0xE0,
            "blue" => 0xd0,
            "hue" => 174,
            "saturation" => 72,
            "lightness" => 56,
            "hex" => "#40E0d0",
            "rgb" => "rgb(64,224,208)",
            "hsl" => "hsl(174,72%,56%)",
            "de" => "Türkis"
        ),
        "lightseagreen" => array(
            "red" => 0x20,
            "green" => 0xB2,
            "blue" => 0xaa,
            "hue" => 177,
            "saturation" => 70,
            "lightness" => 41,
            "hex" => "#20B2aa",
            "rgb" => "rgb(32,178,170)",
            "hsl" => "hsl(177,70%,41%)",
            "de" => "Helles Seegrün"
        ),
        "mediumturquoise" => array(
            "red" => 0x48,
            "green" => 0xd1,
            "blue" => 0xCC,
            "hue" => 178,
            "saturation" => 60,
            "lightness" => 55,
            "hex" => "#48d1CC",
            "rgb" => "rgb(72,209,204)",
            "hsl" => "hsl(178,60%,55%)",
            "de" => "Mittleres Türkis"
        ),
        "azure" => array(
            "red" => 0xF0,
            "green" => 0xFF,
            "blue" => 0xFF,
            "hue" => 180,
            "saturation" => 100,
            "lightness" => 97,
            "hex" => "#F0FFFF",
            "rgb" => "rgb(240,255,255)",
            "hsl" => "hsl(180,100%,97%)",
            "de" => "Himmelblau"
        ),
        "lightcyan" => array(
            "red" => 0xE0,
            "green" => 0xFF,
            "blue" => 0xFF,
            "hue" => 180,
            "saturation" => 100,
            "lightness" => 94,
            "hex" => "#E0FFFF",
            "rgb" => "rgb(224,255,255)",
            "hsl" => "hsl(180,100%,94%)",
            "de" => "Helles Zyan"
        ),
        "paleturquoise" => array(
            "red" => 0xaF,
            "green" => 0xEE,
            "blue" => 0xEE,
            "hue" => 180,
            "saturation" => 65,
            "lightness" => 81,
            "hex" => "#aFEEEE",
            "rgb" => "rgb(175,238,238)",
            "hsl" => "hsl(180,65%,81%)",
            "de" => "Blasses Türkis"
        ),
        "cyan" => array(
            "red" => 0x00,
            "green" => 0xFF,
            "blue" => 0xFF,
            "hue" => 180,
            "saturation" => 100,
            "lightness" => 50,
            "hex" => "#00FFFF",
            "rgb" => "rgb(0,255,255)",
            "hsl" => "hsl(180,100%,50%)",
            "de" => "Zyan"
        ),
        "aqua" => array(
            "red" => 0x00,
            "green" => 0xFF,
            "blue" => 0xFF,
            "hue" => 180,
            "saturation" => 100,
            "lightness" => 50,
            "hex" => "#00FFFF",
            "rgb" => "rgb(0,255,255)",
            "hsl" => "hsl(180,100%,50%)",
            "de" => "Aquamarinblau"
        ),
        "darkslategray" => array(
            "red" => 0x2F,
            "green" => 0x4F,
            "blue" => 0x4F,
            "hue" => 180,
            "saturation" => 25,
            "lightness" => 25,
            "hex" => "#2F4F4F",
            "rgb" => "rgb(47,79,79)",
            "hsl" => "hsl(180,25%,25%)",
            "de" => "Petrol"
        ),
        "darkcyan" => array(
            "red" => 0x00,
            "green" => 0x8B,
            "blue" => 0x8B,
            "hue" => 180,
            "saturation" => 100,
            "lightness" => 27,
            "hex" => "#008B8B",
            "rgb" => "rgb(0,139,139)",
            "hsl" => "hsl(180,100%,27%)",
            "de" => "Dunkles Zyan"
        ),
        "teal" => array(
            "red" => 0x00,
            "green" => 0x80,
            "blue" => 0x80,
            "hue" => 180,
            "saturation" => 100,
            "lightness" => 25,
            "hex" => "#008080",
            "rgb" => "rgb(0,128,128)",
            "hsl" => "hsl(180,100%,25%)",
            "de" => "Blaugrün"
        ),
        "darkturquoise" => array(
            "red" => 0x00,
            "green" => 0xCe,
            "blue" => 0xd1,
            "hue" => 181,
            "saturation" => 100,
            "lightness" => 41,
            "hex" => "#00Ced1",
            "rgb" => "rgb(0,206,209)",
            "hsl" => "hsl(181,100%,41%)",
            "de" => "Dunkles Türkis"
        ),
        "cadetblue" => array(
            "red" => 0x5F,
            "green" => 0x9e,
            "blue" => 0xa0,
            "hue" => 182,
            "saturation" => 25,
            "lightness" => 50,
            "hex" => "#5F9ea0",
            "rgb" => "rgb(95,158,160)",
            "hsl" => "hsl(182,25%,50%)",
            "de" => "Kadettblau"
        ),
        "powderblue" => array(
            "red" => 0xb0,
            "green" => 0xE0,
            "blue" => 0xE6,
            "hue" => 187,
            "saturation" => 52,
            "lightness" => 80,
            "hex" => "#b0E0E6",
            "rgb" => "rgb(176,224,230)",
            "hsl" => "hsl(187,52%,80%)",
            "de" => "Puderblau"
        ),
        "deepskyblue" => array(
            "red" => 0x00,
            "green" => 0xBF,
            "blue" => 0xFF,
            "hue" => 195,
            "saturation" => 100,
            "lightness" => 50,
            "hex" => "#00BFFF",
            "rgb" => "rgb(0,191,255)",
            "hsl" => "hsl(195,100%,50%)",
            "de" => "Tiefes Himmelblau"
        ),

        "lightblue" => array(
            "red" => 0xAD,
            "green" => 0xD8,
            "blue" => 0xe6,
            "hue" => 195,
            "saturation" => 53,
            "lightness" => 79,
            "hex" => "#ADD8e6",
            "rgb" => "rgb(173,216,230)",
            "hsl" => "hsl(195,53%,79%)",
            "de" => "Hellblau"
        ),
        "skyblue" => array(
            "red" => 0x87,
            "green" => 0xCE,
            "blue" => 0xEB,
            "hue" => 197,
            "saturation" => 71,
            "lightness" => 73,
            "hex" => "#87CEEB",
            "rgb" => "rgb(135,206,235)",
            "hsl" => "hsl(197,71%,73%)",
            "de" => "Himmelblau"
        ),
        "lightskyblue" => array(
            "red" => 0x87,
            "green" => 0xCe,
            "blue" => 0xFa,
            "hue" => 203,
            "saturation" => 92,
            "lightness" => 75,
            "hex" => "#87CeFa",
            "rgb" => "rgb(135,206,250)",
            "hsl" => "hsl(203,92%,75%)",
            "de" => "Helles Himmelblau"
        ),
        "steelblue" => array(
            "red" => 0x46,
            "green" => 0x82,
            "blue" => 0xB4,
            "hue" => 207,
            "saturation" => 44,
            "lightness" => 49,
            "hex" => "#4682B4",
            "rgb" => "rgb(70,130,180)",
            "hsl" => "hsl(207,44%,49%)",
            "de" => "Stahlblau"
        ),
        "aliceblue" => array(
            "red" => 0xF0,
            "green" => 0xF8,
            "blue" => 0xFF,
            "hue" => 208,
            "saturation" => 100,
            "lightness" => 97,
            "hex" => "#F0F8FF",
            "rgb" => "rgb(240,248,255)",
            "hsl" => "hsl(208,100%,97%)",
            "de" => "Aliceblau"
        ),
        "dodgerblue" => array(
            "red" => 0x1E,
            "green" => 0x90,
            "blue" => 0xFF,
            "hue" => 210,
            "saturation" => 100,
            "lightness" => 56,
            "hex" => "#1E90FF",
            "rgb" => "rgb(30,144,255)",
            "hsl" => "hsl(210,100%,56%)",
            "de" => "Blau der Los Angeles Dodgers"
        ),
        "lightslategray" => array(
            "red" => 0x77,
            "green" => 0x88,
            "blue" => 0x99,
            "hue" => 210,
            "saturation" => 14,
            "lightness" => 53,
            "hex" => "#778899",
            "rgb" => "rgb(119,136,153)",
            "hsl" => "hsl(210,14%,53%)",
            "de" => "Helles Schiefergrau"
        ),
        "slategray" => array(
            "red" => 0x70,
            "green" => 0x80,
            "blue" => 0x90,
            "hue" => 210,
            "saturation" => 13,
            "lightness" => 50,
            "hex" => "#708090",
            "rgb" => "rgb(112,128,144)",
            "hsl" => "hsl(210,13%,50%)",
            "de" => "Schiefergrau"
        ),
        "lightsteelblue" => array(
            "red" => 0xb0,
            "green" => 0xC4,
            "blue" => 0xde,
            "hue" => 214,
            "saturation" => 41,
            "lightness" => 78,
            "hex" => "#b0C4de",
            "rgb" => "rgb(176,196,222)",
            "hsl" => "hsl(214,41%,78%)",
            "de" => "Helles Stahlblau"
        ),
        "cornflowerblue" => array(
            "red" => 0x64,
            "green" => 0x95,
            "blue" => 0xed,
            "hue" => 219,
            "saturation" => 79,
            "lightness" => 66,
            "hex" => "#6495ed",
            "rgb" => "rgb(100,149,237)",
            "hsl" => "hsl(219,79%,66%)",
            "de" => "Kornblumenblau"
        ),
        "royalblue" => array(
            "red" => 0x41,
            "green" => 0x69,
            "blue" => 0xe1,
            "hue" => 225,
            "saturation" => 73,
            "lightness" => 57,
            "hex" => "#4169e1",
            "rgb" => "rgb(65,105,225)",
            "hsl" => "hsl(225,73%,57%)",
            "de" => "Königsblau"
        ),
        "navy" => array(
            "red" => 0x00,
            "green" => 0x00,
            "blue" => 0x80,
            "hue" => 240,
            "saturation" => 100,
            "lightness" => 25,
            "hex" => "#000080",
            "rgb" => "rgb(0,0,128)",
            "hsl" => "hsl(240,100%,25%)",
            "de" => "Marineblau"
        ),
        "midnightblue" => array(
            "red" => 0x19,
            "green" => 0x19,
            "blue" => 0x70,
            "hue" => 240,
            "saturation" => 64,
            "lightness" => 27,
            "hex" => "#191970",
            "rgb" => "rgb(25,25,112)",
            "hsl" => "hsl(240,64%,27%)",
            "de" => "Mitternachtsblau"
        ),
        "mediumblue" => array(
            "red" => 0x00,
            "green" => 0x00,
            "blue" => 0xCd,
            "hue" => 240,
            "saturation" => 100,
            "lightness" => 40,
            "hex" => "#0000Cd",
            "rgb" => "rgb(0,0,205)",
            "hsl" => "hsl(240,100%,40%)",
            "de" => "Mittleres Blau"
        ),
        "lavender" => array(
            "red" => 0xE6,
            "green" => 0xe6,
            "blue" => 0xFa,
            "hue" => 240,
            "saturation" => 67,
            "lightness" => 94,
            "hex" => "#E6e6Fa",
            "rgb" => "rgb(230,230,250)",
            "hsl" => "hsl(240,67%,94%)",
            "de" => "Flieder, Fliederblau, Lavendel"
        ),
        "ghostwhite" => array(
            "red" => 0xF8,
            "green" => 0xF8,
            "blue" => 0xFF,
            "hue" => 240,
            "saturation" => 100,
            "lightness" => 99,
            "hex" => "#F8F8FF",
            "rgb" => "rgb(248,248,255)",
            "hsl" => "hsl(240,100%,99%)",
            "de" => "Weiß mit Blaustich"
        ),
        "blue" => array(
            "red" => 0x00,
            "green" => 0x00,
            "blue" => 0xFF,
            "hue" => 240,
            "saturation" => 100,
            "lightness" => 50,
            "hex" => "#0000FF",
            "rgb" => "rgb(0,0,255)",
            "hsl" => "hsl(240,100%,50%)",
            "de" => "Blau"
        ),
        "darkblue" => array(
            "red" => 0x00,
            "green" => 0x00,
            "blue" => 0x8B,
            "hue" => 240,
            "saturation" => 100,
            "lightness" => 27,
            "hex" => "#00008B",
            "rgb" => "rgb(0,0,139)",
            "hsl" => "hsl(240,100%,27%)",
            "de" => "Dunkles Blau"
        ),
        "slateblue" => array(
            "red" => 0x6a,
            "green" => 0x5a,
            "blue" => 0xCd,
            "hue" => 248,
            "saturation" => 53,
            "lightness" => 58,
            "hex" => "#6a5aCd",
            "rgb" => "rgb(106,90,205)",
            "hsl" => "hsl(248,53%,58%)",
            "de" => "Schlieferblau"
        ),
        "darkslateblue" => array(
            "red" => 0x48,
            "green" => 0x3D,
            "blue" => 0x8B,
            "hue" => 248,
            "saturation" => 39,
            "lightness" => 39,
            "hex" => "#483D8B",
            "rgb" => "rgb(72,61,139)",
            "hsl" => "hsl(248,39%,39%)",
            "de" => "Dunkles Schieferblau"
        ),
        "mediumslateblue" => array(
            "red" => 0x7B,
            "green" => 0x68,
            "blue" => 0xEE,
            "hue" => 249,
            "saturation" => 80,
            "lightness" => 67,
            "hex" => "#7B68EE",
            "rgb" => "rgb(123,104,238)",
            "hsl" => "hsl(249,80%,67%)",
            "de" => "Mittleres Schieferblau"
        ),
        "mediumpurple" => array(
            "red" => 0x93,
            "green" => 0x70,
            "blue" => 0xdB,
            "hue" => 260,
            "saturation" => 60,
            "lightness" => 65,
            "hex" => "#9370dB",
            "rgb" => "rgb(147,112,219)",
            "hsl" => "hsl(260,60%,65%)",
            "de" => "Mittleres Violett"
        ),
        "rebeccapurple" => array(
            "red" => 0x66,
            "green" => 0x33,
            "blue" => 0x99,
            "hue" => 270,
            "saturation" => 50,
            "lightness" => 40,
            "hex" => "#663399",
            "rgb" => "rgb(102,51,153)",
            "hsl" => "hsl(270,50%,40%)",
            "de" => "Rebecca Violett"
        ),
        "blueviolet" => array(
            "red" => 0x8a,
            "green" => 0x2B,
            "blue" => 0xE2,
            "hue" => 271,
            "saturation" => 76,
            "lightness" => 53,
            "hex" => "#8a2BE2",
            "rgb" => "rgb(138,43,226)",
            "hsl" => "hsl(271,76%,53%)",
            "de" => "Blauviolett"
        ),
        "indigo" => array(
            "red" => 0x4B,
            "green" => 0x00,
            "blue" => 0x82,
            "hue" => 275,
            "saturation" => 100,
            "lightness" => 25,
            "hex" => "#4B0082",
            "rgb" => "rgb(75,0,130)",
            "hsl" => "hsl(275,100%,25%)",
            "de" => "Indigoblau"
        ),
        "darkorchid" => array(
            "red" => 0x99,
            "green" => 0x32,
            "blue" => 0xCC,
            "hue" => 280,
            "saturation" => 61,
            "lightness" => 50,
            "hex" => "#9932CC",
            "rgb" => "rgb(153,50,204)",
            "hsl" => "hsl(280,61%,50%)",
            "de" => "Dunkle Orchideenfarbe"
        ),
        "darkviolet" => array(
            "red" => 0x94,
            "green" => 0x00,
            "blue" => 0xd3,
            "hue" => 282,
            "saturation" => 100,
            "lightness" => 41,
            "hex" => "#9400d3",
            "rgb" => "rgb(148,0,211)",
            "hsl" => "hsl(282,100%,41%)",
            "de" => "Dunkelviolett"
        ),
        "mediumorchid" => array(
            "red" => 0xba,
            "green" => 0x55,
            "blue" => 0xd3,
            "hue" => 288,
            "saturation" => 59,
            "lightness" => 58,
            "hex" => "#ba55d3",
            "rgb" => "rgb(186,85,211)",
            "hsl" => "hsl(288,59%,58%)",
            "de" => "Mittlere Orchideenfarbe"
        ),
        "thistle" => array(
            "red" => 0xD8,
            "green" => 0xBF,
            "blue" => 0xD8,
            "hue" => 300,
            "saturation" => 24,
            "lightness" => 80,
            "hex" => "#D8BFD8",
            "rgb" => "rgb(216,191,216)",
            "hsl" => "hsl(300,24%,80%)",
            "de" => "Distelblau"
        ),
        "plum" => array(
            "red" => 0xDd,
            "green" => 0xa0,
            "blue" => 0xdd,
            "hue" => 300,
            "saturation" => 47,
            "lightness" => 75,
            "hex" => "#Dda0dd",
            "rgb" => "rgb(221,160,221)",
            "hsl" => "hsl(300,47%,75%)",
            "de" => "Pflaumenblau"
        ),
        "violet" => array(
            "red" => 0xEE,
            "green" => 0x82,
            "blue" => 0xEE,
            "hue" => 300,
            "saturation" => 76,
            "lightness" => 72,
            "hex" => "#EE82EE",
            "rgb" => "rgb(238,130,238)",
            "hsl" => "hsl(300,76%,72%)",
            "de" => "Violett"
        ),
        "magenta" => array(
            "red" => 0xFF,
            "green" => 0x00,
            "blue" => 0xFF,
            "hue" => 300,
            "saturation" => 100,
            "lightness" => 50,
            "hex" => "#FF00FF",
            "rgb" => "rgb(255,0,255)",
            "hsl" => "hsl(300,100%,50%)",
            "de" => "Magenta, Fuchsie"
        ),
        "fuchsia" => array(
            "red" => 0xFF,
            "green" => 0x00,
            "blue" => 0xFF,
            "hue" => 300,
            "saturation" => 100,
            "lightness" => 50,
            "hex" => "#FF00FF",
            "rgb" => "rgb(255,0,255)",
            "hsl" => "hsl(300,100%,50%)",
            "de" => "Fuchsie, Magenta"
        ),
        "darkmagenta" => array(
            "red" => 0x8B,
            "green" => 0x00,
            "blue" => 0x8B,
            "hue" => 300,
            "saturation" => 100,
            "lightness" => 27,
            "hex" => "#8B008B",
            "rgb" => "rgb(139,0,139)",
            "hsl" => "hsl(300,100%,27%)",
            "de" => "Dunkles Magenta"
        ),
        "purple" => array(
            "red" => 0x80,
            "green" => 0x00,
            "blue" => 0x80,
            "hue" => 300,
            "saturation" => 100,
            "lightness" => 25,
            "hex" => "#800080",
            "rgb" => "rgb(128,0,128)",
            "hsl" => "hsl(300,100%,25%)",
            "de" => "Purpur"
        ),
        "orchid" => array(
            "red" => 0xDa,
            "green" => 0x70,
            "blue" => 0xd6,
            "hue" => 302,
            "saturation" => 59,
            "lightness" => 65,
            "hex" => "#Da70d6",
            "rgb" => "rgb(218,112,214)",
            "hsl" => "hsl(302,59%,65%)",
            "de" => "Helles Violett"
        ),
        "mediumvioletred" => array(
            "red" => 0xC7,
            "green" => 0x15,
            "blue" => 0x85,
            "hue" => 322,
            "saturation" => 81,
            "lightness" => 43,
            "hex" => "#C71585",
            "rgb" => "rgb(199,21,133)",
            "hsl" => "hsl(322,81%,43%)",
            "de" => "Violettrot"
        ),
        "deeppink" => array(
            "red" => 0xFF,
            "green" => 0x14,
            "blue" => 0x93,
            "hue" => 328,
            "saturation" => 100,
            "lightness" => 54,
            "hex" => "#FF1493",
            "rgb" => "rgb(255,20,147)",
            "hsl" => "hsl(328,100%,54%)",
            "de" => "Kräftiges Rosa"
        ),
        "hotpink" => array(
            "red" => 0xFF,
            "green" => 0x69,
            "blue" => 0xB4,
            "hue" => 330,
            "saturation" => 100,
            "lightness" => 71,
            "hex" => "#FF69B4",
            "rgb" => "rgb(255,105,180)",
            "hsl" => "hsl(330,100%,71%)",
            "de" => "Leuchtendes Rosa"
        ),
        "palevioletred" => array(
            "red" => 0xDB,
            "green" => 0x70,
            "blue" => 0x93,
            "hue" => 340,
            "saturation" => 60,
            "lightness" => 65,
            "hex" => "#DB7093",
            "rgb" => "rgb(219,112,147)",
            "hsl" => "hsl(340,60%,65%)",
            "de" => "Blasses Lila"
        ),
        "lavenderblush" => array(
            "red" => 0xFF,
            "green" => 0xF0,
            "blue" => 0xF5,
            "hue" => 340,
            "saturation" => 100,
            "lightness" => 97,
            "hex" => "#FFF0F5",
            "rgb" => "rgb(255,240,245)",
            "hsl" => "hsl(340,100%,97%)",
            "de" => "Heller Fliederton"
        ),
        "crimson" => array(
            "red" => 0xDC,
            "green" => 0x14,
            "blue" => 0x3C,
            "hue" => 348,
            "saturation" => 83,
            "lightness" => 47,
            "hex" => "#DC143C",
            "rgb" => "rgb(220,20,60)",
            "hsl" => "hsl(348,83%,47%)",
            "de" => "Purpur"
        ),
        "pink" => array(
            "red" => 0xFF,
            "green" => 0xC0,
            "blue" => 0xCb,
            "hue" => 350,
            "saturation" => 100,
            "lightness" => 88,
            "hex" => "#FFC0Cb",
            "rgb" => "rgb(255,192,203)",
            "hsl" => "hsl(350,100%,88%)",
            "de" => "Rosa"
        ),
        "lightpink" => array(
            "red" => 0xFF,
            "green" => 0xb6,
            "blue" => 0xC1,
            "hue" => 351,
            "saturation" => 100,
            "lightness" => 86,
            "hex" => "#FFb6C1",
            "rgb" => "rgb(255,182,193)",
            "hsl" => "hsl(351,100%,86%)",
            "de" => "Helles Rosa"
        ),
        "snow" => array(
            "red" => 0xFF,
            "green" => 0xFa,
            "blue" => 0xFa,
            "hue" => 0,
            "saturation" => 100,
            "lightness" => 99,
            "hex" => "#FFFaFa",
            "rgb" => "rgb(255,250,250)",
            "hsl" => "hsl(0,100%,99%)",
            "de" => "Weiß mit Rotstich"
        ),
        "white" => array(
            "red" => 0xFF,
            "green" => 0xFF,
            "blue" => 0xFF,
            "hue" => 360,
            "saturation" => 0,
            "lightness" => 100,
            "hex" => "#FFFFFF",
            "rgb" => "rgb(255,255,255)",
            "hsl" => "hsl(360,0%,100%)",
            "de" => "Weiß"
        ),
        "whitesmoke" => array(
            "red" => 0xF5,
            "green" => 0xF5,
            "blue" => 0xF5,
            "hue" => 360,
            "saturation" => 0,
            "lightness" => 96,
            "hex" => "#F5F5F5",
            "rgb" => "rgb(245,245,245)",
            "hsl" => "hsl(360,0%,96%)",
            "de" => "Zartes Hellgrau"
        ),
        "gainsboro" => array(
            "red" => 0xDC,
            "green" => 0xdC,
            "blue" => 0xdC,
            "hue" => 360,
            "saturation" => 0,
            "lightness" => 86,
            "hex" => "#DCdCdC",
            "rgb" => "rgb(220,220,220)",
            "hsl" => "hsl(360,0%,86%)",
            "de" => "Hellgrau"
        ),
        "lightgray" => array(
            "red" => 0xD3,
            "green" => 0xd3,
            "blue" => 0xd3,
            "hue" => 360,
            "saturation" => 0,
            "lightness" => 83,
            "hex" => "#D3d3d3",
            "rgb" => "rgb(211,211,211)",
            "hsl" => "hsl(360,0%,83%)",
            "de" => "Hellgrau, Silbergrau"
        ),
        "silver" => array(
            "red" => 0xC0,
            "green" => 0xC0,
            "blue" => 0xC0,
            "hue" => 360,
            "saturation" => 0,
            "lightness" => 75,
            "hex" => "#C0C0C0",
            "rgb" => "rgb(192,192,192)",
            "hsl" => "hsl(360,0%,75%)",
            "de" => "Silber"
        ),
        "darkgray" => array(
            "red" => 0xa9,
            "green" => 0xa9,
            "blue" => 0xa9,
            "hue" => 360,
            "saturation" => 0,
            "lightness" => 66,
            "hex" => "#a9a9a9",
            "rgb" => "rgb(169,169,169)",
            "hsl" => "hsl(360,0%,66%)",
            "de" => "Dunkelgrau"
        ),
        "gray" => array(
            "red" => 0x80,
            "green" => 0x80,
            "blue" => 0x80,
            "hue" => 360,
            "saturation" => 0,
            "lightness" => 50,
            "hex" => "#808080",
            "rgb" => "rgb(128,128,128)",
            "hsl" => "hsl(360,0%,50%)",
            "de" => "Grau"
        ),
        "dimgray" => array(
            "red" => 0x69,
            "green" => 0x69,
            "blue" => 0x69,
            "hue" => 360,
            "saturation" => 0,
            "lightness" => 41,
            "hex" => "#696969",
            "rgb" => "rgb(105,105,105)",
            "hsl" => "hsl(360,0%,41%)",
            "de" => "Dunkelgrau"
        ),
        "black" => array(
            "red" => 0x00,
            "green" => 0x00,
            "blue" => 0x00,
            "hue" => 360,
            "saturation" => 0,
            "lightness" => 0,
            "hex" => "#000000",
            "rgb" => "rgb(0,0,0)",
            "hsl" => "hsl(360,0%,0%)",
            "de" => "Schwarz"
        ),
    );

    /**
     * Calculate the 3dimensional distance of two colors
     *
     * @param array $aColor1
     * @param array $aColor2
     * @return float
     */
    private static function GetColorDistance(array $aColor1, array $aColor2) {
        return sqrt(pow($aColor1[0] - $aColor2[0], 2) + pow($aColor1[1] - $aColor2[1], 2) + pow($aColor1[2] - $aColor2[2], 2));
    }

    /**
     * Get the color by its name or hex values
     *
     * @param string $aColornameOrCode
     * @return mixed
     */
    public static function GetColor(string $aColornameOrCode) {
        $lColor = null;

        // Remove transparent part from ANDA
        if (strpos($aColornameOrCode, '/transparent')) {
            $aColornameOrCode = substr($aColornameOrCode, 0, strlen($aColornameOrCode) - 12);
        }

        // Remove other string parts
        $aColornameOrCode = trim(str_ireplace('Hi-Vis ', '', $aColornameOrCode));
        $aColornameOrCode = trim(str_ireplace('Grey', 'Gray', $aColornameOrCode));
        $aColornameOrCode = trim(str_ireplace('matt', '', $aColornameOrCode));
        $aColornameOrCode = trim(str_ireplace('gebürstet', '', $aColornameOrCode));
        $aColornameOrCode = trim(str_ireplace('poliert', '', $aColornameOrCode));
        $aColornameOrCode = trim(str_ireplace('satin', '', $aColornameOrCode));
        $aColornameOrCode = trim(str_ireplace('metallic', '', $aColornameOrCode));
        $aColornameOrCode = trim(str_ireplace('brushed', '', $aColornameOrCode));
        $aColornameOrCode = trim(str_ireplace('pearl', '', $aColornameOrCode));

        // Check if matches with english name
        if (isset(LColorConvert::$Colors[strtolower($aColornameOrCode)])) {
            $lColor = LColorConvert::$Colors[strtolower($aColornameOrCode)];
        }

        // Try to find within german parts
        if (!$lColor) {
            foreach (LColorConvert::$Colors as $lColorElement) {
                if (strcasecmp($lColorElement['de'], $aColornameOrCode) == 0) {
                    $lColor = $lColorElement;
                }
            }
        }

        // Try to find by hex code
        if (!$lColor) {
            foreach (LColorConvert::$Colors as $lColorElement) {
                if (strcasecmp($lColorElement['hex'], $aColornameOrCode) == 0) {
                    $lColor = $lColorElement;
                }
            }
        }

        // Try to find by shortest distance if given in #HEX
        if (!$lColor) {
            if (preg_match('/[#]*([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})/i', $aColornameOrCode, $lMatches)) {
                $lColorAsArray = array(hexdec($lMatches[1]), hexdec($lMatches[2]), hexdec($lMatches[3]));
                $lMinimumDistance = pow(2, 30);
                foreach (LColorConvert::$Colors as $lColorName => $lColorElement) {
                    $lDistance = LColorConvert::GetColorDistance($lColorAsArray, array($lColorElement['red'], $lColorElement['green'], $lColorElement['blue']));
                    if ($lDistance < $lMinimumDistance) {
                        $lMinimumDistance = $lDistance;
                        $lColor = $lColorElement;
                    }
                }
            }
        }

        return ($lColor);
    }
}
