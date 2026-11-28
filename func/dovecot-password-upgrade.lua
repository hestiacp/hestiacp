-- Runs in a privileged auth-worker, never in a login process.
-- NEXT deliberately leaves authentication to the normal passwd-file backend.
function auth_passdb_get_cache_key()
    return ""
end

function auth_passdb_lookup(request)
    -- Delivery/user lookups and non-plaintext mechanisms must not migrate.
    return dovecot.auth.PASSDB_RESULT_NEXT, ""
end

function auth_password_verify(request, password)
    -- Dovecot 2.4 renamed the auth-request variable used by 2.3.
    local mechanism = string.lower(request.mechanism or request.mech or "")
    if mechanism ~= "plain" and mechanism ~= "login" then
        return dovecot.auth.PASSDB_RESULT_NEXT, ""
    end
    if request.master_user and request.master_user ~= "" then
        return dovecot.auth.PASSDB_RESULT_NEXT, ""
    end
    local user = request.user or ""
    if type(password) ~= "string" or #user > 320 or #password == 0 or #password > 4096
        or string.find(user, "[%z\r\n]") or string.find(password, "[%z\r\n]") then
        return dovecot.auth.PASSDB_RESULT_PASSWORD_MISMATCH, "Invalid credentials"
    end

    -- The shell command is constant. Both credentials travel only over a pipe.
    local pipe = io.popen(
        "/usr/bin/timeout 15 /usr/local/hestia/php/bin/php "
        .. "/usr/local/hestia/func/dovecot-password-upgrade.php > /dev/null", "w")
    if not pipe then
        return dovecot.auth.PASSDB_RESULT_INTERNAL_FAILURE, "Password upgrade unavailable"
    end
    local written = pipe:write(user, "\n", password, "\n")
    local _, reason, status = pipe:close()
    if written and reason == "exit" and (status == 0 or status == 10) then
        return dovecot.auth.PASSDB_RESULT_NEXT, ""
    end
    if reason == "exit" and status == 1 then
        return dovecot.auth.PASSDB_RESULT_PASSWORD_MISMATCH, "Invalid credentials"
    end
    return dovecot.auth.PASSDB_RESULT_INTERNAL_FAILURE, "Password upgrade failed"
end
