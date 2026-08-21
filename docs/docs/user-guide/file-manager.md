# File Manager

To access the file manager, click the **<i class="fas fa-fw fa-folder-open"></i> Files** button in the top left.

The file manager Hestia uses is called FileGator. You can find more information about it on [their website](https://filegator.io/).

## What you can do

The file manager opens in your Hestia user home directory. From there you can browse your web, mail, and other user-owned files without using an FTP client.

Common actions include:

- Upload and download files.
- Create files and folders.
- Rename, copy, move, and delete files or folders.
- Edit supported text files in the browser.
- Compress files into an archive and extract supported archives.
- Change file permissions when your account owns the file.
- Search files from the current file manager view.

## Uploading files

Open the folder where the file should be stored, then use the file manager upload action. You can also drag files into the upload area when your browser supports it.

When uploading a website with many files, compress the files on your computer first, upload the archive, and extract it in the file manager. This is usually faster and more reliable than uploading many small files one by one.

If you upload a file with the same name as an existing file, Hestia's FileGator configuration allows the uploaded file to replace the existing one. Make sure you have a backup before uploading over important files.

## Editing files

The file manager can edit common text files such as `.txt`, `.css`, `.js`, `.html`, `.php`, `.py`, `.yml`, `.xml`, `.md`, `.log`, `.csv`, `.conf`, `.ini`, `.scss`, `.sh`, `.env`, `.htaccess`, `.twig`, `.tpl`, and `.yaml`.

For large files, binary files, or files with unusual character encoding, download a copy and edit it locally. Saving a file with the wrong encoding can corrupt its contents.

## Archives

Use archive actions to compress files before downloading or to extract uploaded website packages. Hestia's file manager integration supports FileGator archive actions and Hestia's server-side extraction helper for common archive formats.

When extracting an archive into a folder that already contains files, review the destination first. Existing files can be replaced depending on the archive contents and file manager action.

## Permissions

Use permission changes carefully:

- Files commonly use `644`.
- Folders commonly use `755`.
- Avoid setting files or folders to `777` unless you understand the security impact.

The file manager can only change permissions for files that your Hestia user is allowed to manage. If a file is owned by another system user, use the command line as an administrator to inspect ownership and permissions.

When changing permissions on a folder, review any recursive option before saving. Recursive changes can apply to everything below the selected folder.

## Hidden files

Many web applications use hidden dotfiles such as `.htaccess`, `.env`, or `.user.ini`. These files can affect redirects, PHP settings, credentials, and application behavior. Be careful when editing or deleting hidden files.

## Deleted files

The current Hestia file manager integration does not provide a recycle bin. Deleted files are removed from the account. Restore accidental deletions from a Hestia backup or another backup source.
