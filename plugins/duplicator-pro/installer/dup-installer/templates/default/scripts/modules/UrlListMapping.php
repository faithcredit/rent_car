<?php

/**
 *
 * @package Duplicator/Installer
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

?>
<script>
    class UrlListMapping {
        wrapperNode = null;
        itemListNode = null;
        addItemNode = null;
        addButton = null;
        newItemTeplate = null;
        listInfo = null;
        inputName = '';

        constructor(mainWrapper, inputName) {
            if (mainWrapper.length == 0) {
                throw 'Wrapper node don\'t exists';
            }
            if (inputName.length == 0) {
                throw 'Input name is empty';
            }
            this.wrapperNode  = mainWrapper;
            this.inputName    = inputName;
            this.itemListNode = this.wrapperNode.find('.overwrite_sites_list');
            this.listInfo     = this.itemListNode.data('list-info');
            this.addItemNode  = this.itemListNode.find('.overwrite_site_item.add_item');
            this.initAddButton();
        }

        initAddButton() {
            let thisObj = this;

            this.addButton      = this.addItemNode.find('.add_button');
            this.newItemTeplate = jQuery(this.addButton.data('new-item'));

            this.addButton.click(function (event) {
                event.stopPropagation();
                thisObj.addItem();
                return false;
            });

            this.getItemsList().each(function () {
                thisObj.initItemEvents(jQuery(this));
            });
        }

        addItem() {
            if (!this.canAddNewItem()) {
                return;
            }
            
            let itemList = this.getItemsList();
            let newItem  = this.newItemTeplate.clone();

            newItem.find(':disabled').prop('disabled', false);
            newItem.insertBefore(this.addItemNode);
            DUPX.initJsSelect(newItem.find('.js-select'));

            this.setSourceIdOptionsEnabled(itemList, newItem, true);
            this.changeSelectSourceId();
            
            this.setTargetIdOptionsEnabled(itemList, newItem, true);
            this.changeSelectTargetId();
            
            this.initItemEvents(newItem);
            this.setAddItemButtonStatus();
            this.setRemoveItemButtonStatus();
            this.updateLimitMessages();
            DUPX.reavelidateOnChangeAction();
        }

        initItemEvents(item) {
            let thisObj = this;
            let sourceId = item.find('.source_id');
            let targetId = item.find('.target_id');
            let newSlug  = item.find('.new_slug');
            let sourceNoteSlug = item.find('.source-site-note .site-slug');
            let targetNoteSlug = item.find('.target-site-note .site-slug');

            item.find('.del_item').click(function (event) {
                event.stopPropagation();
                thisObj.removeItem(item);
                return false;
            });

            sourceId.change(function () {
                let currentVal = parseInt(jQuery(this).val());
                thisObj.changeSelectSourceId();
                let currentData = thisObj.listInfo.sourceInfo.sites['id_' + currentVal];
                sourceNoteSlug.text(currentData.domain + currentData.path);
            });
            
            targetId.change(function () {
                let currentVal = parseInt(jQuery(this).val());
                thisObj.changeSelectTargetId();
                item.find('.new-slug-wrapper').toggleClass('no-display', (currentVal > 0));
                if (currentVal < 1) {
                    newSlug.trigger('input');
                } else {
                    let currentData = thisObj.listInfo.targetInfo.sites['id_' + currentVal];
                    targetNoteSlug.text(currentData.domain + currentData.path);
                }
            });

            newSlug.on('input',function(e){
                let newText = '';
                let newVal = jQuery(this).val();
                switch (parseInt(targetId.val())) {
                    case -1:
                        newText = (newVal.length == 0 ? '_____/_____' : newVal);
                        break;
                    case 0:
                        newText = thisObj.listInfo.targetInfo.urlPrefix + 
                            (newVal.length == 0 ? '_____' : newVal) + 
                            thisObj.listInfo.targetInfo.urlPostfix;
                        break;
                    default:
                        return;
                }
                targetNoteSlug.text(newText)
            });

            sourceId.trigger('change');
            targetId.trigger('change');
        }

        changeSelectSourceId() {
            let thisObj = this;
            let itemList = this.getItemsList();

            itemList.each(function () {
                thisObj.setSourceIdOptionsEnabled(itemList, jQuery(this), false);
            });
        }

        setSourceIdOptionsEnabled(itemList, currentItem, autoSelect) {
            let selectObj = currentItem.find('.source_id');
            let alreadySelectedIds = itemList.not(currentItem).find('.source_id').map(function(idx, elem) {
                return parseInt(jQuery(elem).val());
            }).get();

            selectObj.find('option').each(function () {
                let currentValue = parseInt(jQuery(this).attr('value'));
                let isAlreadySelected = (jQuery.inArray(currentValue, alreadySelectedIds) > -1);
                jQuery(this).prop('disabled', isAlreadySelected);
            });

            if (autoSelect) {
                selectObj.find('option:not([disabled]):first').prop('selected', true);
                selectObj.trigger('change');
            }
        }

        changeSelectTargetId(selectObj) {
            let thisObj = this;
            let itemList = this.getItemsList();

            itemList.each(function () {
                thisObj.setTargetIdOptionsEnabled(itemList, jQuery(this), false);
            });
        }

        setTargetIdOptionsEnabled(itemList, currentItem, autoSelect) {
            let selectObj = currentItem.find('.target_id');
            let alreadySelectedIds = itemList.not(currentItem).find('.target_id').map(function(idx, elem) {
                return parseInt(jQuery(elem).val());
            }).get();

            selectObj.find('option').each(function () {
                let currentValue = parseInt(jQuery(this).attr('value'));
                if (currentValue == 0 || currentValue == -1) {
                    return;
                }
                let isAlreadySelected = (jQuery.inArray(currentValue, alreadySelectedIds) > -1);
                jQuery(this).prop('disabled', isAlreadySelected);
            });

            if (autoSelect) {
                selectObj.find('option:not([disabled]):first').prop('selected', true);
                selectObj.trigger('change');
            }
        }

        updateFormData(formData) {
            if (this.wrapperNode.length == 0) {
                return formData;
            }
            let itemsList = this.getItemsList();
            if (itemsList.length == 0) {
                return formData;
            }
            let paramValue = [];
            let nameSourceId = itemsList.first().find('.source_id').attr('name').replace(/(.+)\[\]/, '$1');
            let nameTargetId = itemsList.first().find('.target_id').attr('name').replace(/(.+)\[\]/, '$1');
            let nameNewSlug  = itemsList.first().find('.new_slug').attr('name').replace(/(.+)\[\]/, '$1');

            itemsList.each(function() {
                let node = jQuery(this);
                let newObj = {
                    'sourceId': node.find('.source_id').val(),
                    'targetId': node.find('.target_id').val(),
                    'newSlug' : node.find('.new_slug').val()
                };
                paramValue.push(newObj);
            });
            delete formData[nameSourceId];
            delete formData[nameTargetId];
            delete formData[nameNewSlug];
            formData[this.inputName] = JSON.stringify(paramValue);

            return formData;
        }

        getItemsList() {
            return this.itemListNode.find('.overwrite_site_item:not(.title):not(.add_item)');
        }

        canAddNewItem() {
            let numItems = this.getItemsList().length;
            return (
                numItems < this.listInfo.sourceInfo.numSites &&
                numItems < this.listInfo.hardLimit
            );
        }

        canRemoveItem() {
            return (this.getItemsList().length > this.listInfo.minListItems);
        }

        setAddItemButtonStatus() {
            this.addButton.prop('disabled', !this.canAddNewItem());
        }

        setRemoveItemButtonStatus() {
            let thisObj = this;

            this.getItemsList().each(function () {
                jQuery(this).find('.del_item').toggleClass('disabled', !thisObj.canRemoveItem());
            });
        }

        updateLimitMessages() {
            let numItems = this.getItemsList().length;
            if (numItems >= this.listInfo.sourceInfo.numSites) {
                this.addItemNode.find('.overwrite_site_empty_list_msg').addClass('no-display');
                this.addItemNode.find('.overwrite_site_full_list_msg').removeClass('no-display');
                this.addItemNode.find('.overwrite_site_soft_limit_msg').addClass('no-display');
                this.addItemNode.find('.overwrite_site_hard_limit_msg').addClass('no-display');
            } else if (numItems == 0) {
                this.addItemNode.find('.overwrite_site_empty_list_msg').removeClass('no-display');
                this.addItemNode.find('.overwrite_site_full_list_msg').addClass('no-display');
                this.addItemNode.find('.overwrite_site_soft_limit_msg').addClass('no-display');
                this.addItemNode.find('.overwrite_site_hard_limit_msg').addClass('no-display');
            } else if (numItems >= this.listInfo.hardLimit) {
                this.addItemNode.find('.overwrite_site_empty_list_msg').addClass('no-display');
                this.addItemNode.find('.overwrite_site_full_list_msg').addClass('no-display');
                this.addItemNode.find('.overwrite_site_soft_limit_msg').addClass('no-display');
                this.addItemNode.find('.overwrite_site_hard_limit_msg').removeClass('no-display');
            } else if (numItems >= this.listInfo.softLimit) {
                this.addItemNode.find('.overwrite_site_empty_list_msg').addClass('no-display');
                this.addItemNode.find('.overwrite_site_full_list_msg').addClass('no-display');
                this.addItemNode.find('.overwrite_site_soft_limit_msg').removeClass('no-display');
                this.addItemNode.find('.overwrite_site_hard_limit_msg').addClass('no-display');
            } else {
                this.addItemNode.find('.overwrite_site_empty_list_msg').addClass('no-display');
                this.addItemNode.find('.overwrite_site_full_list_msg').addClass('no-display');
                this.addItemNode.find('.overwrite_site_soft_limit_msg').addClass('no-display');
                this.addItemNode.find('.overwrite_site_hard_limit_msg').addClass('no-display');
            }
        }

        removeItem(itemNode) {
            if (!this.canRemoveItem()) {
                return;
            }
            itemNode.remove();
            this.changeSelectSourceId();
            this.changeSelectTargetId();
            this.setAddItemButtonStatus();
            this.setRemoveItemButtonStatus();
            this.updateLimitMessages();
            DUPX.reavelidateOnChangeAction();
        }
    }
</script>