<!-- div class="upload button" onClick="FM.uploadFile()">UPLOAD</div -->
<!-- input type="file" name="file_upload" id="file_upload_<?php echo $pre_tab ?>" / -->
<span class="btn btn-success fileinput-button button upload file-upload-button-<?php echo $pre_tab ?>">
    <i class="glyphicon glyphicon-plus"></i>
    <span>UPLOAD<span class="progress-<?php echo $pre_tab ?>"></span></span>
    <!-- The file input field used as target for the file upload widget -->
    <input id="file_upload_<?php echo $pre_tab ?>" type="file" name="files[]" multiple>
</span>
<div class="mkfile button" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.createFile()">NEW FILE</div>
<div class="mkfile button small" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.createFile()" title="Create File"></div>
<div class="mkdir button" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.createDir()">NEW DIR</div>
<div class="mkdir button small" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.createDir()" title="Create Dir"></div>
<div class="del button" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.deleteItems()">DELETE</div>
<div class="del button small" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.deleteItems()" title="Delete"></div>
<div class="rename button" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.renameItems()">RENAME</div>
<div class="rename button small" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.renameItems()"title="Rename"></div>
<div class="copy button" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.copyItems()">COPY</div>
<div class="copy button small" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.copyItems()" title="Copy"></div>
<div class="archive button" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.packItem()">ARCHIVE</div>
<div class="archive button small" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.packItem()" title="Create Archive"></div>
<div class="extract button extract-btn" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.unpackItem()">EXRACT</div>
<div class="extract button small" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.unpackItem()" title="Extract Archive"></div>
<div class="download button" onClick="FM.setTabActive(FM['TAB_<?php echo $pre_tab ?>']);FM.downloadFiles()">DOWNLOAD</div><!-- div class="total-size">0 Mb</div -->


<? /* div class="" title="Bulk">
    <select onChange="FM.bulkOperation(this)">
        <option value="-1">Select bulk operation</option>
        <option value="bulkCopy">Bulk copy</option>
        <option value="bulkRemove">Bulk remove</option>
    </select>
</div */ ?><!-- div class="total-size">0 Mb</div -->


<div class="sort-by button medium"><span class="direction"></span><span class="entity">type</span><input type="hidden" class="sort-by-v" /></div>
<!-- div class="sort-by button">SORT BY<span class="direction"></span><span class="entity">type</span><input type="hidden" class="sort-by-v" /></div-->
