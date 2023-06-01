#!/usr/bin/env bash

###
## Initialize the plugins structure if it does not exist.
###
init_plugins_conf_if_not_exists() {
	if [[ ! -f "$HESTIA/conf/plugins.json" ]]; then
		echo -n "{}" > "$HESTIA/conf/plugins.json"
	fi

	if [[ ! -d "$HESTIA/plugins" ]]; then
		mkdir -p "$HESTIA/plugins"
	fi

	if [[ ! -d "$HESTIA/web/plugin" ]]; then
		mkdir -p "$HESTIA/web/plugin"
	fi
}

###
## Get the name of a plugin from its source directory.
###
plugin_get_name_from_source() {
	local plugin_dir="$1"

	local plugin_name

	if [[ -f "$plugin_dir/hestiacp.json" ]]; then
		plugin_name="$(get_json_index "name" "$plugin_dir/hestiacp.json")"

		if [[ -n "$plugin_name" && "$plugin_name" != "null" && -n "$(echo "$plugin_name" | grep -E "^[a-z0-9_\-]+$")" ]]; then
			echo "$plugin_name"
		fi
	fi
}

###
## Checks if the current version of Hestia meets the plugin requirement and returns the required if the current one does not.
###
plugin_check_hestia_min_version() {
	local plugin_dir="$1"

	local hestia_version min_version

	if [[ -f "$plugin_dir/hestiacp.json" ]]; then
		hestia_version="$(sed -En "s/^VERSION='(.*)'/\1/p" "$HESTIA/conf/hestia.conf")"
		min_version="$(get_json_index "min-hestia" "$plugin_dir/hestiacp.json")"

		if [[ "$min_version" && "$min_version" != "null" && "$(php -r "echo version_compare(\"$hestia_version\", \"$min_version\", '<') ? '1' : '';")" ]]; then
			echo "$min_version"
		fi
	fi
}

###
## Looks for scripts that may conflict with existing scripts
###
plugin_check_conflicts() {
	local plugin_name="$1"
	local tmp_bin_dir="$2"

	if [[ ! -d "$tmp_bin_dir" || -z "$(ls "$tmp_bin_dir/v-plugin-"* 2> /dev/null)" ]]; then
		return
	fi

	local bin_name real_script_path error_msg
	local bin_conflicts=""
	for f in "$tmp_bin_dir/v-plugin-"*; do
		bin_name="$(basename -- "$f")"

		if [[ -e "$HESTIA/bin/$bin_name" ]]; then
			# Checks if the script is from an already installed version of the same plugin
			real_script_path="$(readlink -f "$HESTIA/bin/$bin_name")"
			if [[ "$real_script_path" != "$HESTIA/plugins/$plugin_name/bin/$bin_name" ]]; then
				if [[ -n "$bin_conflicts" ]]; then
					bin_conflicts+=", "
				fi

				bin_conflicts+="$bin_name"
			fi
		fi
	done

	if [[ -n "$bin_conflicts" ]]; then
		error_msg="The following scripts conflict with scripts that already exist on the system: $bin_conflicts"
		check_result "$E_INVALID" "$error_msg"
	fi
}

###
## Get information about a plugin using a github URL.
###
plugin_get_from_github() {
	local repo_url="$1"
	local info="$2"

	local repo_name repo_owner repo_branch plugin_conf

	repo_url="$(echo "$repo_url" | sed -E "s|.git$||")"

	if [[ -n "$(echo "$repo_url" | grep -E "^https://github.com/[^/]*/[^/]*/tree/[^/]*$")" ]]; then
		# Get from another branch
		repo_name="$(echo "$repo_url" | sed -En "s|.*/(.*)/tree/.*|\1|p")"
		repo_owner="$(echo "$repo_url" | sed -En "s|.*/(.*)/.*/tree/.*|\1|p")"
		repo_branch="$(basename -- "$repo_url")"
		repo_url="$(echo "$repo_url" | sed -En "s|(.*)/tree/.*|\1|p")"
	elif [[ -n "$(echo "$repo_url" | grep -E "^https://github.com/[^/]*/[^/]*$")" ]]; then
		# Get from master
		repo_name="$(basename -- "$repo_url")"
		repo_owner="$(echo "$repo_url" | sed -En "s|.*/(.*)/.*|\1|p")"
		repo_branch="master"
	else
		echo "Invalid Github URL" >&2
		return
	fi

	if [[ -z "$(curl -L -I -s "$repo_url" | grep -E "HTTP/(.*)200")" ]]; then
		echo "Github repository not found" >&2
		return
	fi

	if [[ "$info" == "root_url" ]]; then
		echo "$repo_url"
	elif [[ "$info" == "repo_name" ]]; then
		echo "$repo_name"
	elif [[ "$info" == "repo_owner" ]]; then
		echo "$repo_owner"
	elif [[ "$info" == "branch" ]]; then
		echo "$repo_branch"
	elif [[ "$info" == "archive" ]]; then
		if [[ -n "$(curl -L -I -s "$repo_url/archive/$repo_branch.zip" | grep -E "HTTP/(.*)200")" ]]; then
			echo "$repo_url/archive/$repo_branch.zip"
		fi
	elif [[ "$info" == "raw_path" ]]; then
		echo "https://raw.githubusercontent.com/$repo_owner/$repo_name/$repo_branch"
	elif [[ -n "$info" ]]; then
		plugin_conf="$(curl "https://raw.githubusercontent.com/$repo_owner/$repo_name/$repo_branch/hestiacp.json" 2> /dev/null)"

		if [[ "$(echo "$plugin_conf" | jq -r '.' 2> /dev/null)" ]]; then
			get_json_index "$info" "$plugin_conf"
		fi
	fi
}

###
## Checks if there is an update available for the plugin and if so, returns the new version number.
###
plugin_check_update() {
	local plugin_name="$1"
	local new_version_path="$2"

	local new_version version plugin_repository

	plugin_name="$(echo "$plugin_name" | awk '{gsub(/^[ \t]+| [ \t]+$/,""); print $0 }')"

	if [[ -f "$HESTIA/plugins/$plugin_name/hestiacp.json" ]]; then
		version="$(get_json_index "version" "$HESTIA/plugins/$plugin_name/hestiacp.json")"
		plugin_repository="$(get_json_index "repository" "$HESTIA/plugins/$plugin_name/hestiacp.json")"

		if [[ "$new_version_path" && -f "$new_version_path/hestiacp.json" ]]; then
			new_version="$(get_json_index "version" "$new_version_path/hestiacp.json")"
		elif [[ "$(echo "$plugin_repository" | grep -E "^https://github.com/.*")" ]]; then
			new_version="$(plugin_get_from_github "$plugin_repository" "version")"
		fi

		if [[ -z "$version" || "$version" == "null" ]] \
			|| [[ "$new_version" && "$new_version" != "null" && "$(php -r "echo version_compare(\"$new_version\", \"$version\", '>') ? '1' : '';")" ]]; then
			echo "$new_version"
		fi
	fi
}

###
## Install a plugin using a local path.
###
plugin_install_from_path() {
	local plugin_source="$1"
	local update_if_exist="${2:-no}"

	local file_name plugin_name min_hestia
	file_name="$(basename -- "$plugin_source")"

	if [[ ! -d "$plugin_source" ]]; then
		check_result "$E_INVALID" "The source is not a directory"
	fi

	plugin_name="$(plugin_get_name_from_source "$plugin_source")"
	min_hestia="$(plugin_check_hestia_min_version "$plugin_source")"

	if [[ -z "$plugin_name" ]]; then
		check_result "$E_INVALID" "The source is not a Hestia plugin"
	elif [[ -n "$min_hestia" ]]; then
		check_result "$E_INVALID" "The plugin needs Hestia version $min_hestia or higher."
	elif [[ "${update_if_exist,,}" != "yes" && -d "$HESTIA/plugins/$plugin_name" ]]; then
		check_result "$E_INVALID" "There is already a plugin with that name"
	fi

	plugin_check_conflicts "$plugin_name" "$plugin_source/bin"

	# Remove old versions
	if [[ -d "$HESTIA/plugins/$plugin_name" ]]; then
		rm -rf "$HESTIA/plugins/$plugin_name"
	fi

	cp -a "$plugin_source" "$HESTIA/plugins/$plugin_name"

	plugin_configure "$plugin_name"

	final_plugin_name="$plugin_name"
}

###
## Install a plugin using a zip file.
###
plugin_install_from_zip() {
	local plugin_source="$1"
	local update_if_exist="${2:-no}"

	local remove_zip_after_install="no"

	local file_name plugin_name min_hestia tmp_dir delete_me sub_dir
	file_name="$(basename -- "$plugin_source" | sed -E "s|.zip$||")"

	# Download zip
	if [[ "$plugin_source" && ! -f "$plugin_source" &&
		"$(curl -L -I -s "$plugin_source" | grep -E "HTTP/(.*)200")" ]]; then
		curl -L -J "$plugin_source" -o "/tmp/$file_name.zip"
		plugin_source="/tmp/$file_name.zip"
		remove_zip_after_install="yes"
	fi

	if [[ ! -f "$plugin_source" ]]; then
		check_result "$E_INVALID" "The source is not a ZIP file"
	fi

	# Installation
	tmp_dir=$(mktemp -d -t "hestiacp-plugin_${file_name}.XXXXXXXXXX" -p "/tmp")
	unzip -q "$plugin_source" -d "$tmp_dir"
	if [[ "${remove_zip_after_install,,}" == "yes" ]]; then
		rm -rf "$plugin_source"
	fi

	# Check if files is in a subdirectory
	delete_me=""
	if (($(ls -1 "$tmp_dir" | wc -l) == 1)); then
		sub_dir="$tmp_dir/$(ls -1 "$tmp_dir")"

		if [[ -d "$sub_dir" ]]; then
			delete_me="$tmp_dir"
			tmp_dir="$sub_dir"
		fi
	fi

	plugin_name="$(plugin_get_name_from_source "$tmp_dir")"
	min_hestia="$(plugin_check_hestia_min_version "$tmp_dir")"

	if [[ -z "$plugin_name" ]]; then
		check_result "$E_INVALID" "The source is not a Hestia plugin"
	elif [[ -n "$min_hestia" ]]; then
		check_result "$E_INVALID" "The plugin needs Hestia version $min_hestia or higher."
	elif [[ "${update_if_exist,,}" != "yes" && -d "$HESTIA/plugins/$plugin_name" ]]; then
		check_result "$E_INVALID" "There is already a plugin with that name"
	fi

	plugin_check_conflicts "$plugin_name" "$tmp_dir/bin"

	# Remove old versions
	if [[ -d "$HESTIA/plugins/$plugin_name" ]]; then
		rm -rf "$HESTIA/plugins/$plugin_name"
	fi

	# Move to plugins dir
	mv "$tmp_dir" "$HESTIA/plugins/$plugin_name"

	# Delete empty dir
	if [[ "$delete_me" ]]; then
		rm -rf "$delete_me"
	fi

	plugin_configure "$plugin_name"

	final_plugin_name="$plugin_name"
}

###
## Run plugin settings after installation.
##
## * Add in Hestia plugins list
## * Execute hooks
## * Add plugin parts in the hestia environment
###
plugin_configure() {
	local plugin_name="$1"

	local plugin_data current_plugin_data type_config

	plugin_name="$(echo "$plugin_name" | awk '{gsub(/^[ \t]+| [ \t]+$/,""); print $0 }')"
	type_config="install"

	if [[ -z "$plugin_name" ]]; then
		check_result "$E_ARGS" "Plugin name is required"
	elif [[ -z "$(ls -A "$HESTIA/plugins/$plugin_name")" ]]; then
		#rm -rf "$HESTIA/plugins/$plugin_name"
		check_result "$E_INVALID" "Plugin is empty"
	fi

	# Get plugin data and add installation info
	plugin_data="$(get_json "$HESTIA/plugins/$plugin_name/hestiacp.json")"
	plugin_data="$(echo "$plugin_data" | jq -r ".enabled = true | .date = \"$(date +'%Y-%m-%d %H:%M:%S')\"")"

	# Check if plugin exist in hestia list to keep additional configurations
	current_plugin_data="$(get_json_index "$plugin_name" "$HESTIA/conf/plugins.json")"
	if [[ -n "$current_plugin_data" && "$current_plugin_data" != "null" ]]; then
		type_config="update"

		# Merge data
		plugin_data="$(echo "$current_plugin_data $plugin_data" | jq -s ".[0] + .[1]")"
	fi

	# Update hestia plugins list
	update_json_index "$plugin_name" "$plugin_data" "$HESTIA/conf/plugins.json"

	# Check post_install hook
	if [[ "$type_config" == "install" && -f "$HESTIA/plugins/$plugin_name/hook/post_install" ]]; then
		bash "$HESTIA/plugins/$plugin_name/hook/post_install" > /dev/null 2>&1
	fi

	# Execute configuration for plugin in the hestia environment
	$HESTIA/bin/v-rebuild-plugin "$plugin_name" > /dev/null 2>&1

	# Check if plugin has additional configurations
	if [[ -f "$HESTIA/plugins/$plugin_name/hook/post_enable" ]]; then
		bash "$HESTIA/plugins/$plugin_name/hook/post_enable" > /dev/null 2>&1
	fi
}
