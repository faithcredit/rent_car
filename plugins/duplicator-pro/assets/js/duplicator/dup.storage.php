<?php

/*! ============================================================================
*  STORAGE NAMESPACE: All methods at the top of the Duplicator Namespace
*  =========================================================================== */

/**
* Returns the FontAwesome storage type icon.
*
* @param int        id   An id based on the PHP class DUP_PRO_Storage_Types
* @param cssClass   cssClass Extra CSS classes to pass to icon

* @return string    Returns the font-awesome icon
*
* @see DUP_PRO_Storage_Types in file class.storage.entity.php
*      DUP_PRO_Storage_Entity::getStorageIcon
*/

?>
<script>
Duplicator.Storage.getFontAwesomeIcon = function(id, cssClass = '') {
    var icon;
    switch (id) {
        case 0: icon = `<i class="fas fa-hdd fa-fw ${cssClass}"></i>`;                break;
        case 1: icon = `<i class="fab fa-dropbox fa-fw ${cssClass}"></i>`;            break;
        case 2: icon = `<i class="fas fa-network-wired fa-fw ${cssClass}"></i>`;      break;
        case 3: icon = `<i class="fab fa-google-drive fa-fw ${cssClass}"></i>`;       break;
        case 4: icon = `<i class="fab fa-aws fa-fw ${cssClass}"></i>`;                break;
        case 5: icon = `<i class="fas fa-network-wired fa-fw ${cssClass}"></i>`;      break;
        case 6:
        case 7: icon = `<i class="fas fa-cloud fa-fw ${cssClass}"></i>`;              break;
        default:icon = `<i class="fas fa-cloud fa-fw ${cssClass}"></i>`;              break;
    }
    return icon;
};

</script>
