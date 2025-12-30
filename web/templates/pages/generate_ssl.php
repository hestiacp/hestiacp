<div class="container">

    <form id="main-form" name="v_generate_csr" method="post">
        <input type="hidden" name="token" value="<?= $_SESSION["token"] ?>">
        <input type="hidden" name="generate" value="generate">

        <div class="form-container">
            <h1 class="u-mb20"><?= _("Generate Self-Signed SSL Certificate") ?></h1>
            <?php show_alert_message($_SESSION); ?>
            <div class="u-mb10">
                <label for="v_domain" class="form-label"><?= _("Domain") ?></label>
                <?php $v_domain_value = htmlentities(trim($v_domain, "'")); ?>
                <input
                    type="text"
                    class="form-control"
                    name="v_domain"
                    id="v_domain"
                    value="<?= $v_domain_value ?>">
            </div>
            <div class="u-mb10">
                <label for="v_aliases" class="form-label"><?= _("Aliases") ?></label>
                <?php $v_aliases_value = htmlentities(trim($v_aliases, "'")); ?>
                <textarea
                    class="form-control"
                    name="v_aliases"
                    id="v_aliases"><?= $v_aliases_value ?></textarea>
            </div>
            <div class="u-mb10">
                <label for="v_email" class="form-label">
                    <?= _("Email") ?>
                    <span class="optional">(<?php print _("Optional"); ?>)</span>
                </label>
                <?php $v_email_value = htmlentities(trim($v_email, "'")); ?>
                <input
                    type="email"
                    class="form-control"
                    name="v_email"
                    id="v_email"
                    value="<?= $v_email_value ?>">
            </div>
            <div class="u-mb10">
                <label for="v_country" class="form-label">
                    <?= _("Country") ?>
                    <span class="optional">(<?= _("ISO 3166-1 alpha-2 two-letter code") ?>)</span>
                </label>
                <?php $v_country_value = htmlentities(trim($v_country, "'")); ?>
                <input
                    type="text"
                    class="form-control"
                    name="v_country"
                    id="v_country"
                    value="<?= $v_country_value ?>">
            </div>
            <div class="u-mb10">
                <label for="v_state" class="form-label">
                    <?= _("State / Province") ?>
                </label>
                <?php $v_state_value = htmlentities(trim($v_state, "'")); ?>
                <input
                    type="text"
                    class="form-control"
                    name="v_state"
                    id="v_state"
                    value="<?= $v_state_value ?>">
            </div>
            <div class="u-mb10">
                <label for="v_locality" class="form-label">
                    <?= _("City / Locality") ?>
                </label>
                <?php $v_locality_value = htmlentities(trim($v_locality, "'")); ?>
                <input
                    type="text"
                    class="form-control"
                    name="v_locality"
                    id="v_locality"
                    value="<?= $v_locality_value ?>">
            </div>
            <div class="u-mb20">
                <label for="v_org" class="form-label">
                    <?= _("Organization") ?>
                </label>
                <?php $v_org_value = htmlentities(trim($v_org, "'")); ?>
                <input
                    type="text"
                    class="form-control"
                    name="v_org"
                    id="v_org"
                    value="<?= $v_org_value ?>">
            </div>
            <div>
                <button type="submit" class="button" name="generate">
                    <?= _("Generate") ?>
                </button>
            </div>
        </div>

    </form>

</div>
