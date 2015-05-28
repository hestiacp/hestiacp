<!-- div class="upload button" onClick="FM.uploadFile()">UPLOAD</div -->
<!-- input type="file" name="file_upload" id="file_upload_<?php echo $pre_tab ?>" / -->
<span class="btn btn-success fileinput-button button upload file-upload-button-<?php echo $pre_tab ?>">
    <i class="glyphicon glyphicon-plus"></i>
    <span>UPLOAD<span class="progress-<?php echo $pre_tab ?>"></span></span>
    <!-- The file input field used as target for the file upload widget -->
    <input id="file_upload_<?php echo $pre_tab ?>" type="file" name="files[]" multiple>
</span>
<div class="mkfile button" onClick="FM.createFile()">CREATE FILE</div>
<div class="mkdir button" onClick="FM.createDir()">CREATE DIR</div>
<div class="mkdir button" onClick="FM.deleteItems()">DELETE</div>
<div class="mkdir button" onClick="FM.renameItems()">RENAME</div>

<div class="mkdir button" onClick="FM.copyItems()">COPY</div>

<div class="download button" onClick="FM.downloadFiles()">DOWNLOAD</div><!-- div class="total-size">0 Mb</div -->
<div class="sort-by button">SORT BY<span class="direction"></span><span class="entity">type</span><input type="hidden" class="sort-by-v" /></div>
