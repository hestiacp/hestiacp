<!-- Begin toolbar -->
<div class="toolbar">
  <div class="toolbar-inner">
    <div class="toolbar-buttons">
      <a class="button button-secondary" id="btn-back" href="/list/firewall/"><i class="fas fa-arrow-left status-icon blue"></i><?=_('Back');?></a>
    </div>
    <div class="toolbar-buttons">
      <a href="#" class="button" data-action="submit" data-id="vstobjects"><i class="fas fa-floppy-disk status-icon purple"></i><?=_('Save');?></a>
    </div>
  </div>
</div>
<!-- End toolbar -->

<div class="l-center animate__animated animate__fadeIn">

  <form id="vstobjects" name="v_add_ip" method="post">
    <input type="hidden" name="token" value="<?=$_SESSION['token']?>">
    <input type="hidden" name="ok" value="Add">

    <div class="form-container">
      <h1 class="form-title"><?=_('Adding Firewall Rule');?></h1>
      <?php show_alert_message($_SESSION);?>
      <div class="u-mb10">
        <label for="v_action" class="form-label"><?=_('Action') ?></label>
        <select class="form-select" name="v_action" id="v_action">
          <option value="DROP" <?php if ((!empty($v_action)) && ( $v_action == "'DROP'" )) echo 'selected'?>><?=_('DROP');?></option>
          <option value="ACCEPT" <?php if ((!empty($v_action)) && ( $v_action == "'ACCEPT'" )) echo 'selected'?>><?=_('ACCEPT');?></option>
        </select>
      </div>
      <div class="u-mb10">
        <label for="v_protocol" class="form-label"><?=_('Protocol') ?></label>
        <select class="form-select" name="v_protocol" id="v_protocol">
          <option value="TCP" <?php if ((!empty($v_protocol)) && ( $v_protocol == "'TCP'" )) echo 'selected'?>><?=_('TCP');?></option>
          <option value="UDP" <?php if ((!empty($v_protocol)) && ( $v_protocol == "'UDP'" )) echo 'selected'?>><?=_('UDP');?></option>
          <option value="ICMP" <?php if ((!empty($v_protocol)) && ( $v_protocol == "'ICMP'" )) echo 'selected'?>><?=_('ICMP');?></option>
        </select>
      </div>
      <div class="u-mb10">
        <label for="v_port" class="form-label">
          <?=_('Port');?> <span class="optional">(<?=_('Ranges and Lists are acceptable');?>)</span>
        </label>
        <input type="text" class="form-control" name="v_port" id="v_port" value="<?=htmlentities(trim($v_port, "'"))?>" placeholder="<?=_('All ports: 0, Range: 80-82, List: 80,443,8080,8443');?>">
      </div>
      <div class="u-mb10">
        <label for="v_ip" class="form-label">
          <?=_('IP address / IPset');?> <span class="optional">(<?=_('CIDR format is supported');?>)</span>
        </label>
        <div class="u-pos-relative">
          <select class="form-select" tabindex="-1" id="quickips_list" onchange="this.nextElementSibling.value=this.value">
            <option value="">&nbsp;</option>
          </select>
          <input type="text" class="form-control list-editor" name="v_ip" id="v_ip" value="<?=htmlentities(trim($v_ip, "'"))?>">
        </div>
      </div>
      <div class="u-mb10">
        <label for="v_comment" class="form-label">
          <?=_('Comment');?> <span class="optional">(<?=_('optional');?>)</span>
        </label>
        <input type="text" class="form-control" name="v_comment" id="v_comment" maxlength="255" value="<?=htmlentities(trim($v_comment, "'"))?>">
      </div>
    </div>

  </form>

</div>

<script>
  var ipLists = JSON.parse('<?=$ipset_lists_json?>');
  ipLists.sort(function (a, b) {
    return a.name > b.name;
  });

  $(function () {
    var targetElement = document.getElementById('quickips_list');

    var newEl = document.createElement("option");
    newEl.text = "IP address lists:";
    newEl.disabled = true;
    targetElement.appendChild(newEl);

    ipLists.forEach(iplist => {
      var newEl = document.createElement("option");
      newEl.text = iplist.name;
      newEl.value = "ipset:" + iplist.name;
      targetElement.appendChild(newEl);
    });
  });
</script>
