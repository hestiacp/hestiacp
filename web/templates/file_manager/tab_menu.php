<span class="btn btn-success fileinput-button button upload file-upload-button-<?php echo $pre_tab ?>">
    <i class="glyphicon glyphicon-plus"></i>
    <span><?=__('UPLOAD')?><span class="progress-<?php echo $pre_tab ?>"></span></span>
    <!-- The file input field used as target for the file upload widget -->
    <input id="file_upload_<?php echo $pre_tab ?>" type="file" name="files[]" multiple>
</span>
<div class="mkfile button" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.createFile()"><?=__('NEW FILE')?></div>
<div class="mkfile button small" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.createFile()" title="<?=__('NEW FILE')?>"></div>
<div class="mkdir button" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.createDir()"><?=__('NEW DIR')?></div>
<div class="mkdir button small" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.createDir()" title="<?=__('NEW DIR')?>"></div>
<div class="download button" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.downloadFiles()"><?=__('DOWNLOAD')?></div><!-- div class="total-size">0 Mb</div -->
<div class="download button small" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.downloadFiles()" title="<?=__('DOWNLOAD')?>"></div>
<div class="rename button" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.renameItems()"><?=__('RENAME')?></div>
<div class="rename button small" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.renameItems()"title="<?=__('RENAME')?>"></div>
<div class="rights button" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.chmodItems()"><?=__('RIGHTS')?></div>
<div class="rights button small" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.chmodItems()"title="<?=__('RIGHTS')?>"></div>
<div class="copy button" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.copyItems()"><?=__('COPY')?></div>
<div class="copy button small" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.copyItems()" title="<?=__('COPY')?>"></div>
<div class="move button" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.moveItems()"><?=__('MOVE')?></div>
<div class="move button small" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.moveItems()" title="<?=__('MOVE')?>"></div>
<div class="archive button" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.packItem()"><?=__('ARCHIVE')?></div>
<div class="archive button small" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.packItem()" title="<?=__('ARCHIVE')?>"></div>
<div class="extract button extract-btn" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.unpackItem()"><?=__('EXTRACT')?></div>
<div class="extract button extract-btn small" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.unpackItem()" title="<?=__('EXTRACT')?>"></div>
<div class="del button" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.deleteItems()"><?=__('DELETE')?></div>
<div class="del button small" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.deleteItems()" title="<?=__('DELETE')?>"></div>


<div class="sort-by button medium"><span class="direction"></span><span class="entity"><?=__('type')?></span><input type="hidden" class="sort-by-v" /></div>
