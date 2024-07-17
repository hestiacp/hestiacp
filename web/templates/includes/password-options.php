<button type="button" title="<?= _("Toggle password options") ?>" class="u-unstyled-button u-ml5 js-toggle-generate-password">
    <i class="fas fa-caret-down icon-green"></i>
</button>
<div class="u-pos-relative u-mb10 u-mt10 password-options u-hidden panel">
    <div class="u-mb20"><?= _("Select the length and characters to use when generating a password:") ?></div>
    <div class="u-mb20">
        <label for="v_password_length" class="form-label">
            <?= _("Length") ?>
        </label>
        <input type="number" class="form-control js-password-length" name="v_password_length" id="v_password_length" min="8" value="16">
    </div>
    <div class="u-mb20">
        <label for="v_password_options_symbols" class="form-label">
            <?= _("Symbols the password may include") ?>
        </label>
        <input type="text" class="form-control" name="v_password_options_symbols" id="v_password_options_symbols" value="!@#$%^&*()_+-=[]{}|;:/?">
    </div>
    <h2 class="u-text-H3 u-mb10">
        <?= _("Numbers and Symbols") ?>
    </h2>
    <div class="u-mb10">
        <label for="v_password_options_both" class="form-label">
            <input type="radio" class="password_options_radios" name="v_password_options_numbers_symbols" id="v_password_options_both" checked value="both"> <?= _("Both (1@3$)") ?>
        </label>
    </div>
    <div class="u-mb20">
        <label for="v_password_options_numbers" class="form-label">
            <input type="radio" class="password_options_radios" name="v_password_options_numbers_symbols" id="v_password_options_numbers" value="numbers"> <?= _("Numbers (123)") ?>
        </label>
    </div>
</div>
