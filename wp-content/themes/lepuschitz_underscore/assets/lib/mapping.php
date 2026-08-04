<?php

class LMappingBase {
    public $Name;
    public $MappedToId;

    public function __construct($aName) {
        $this->Name = $aName;
    }
}

class LMappingCategory extends LMappingBase {
    public $GroupMapping;
    /**
     * @var string[]
     */
    public $IgnoredGroups;
}

class LMappingCategoryToCategory extends LMappingCategory {
    /**
     * @var LMappingGroup[]
     */
    public $Groups;
}

class LMappingCategoryToGroup extends LMappingCategory {
}

class LMappingGroup extends LMappingBase {
    const ALL = '*';
}

class LMapping {
    /**
     * @var LMappingCategory[]
     */
    public $MappingElements;

    public function LoadMapping(LCatalog $aCatalog) {
        $lMappings = get_field('mappings', $aCatalog->Id);
        foreach ($lMappings as $lMapping) {
            // Check if to be ignored
            if ($lMapping['mapping_category_ignore'])
                continue;

            $lElementName = $lMapping['mapping_category_name'];

            if ($lMapping['mapping_category_to'] == 'category') {
                $lNewMappingCategory = new LMappingCategoryToCategory($lElementName);
            } else {
                $lNewMappingCategory = new LMappingCategoryToGroup($lElementName);
                $lNewMappingCategory->MappedToId = $lMapping['mapping_category_mapped_group_id'];
            }

            $lNewMappingCategory->GroupMapping = $lMapping['mapping_category_groups_method'];
            switch ($lNewMappingCategory->GroupMapping) {
                case 'customize':
                    $lMappingGroups = $lMapping['mapping_category_groups'];
                    foreach ($lMappingGroups as $lMappingGroup) {
                        // Check if to be ignored
                        if ($lMappingGroup['mapping_group_ignore'])
                            continue;

                        $lNewMappingGroup = new LMappingGroup($lMappingGroup['mapping_group_name']);
                        $lNewMappingGroup->MappedToId = $lMappingGroup['mapping_group_mapped_id'];

                        // Add to list
                        $lNewMappingCategory->Groups[] = $lNewMappingGroup;
                    }
                    break;

                case 'allInGroupExcept':
                    $lMappingIgnoredGroups = $lMapping['mapping_category_ignoredgroups'];
                    foreach ($lMappingIgnoredGroups as $lMappingIgnoredGroup) {
                        $lNewMappingCategory->IgnoredGroups[] = $lMappingIgnoredGroup['mapping_category_ignoredgroup_name'];
                    }

                    // !!! Falling through to default to add also the ALL-Group: NO BREAK !!!

                default:
                    $lNewMappingGroup = new LMappingGroup(LMappingGroup::ALL);
                    $lNewMappingGroup->MappedToId = $lMapping['mapping_category_mapped_group_id'];

                    // Add to list
                    $lNewMappingCategory->Groups[] = $lNewMappingGroup;
                    break;
            }

            $this->MappingElements[] = $lNewMappingCategory;
        }
    }

    /**
     * @param LProduct $aProduct
     * @return false|int
     */
    public function GetMappedGroup($aProduct) {
        foreach ($this->MappingElements as $lMappingElement) {
            // Check if category name exists
            if (($lMappingElement->Name == $aProduct->CategoryIdOrName) || ($lMappingElement->Name == '*')) {
                // Category found, check mapping type
                if ($lMappingElement instanceof LMappingCategoryToCategory) {
                    // Category-mapping, check group-mapping type
                    switch ($lMappingElement->GroupMapping) {
                        case 'customize':
                            // Run through group mapping
                            foreach ($lMappingElement->Groups as $lGroup) {
                                if ($lGroup->Name == $aProduct->GroupIdOrName) {
                                    // Mapping group found
                                    return $lGroup->MappedToId;
                                }
                            }
                            break;

                        case 'allInGroupExcept':
                            // Check not in ignore list
                            if (!in_array('', $lMappingElement->IgnoredGroups)) {
                                // Product is mapped to group
                                return $lMappingElement->Groups[0]->MappedToId;
                            }
                            break;

                        default:
                            // Product is mapped to group
                            return $lMappingElement->Groups[0]->MappedToId;
                    }
                } else {
                    // Group-mapping, check group-mapping type
                    switch ($lMappingElement->GroupMapping) {
                        case 'allInGroupExcept':
                            // Check not in ignore list
                            if (!in_array('', $lMappingElement->IgnoredGroups)) {
                                // Product is mapped to group
                                return $lMappingElement->MappedToId;
                            }
                            break;

                        default:
                            // Product is mapped to group
                            return $lMappingElement->MappedToId;
                    }
                }
            }
        }

        // Not found
        return false;
    }
}
