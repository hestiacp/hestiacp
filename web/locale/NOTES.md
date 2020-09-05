# Hestia Update guide for improved language system

### Create new translation 

Open hestiacp.pot in a .po(t) editor for example Poedit-2, gtranslator or online tools like https://localise.biz/free/poeditor
Translate the file. Make sure you escape " with a slash. Save and update it in the folder. "iso"/LC_MESSAGES Please be aware that English (en) becomes en and Dutch (nl) becomes nl

For example I create the file for Spanish:

Folder becomes locale/nl/LC_MESSAGES

Run the command ./hst-convert-po2mo.sh language (./hst-convert-po2mo.sh nl)

### Upadate translation

Open .po file in /locale/{language}/LC_MESSAGES .po(t) editor 

### Add / remove language strings

Run the command ./hst_scan_i18n.sh
