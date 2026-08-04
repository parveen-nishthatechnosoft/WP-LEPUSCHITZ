<?php

require_once('../wp-load.php');
//require_once('../wp-settings.php');
require_once('../wp-includes/post.php');

class LLabeling {
    public $ItemNumber;
    public $PrintTemplate;
    public $Positions = array();
}

if (!class_exists("LPosition")) {
class LPosition {
    public $Serial;
    public $PositionName;
    public $PositionImage;
    public $Technologies = array();
}
}

class LTechnology {
    public $Code;
    public $Name;
    public $MaxColors;
    public $MaxSize;
}

function AddPositions(LLabeling $aLabeling, string $aItemNumber) {

    set_time_limit(0);

    $args = array(
        'post_type' => 'product',
        'meta_key' => 'product_id',
        'meta_value' => $aItemNumber
    );

    $query = new WP_Query($args);

    while($query->have_posts()) {
        $query->the_post();
        
        echo ('Positions for: ' .$aItemNumber .' <br />');
        $positionExists = false;

        $lOldPositions = get_field('positions');
        if ($lOldPositions) {
            for ($i = count($lOldPositions); $i > 0; $i--) {
                delete_row('positions', $i);
            }
        }

        foreach ($aLabeling->Positions as $aPosition) {

            if (have_rows('positions')){
                while (have_rows('positions')){
                    the_row();

                    if (get_sub_field('serial') == $aPosition->Serial) {
                        $positionExists = true;
                        break;
                    }
                }
            }

            if ($positionExists) {
                continue;
            }

            $row = array(
                'serial' => (string) $aPosition->Serial,
                'position_name' => (string) $aPosition->PositionName,
                'position_image' => (string) $aPosition->PositionImage
            );
            $newPosition = add_row('positions', $row);

            foreach ($aPosition->Technologies as $Technology) {

                $tech_code = (string) $Technology->Code;

                global $wpdb;

                $techExists = false;

                $results = $wpdb->get_results("
                    SELECT posts.ID
                    FROM ".$wpdb->prefix."posts as posts
                    LEFT OUTER JOIN ".$wpdb->prefix."postmeta as meta ON
                            posts.ID = meta.post_id
                        AND meta.meta_key = 'code'
                    WHERE
                        posts.post_type = 'technology'
                    AND posts.post_status = 'publish'
                    AND meta.meta_value = '".$tech_code."'
                 ");

                if (count($results) == 0) {

                    for ($variant = 0; $variant <= 10; $variant++) {

                        $results2 = $wpdb->get_results("
                            SELECT posts.ID
                            FROM ".$wpdb->prefix."posts as posts
                            LEFT OUTER JOIN ".$wpdb->prefix."postmeta as meta ON
                                    posts.ID = meta.post_id
                                AND meta.meta_key = 'code'
                            WHERE
                                posts.post_type = 'technology'
                            AND posts.post_status = 'publish'
                            AND meta.meta_value = '".$tech_code.$variant."'
                         ");

                        if (count($results2) == 0) {
                            continue;
                        } else {

                            foreach ($results2 as $result2) {

                                $sizeFrom = (float) get_field('size_from', $result2->ID);
                                $sizeTo = (float) get_field('size_to', $result2->ID);
                                $maxSize = (float) $Technology->MaxSize;

                                if ($maxSize >= $sizeFrom && $maxSize <= $sizeTo) {
                                    $techExists = true;
                                    $techID = $result2->ID;
                                    break;
                                } else {
                                    continue;
                                }

                            }

                        }
                    }
                } else {

                    foreach ($results as $result) {

                        if (get_post_field('post_title', $result->ID) == $tech_code) {
                            $techExists = true;
                            $techID = $result->ID;
                            break;
                        } else {
                            for ($variant = 0; $variant <= 10; $variant++) {

                                $results2 = $wpdb->get_results("
                                    SELECT posts.ID
                                    FROM ".$wpdb->prefix."posts as posts
                                    LEFT OUTER JOIN ".$wpdb->prefix."postmeta as meta ON
                                            posts.ID = meta.post_id
                                        AND meta.meta_key = 'code'
                                    WHERE
                                        posts.post_type = 'technology'
                                    AND posts.post_status = 'publish'
                                    AND meta.meta_value = '".$tech_code.$variant."'
                                 ");

                                if (count($results2) == 0) {
                                    continue;
                                } else {

                                    foreach ($results2 as $result2){

                                        if (get_post_field('post_title', $result2->ID) == $tech_code) {

                                            $sizeFrom = (float) get_field($result2->ID, 'size_from');
                                            $sizeTo = (float) get_field($result2->ID, 'size_to');
                                            $maxSize = (float) $Technology->MaxSize;

                                            if ($maxSize >= $sizeFrom && $maxSize <= $sizeTo) {
                                                $techExists = true;
                                                $techID = $result2->ID;
                                                break;
                                            } else {
                                                continue;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if ($techExists) {

                    $sub_row = array(
                        'technology_code' => get_post($techID),
                        'technology_name' => (string) $Technology->Name,
                        'max_colors' => (string) $Technology->MaxColors,
                        'max_size' => (string) $Technology->MaxSize
                    );

                    add_sub_row(array('positions', $newPosition, 'technologies'), $sub_row);
                }
            }
        }
    }
}

// Start the Timer
$timestart = microtime(true);

$file = '/var/virtual_www/lepuschitzPROD/tools/testdata/labeling.xml';
$xml = simplexml_load_file($file) or die("Error: Cannot create object");

$lLabelings = array();

echo ('Start parsing' . '<br />');
$pcounter = 0;
foreach ($xml->children() as $Labeling) {
    $lLabeling = new LLabeling();

    // Extract the item number
    $lMatchParts = array();
    if (!preg_match('/([a-zA-Z]+[\d]+)/', $Labeling->itemNumber, $lMatchParts) > 0) {
        // Problem with the product code! Maybe log this and do error handling!
        continue;
    };

    // Get the productcode
    $lProductCode = $lMatchParts[1];

    // If Tech already exists
    if (isset($lLabelings[$lProductCode]))
        continue;

    $lLabeling->ItemNumber = $lProductCode;
    $lLabeling->PrintTemplate = $Labeling->printTemplate;

    foreach ($Labeling->positions->children() as $Position) {
        $lPosition = new LPosition();

        $lPosition->Serial = $Position->serial;
        $lPosition->PositionName = $Position->posName;
        $lPosition->PositionImage = $Position->posImage;

        foreach ($Position->technologies->children() as $Technology) {
            $lTechnology = new LTechnology();

            $lTechnology->Code = $Technology->Code;
            $lTechnology->Name = $Technology->Name;
            $lTechnology->MaxColors = $Technology->maxColor;
            $lTechnology->MaxSize = ((float) $Technology->maxWmm * (float) $Technology->maxHmm) / 100;

            array_push($lPosition->Technologies, $lTechnology);
        }

        array_push($lLabeling->Positions, $lPosition);
    }

    $lLabelings[$lProductCode] = $lLabeling;
    $pcounter++;
    // echo ('Labelnr: ' . $pcounter . '<br />');
    //array_push($lLabelings, $lLabeling);
}

$counter = 0;
foreach ($lLabelings as $Labeling) {
    $itemNumber = $Labeling->ItemNumber;
    // try to limit the calls
    // Max currently 3828
    if (  $counter == 4000 )
    {
        break;
    }
    
    $time_start = microtime(true);
    if (
        /*
        $itemNumber == 'AP731799' ||
        $itemNumber == 'AP1128' ||
        $itemNumber == 'AP2208' ||
$itemNumber == 'AP1229' ||
$itemNumber == 'AP2215' ||
$itemNumber == 'AP63136' ||
$itemNumber == 'AP61982' ||
$itemNumber == 'AP61995' ||
$itemNumber == 'AP61996' ||
$itemNumber == 'AP62519' ||
$itemNumber == 'AP62521' ||
$itemNumber == 'AP62524' ||
$itemNumber == 'AP63024' ||
$itemNumber == 'AP63128' ||
$itemNumber == 'AP731319' ||
$itemNumber == 'AP741663' ||
$itemNumber == 'AP741722' ||
$itemNumber == 'AP761020' ||
$itemNumber == 'AP761104' ||
$itemNumber == 'AP761333' ||
$itemNumber == 'AP781070' ||
$itemNumber == 'AP781187' ||
$itemNumber == 'AP791132' ||
$itemNumber == 'AP791276' ||
$itemNumber == 'AP791400' ||
$itemNumber == 'AP791705' ||
$itemNumber == 'AP791513' ||
$itemNumber == 'AP802520' ||
$itemNumber == 'AP808602' ||
$itemNumber == 'AP808607' ||
$itemNumber == 'AP809346' ||
$itemNumber == 'AP843004' ||
$itemNumber == 'AP61094' ||
$itemNumber == 'AP731789' ||
$itemNumber == 'AP731853' ||
$itemNumber == 'AP741114' ||
$itemNumber == 'AP803604' ||
$itemNumber == 'AP803907' ||
$itemNumber == 'AP809438' ||
$itemNumber == 'AP810123' ||
$itemNumber == 'AP800349' ||
$itemNumber == 'AP808746' ||
$itemNumber == 'AP808749' ||
$itemNumber == 'AP808750' ||
$itemNumber == 'AP808752' ||
$itemNumber == 'AP781887' 

 $itemNumber == 'AP718182' ||
 $itemNumber == 'AP809567' ||
 $itemNumber == 'AP809568' ||
 $itemNumber == 'AP721294' ||
 $itemNumber == 'AP721238' ||
 $itemNumber == 'AP721240' ||
 $itemNumber == 'AP721254' ||

  $itemNumber == 'AP741570' ||
  $itemNumber == 'AP781673' ||
  $itemNumber == 'AP721458' ||
  $itemNumber == 'AP721594' ||
  $itemNumber == 'AP721588' ||
  $itemNumber == 'AP721589' ||
  $itemNumber == 'AP721599' ||
 $itemNumber == 'AP721590' ||
 $itemNumber == 'AP721604' ||
 $itemNumber == 'AP721669' ||
 $itemNumber == 'AP721676' ||
 $itemNumber == 'AP721683' ||
 $itemNumber == 'AP721688' ||
 $itemNumber == 'AP721690' ||
 $itemNumber == 'AP721622' ||
$itemNumber == 'AP721625' ||
$itemNumber == 'AP721626' ||
$itemNumber == 'AP721632' ||
$itemNumber == 'AP721659' ||
$itemNumber == 'AP721665' ||
$itemNumber == 'AP721735' ||
$itemNumber == 'AP721738' ||
$itemNumber == 'AP721703' ||
$itemNumber == 'AP721704' ||
$itemNumber == 'AP721716' ||
$itemNumber == 'AP721718' ||
$itemNumber == 'AP721728' ||
$itemNumber == 'AP721427' ||
$itemNumber == 'AP721441' ||
$itemNumber == 'AP721700' ||
$itemNumber == 'AP721647' ||
$itemNumber == 'AP721651' 

$itemNumber == 'AP721651' ||
$itemNumber == 'AP721744' ||
$itemNumber == 'AP721743' ||
$itemNumber == 'AP718905' ||
$itemNumber == 'AP718904' ||
$itemNumber == 'AP718937' ||
$itemNumber == 'AP718938' ||
$itemNumber == 'AP718939' ||
$itemNumber == 'AP718940' ||
$itemNumber == 'AP718631' ||
$itemNumber == 'AP718936' ||
$itemNumber == 'AP721812' ||
$itemNumber == 'AP721802' ||
$itemNumber == 'AP718990' ||
$itemNumber == 'AP721811' ||
$itemNumber == 'AP718908' ||
$itemNumber == 'AP718671' ||
$itemNumber == 'AP810458' ||
$itemNumber == 'AP810459' ||
$itemNumber == 'AP718686' ||
$itemNumber == 'AP718685' ||
$itemNumber == 'AP718684' ||
$itemNumber == 'AP718683' ||
$itemNumber == 'AP718682' ||
$itemNumber == 'AP718661' ||
$itemNumber == 'AP718677' ||
$itemNumber == 'AP718933' ||
$itemNumber == 'AP718934' ||
$itemNumber == 'AP718694' ||
$itemNumber == 'AP718649' ||
$itemNumber == 'AP718692' ||
$itemNumber == 'AP718691' ||
$itemNumber == 'AP718698' ||
$itemNumber == 'AP718909' ||
$itemNumber == 'AP718910' ||
$itemNumber == 'AP721808' ||
$itemNumber == 'AP721804' ||
$itemNumber == 'AP721809' ||
$itemNumber == 'AP721794' ||
$itemNumber == 'AP874015' ||
$itemNumber == 'AP721791' ||
$itemNumber == 'AP718678' ||
$itemNumber == 'AP718680' ||
$itemNumber == 'AP721817' ||
$itemNumber == 'AP721821' ||
$itemNumber == 'AP718697' ||
$itemNumber == 'AP721801' ||
$itemNumber == 'AP721806' ||
$itemNumber == 'AP721820' ||
$itemNumber == 'AP721807' ||
$itemNumber == 'AP721760' ||
$itemNumber == 'AP721822' ||
$itemNumber == 'AP721816' ||
$itemNumber == 'AP721805' ||
$itemNumber == 'AP721792' ||
$itemNumber == 'AP721796' ||
$itemNumber == 'AP721747' ||
$itemNumber == 'AP721789' ||
$itemNumber == 'AP721765' ||
$itemNumber == 'AP721787' ||
$itemNumber == 'AP718696' ||
$itemNumber == 'AP721803' ||
$itemNumber == 'AP721793' ||
$itemNumber == 'AP721818' ||
$itemNumber == 'AP721815' ||
$itemNumber == 'AP721764' ||
$itemNumber == 'AP721797' ||
$itemNumber == 'AP718695' ||
$itemNumber == 'AP718989' ||
$itemNumber == 'AP718987' ||
$itemNumber == 'AP718988' ||
$itemNumber == 'AP718690' ||
$itemNumber == 'AP718667' ||
$itemNumber == 'AP718986' ||
$itemNumber == 'AP718900' ||
$itemNumber == 'AP718902' ||
$itemNumber == 'AP718665' ||
$itemNumber == 'AP718699' ||
$itemNumber == 'AP718666' ||
$itemNumber == 'AP718903' ||
$itemNumber == 'AP718901' ||
$itemNumber == 'AP721810' ||
$itemNumber == 'AP718651' ||
$itemNumber == 'AP718932' ||
$itemNumber == 'AP721845' ||
$itemNumber == 'AP721842' ||
$itemNumber == 'AP721838' ||
$itemNumber == 'AP721847' ||
$itemNumber == 'AP721848'
*/
$itemNumber == 'AP721849' ||
$itemNumber == 'AP721851' ||
$itemNumber == 'AP718911' ||
$itemNumber == 'AP718912' ||
$itemNumber == 'AP718913' ||
$itemNumber == 'AP718914' ||
$itemNumber == 'AP718915' ||
$itemNumber == 'AP718916' ||
$itemNumber == 'AP718917' ||
$itemNumber == 'AP718918' ||
$itemNumber == 'AP718920' ||
$itemNumber == 'AP718921' ||
$itemNumber == 'AP718922' ||
$itemNumber == 'AP718923' ||
$itemNumber == 'AP718924' ||
$itemNumber == 'AP718925' ||
$itemNumber == 'AP718926' ||
$itemNumber == 'AP718928' ||
$itemNumber == 'AP718929' ||
$itemNumber == 'AP718930' ||
$itemNumber == 'AP718931' ||
$itemNumber == 'AP716001' ||
$itemNumber == 'AP716002' ||
$itemNumber == 'AP716011' ||
$itemNumber == 'AP716003' ||
$itemNumber == 'AP716004' ||
$itemNumber == 'AP716005' ||
$itemNumber == 'AP716006' ||
$itemNumber == 'AP716007' ||
$itemNumber == 'AP716012' ||
$itemNumber == 'AP716008' ||
$itemNumber == 'AP716013' ||
$itemNumber == 'AP716014' ||
$itemNumber == 'AP716009' ||
$itemNumber == 'AP716015' ||
$itemNumber == 'AP840012' ||
$itemNumber == 'AP718513' ||
$itemNumber == 'AP718512' ||
$itemNumber == 'AP837002' ||
$itemNumber == 'AP845029' ||
$itemNumber == 'AP845081' ||
$itemNumber == 'AP845086' ||
$itemNumber == 'AP61787' ||
$itemNumber == 'AP61790' ||
$itemNumber == 'AP61876' ||
$itemNumber == 'AP62033' ||
$itemNumber == 'AP63022' ||
$itemNumber == 'AP63029' ||
$itemNumber == 'AP63030' ||
$itemNumber == 'AP63162' ||
$itemNumber == 'AP63182' ||
$itemNumber == 'AP63838' ||
$itemNumber == 'AP6618' ||
$itemNumber == 'AP702637' ||
$itemNumber == 'AP717005' ||
$itemNumber == 'AP751374' ||
$itemNumber == 'AP800702' ||
$itemNumber == 'AP800718' ||
$itemNumber == 'AP807953' ||
$itemNumber == 'AP852000' ||
$itemNumber == 'AP811500' ||
$itemNumber == 'AP756508' ||
$itemNumber == 'AP721864' ||
$itemNumber == 'AP721865' ||
$itemNumber == 'AP721867' ||
$itemNumber == 'AP806654' ||
$itemNumber == 'AP718547' ||
$itemNumber == 'AP721871' ||
$itemNumber == 'AP721868' ||
$itemNumber == 'AP721869' ||
$itemNumber == 'AP721870' ||
$itemNumber == 'AP718530' ||
$itemNumber == 'AP721872' ||
$itemNumber == 'AP721873' ||
$itemNumber == 'AP721874' ||
$itemNumber == 'AP721876' ||
$itemNumber == 'AP721877' ||
$itemNumber == 'AP721878' ||
$itemNumber == 'AP721879' ||
$itemNumber == 'AP721880' ||
$itemNumber == 'AP718553' ||
$itemNumber == 'AP718555' ||
$itemNumber == 'AP718556' ||
$itemNumber == 'AP812601' ||
$itemNumber == 'AP718540' ||
$itemNumber == 'AP718541' ||
$itemNumber == 'AP718550' ||
$itemNumber == 'AP718551' ||
$itemNumber == 'AP808029' ||
$itemNumber == 'AP808031' ||
$itemNumber == 'AP808032' ||
$itemNumber == 'AP808033' ||
$itemNumber == 'AP721901' ||
$itemNumber == 'AP800659' ||
$itemNumber == 'AP892011' ||
$itemNumber == 'AP861007' ||
$itemNumber == 'AP721909' ||
$itemNumber == 'AP721908' ||
$itemNumber == 'AP721898' ||
$itemNumber == 'AP721882' ||
$itemNumber == 'AP721905' ||
$itemNumber == 'AP718544' ||
$itemNumber == 'AP718545' ||
$itemNumber == 'AP718546' ||
$itemNumber == 'AP718531' ||
$itemNumber == 'AP721896' 
    )
    {
        echo ('got ' . $itemNumber . '<br />');
        AddPositions($Labeling, $itemNumber);
    }
 
    $time_end = microtime(true);
    $time = $time_end - $time_start;
    echo ('AddPositions() in '. $time.' seconds <br />');
    
    $counter++;
    echo ( 'Position: ' . $counter . '<br />');
}

$timeend = microtime(true);
$timetotal = $timeend - $timestart;
echo ('Took: '. $timetotal.' seconds <br />');

echo count($lLabelings);