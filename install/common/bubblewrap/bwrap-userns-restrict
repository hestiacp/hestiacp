# This profile allows almost everything and only exists to allow bwrap
# to work on a system with user namespace restrictions being enforced.
# bwrap is allowed access to user namespaces and capabilities within
# the user namespace, but its children do not have capabilities,
# blocking bwrap from being able to be used to arbitrarily by-pass the
# user namespace restrictions.

# Note: the bwrap child is stacked against the bwrap profile due to
# bwraps use of no-new-privs.

abi <abi/4.0>,

include <tunables/global>

profile bwrap /usr/bin/bwrap flags=(attach_disconnected,mediate_deleted) {
  allow capability,
  # not allow all, to allow for pix stack on systems that don't support
  # rule priority.
  #
  # sadly we have to allow 'm' every where to allow children to work under
  # profile stacking atm.
  allow file rwlkm /{**,},
  allow network,
  allow unix,
  allow ptrace,
  allow signal,
  allow mqueue,
  allow io_uring,
  allow userns,
  allow mount,
  allow umount,
  allow pivot_root,
  allow dbus,

  # stacked like this due to no-new-privs restriction
  # this will stack a target profile against bwrap and unpriv_bwrap
  # Ideally
  # - there would be a transition at userns creation first. This would allow
  #   for the bwrap profile to be tighter, and looser within the user
  #   ns. bwrap will still have to fairly loose until a transition at
  #   namespacing in general (not just user ns) is available.
  # - there would be an independent second target as fallback
  #   This would allow for select target profiles to be used, and not
  #   necessarily stack the unpriv_bwrap in cases where this is desired
  #
  # the ix works here because stack will apply to ix fallback
  # Ideally we would sanitize the environment across a privilege boundry
  # (leaving bwarp into application) but flatpak etc use environment glibc
  # sanitized environment variables as part of the sandbox setup.
  allow pix /** -> &bwrap//&unpriv_bwrap,

  # the local include should not be used without understanding the userns
  # restriction.
  # Site-specific additions and overrides. See local/README for details.
  include if exists <local/bwrap-userns-restrict>
}

# The unpriv_bwrap profile is used to strip capabilities within the userns
profile unpriv_bwrap flags=(attach_disconnected,mediate_deleted) {
  # not allow all, to allow for pix stack
  allow file rwlkm /{**,},
  allow network,
  allow unix,
  allow ptrace,
  allow signal,
  allow mqueue,
  allow io_uring,
  allow userns,
  allow mount,
  allow umount,
  allow pivot_root,
  allow dbus,

  # bwrap profile does stacking against itself this will keep the target
  # profile from having elevated privileges in the container.
  # If done recursively the stack will remove any duplicate
  allow pix /** -> &unpriv_bwrap,

  audit deny capability,

  # the local include should not be used without understanding the userns
  # restriction.
  # Site-specific additions and overrides. See local/README for details.
  include if exists <local/unpriv_bwrap>
}
