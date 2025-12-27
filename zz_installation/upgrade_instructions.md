- [Upgrade from version 3.1.0 and later](#upgrade-from-version-310-and-later)
- [Upgrade from version 3.0.0](#upgrade-from-version-300)
- [Upgrade from version 2.3.4 and later](#upgrade-from-version-234-and-later)
- [Upgrade old installations](#upgrade-old-installations)

Please read in the release notes of the releases possible additional special instructions.

---

# Upgrade from version 3.1.0 and later
Click `Update now` in the yellow box displayed at the bottom of the footer.
This box is shown when the system has detected that a new release is available in GitHub.

# Upgrade from version 3.0.0
1. Download from Git the latest upgrade_manager.php ([www/htdocs/update_manager.php](https://github.com/ABCD-DEVCOM/ABCD/blob/master/www/htdocs/update_manager.php) )
2. Copy the downloaded file to the Document root of your installation  
    1. In Linux typical  `/opt/ABCD/htdocs`
    2. Check that `update_manager.php` is readable by your webserver
3. Run ABCD, login as administrator, perform some arbitrary actions to ensure that ABCD detects the new version
4. The footer shows a yellow bar with text like `Update now (v3.1.0) ABCD is available`. Click on the link `Update Now`
5. The version of the Update  manager should be: **v4.3** or higher
6. The Update manager will guide you through the upgrade process

---

# Upgrade from version 2.3.4 and later
This release contains an **Update Manager**: an interactive tool that performs an update to the last release.  
Can be started with `<url_of_yourinstallation>/update_manager.php`.  See [Update Manager](https://github.com/ABCD-DEVCOM/ABCD/pull/567) for functionality.
Alternative start is by click `Update now` in the yellow box displayed at the bottom of the footer.
This box is shown when the system has detected that a new release is available in GitHub.
![update_warning](https://github.com/user-attachments/assets/e7f7d64d-0719-4473-aa77-3ef3894f393e)


This tool updates several code folders and preserves important code configuration files.
Please read the release notes for additional release related actions by the administrator.

---

# Upgrade old installations

This chapter intends to give directions for upgrading an (old) ABCD installation to the latest status.
As there are many known different older versions, each with their own local modifications, adaptions and extensions it is impossible to give detailed step by step instructions.
This file intends to give a <i>guideline</i> for the upgrade process to the last status.

### Preparation

1. Create a full backup of the code and/or webserver
2. Prepare your installation. See [Installation instructions](https://github.com/ABCD-DEVCOM/ABCD2/blob/master/zz_installation/installation_instructions.md) for details
   - Check Prerequisites (e.g. versions of PHP and other components)
   - Download ABCD 
   - Unpack downloaded archive
   - Preprocess the downloaded folders.

### Actual upgrade

1. Ask or force the client to log-off and close or shutdown the webserver
2. Copy folders **htdocs** and **cgi-bin** to the web server.  
It is recommended to delete these folders in the webserver first.
This ensures that files no longer present in the downloaded archives do not pollute your installation
3. Ensure that possible prerequisites are copied to the correct location in the web server (e.g. tika)
4. Perform the [Upgrade post processing](#upgrade-post-processing). Ensure that you have a correct `htdocs/central/config.php`
5. For Linux installations
   - Change ownership of the folders to the owner/group of the webserver (`chown -R ...`)
   - Change protection of the folders to correct values (`chmod -R ...`)
6. Restart the webserver/web server service

---

## Upgrade post processing

### Modifications in code

#### File config.php

The main configuration file `htdocs/central/config.php` is no longer distributed (so it will no longer overwrite local modified files).  
The distribution contains now `htdocs/central/config.php.template` with a default content for the `htdocs/central/config.php` suitable for Windows and Linux

&rArr; Merge your existing `config.php` with the template file

### Modifications in the database files

#### In file bases/www/epilogoact.pft

Remove the script that sets the current record number/number of records in the Data Entry heading
```
<script> ....
    top.mfn='v1001'
    top.maxmfn='v1002'
    top.menu.forma1.ir_a.value=...
</script>
```  
The complete script must be deleted from this file to ensure correct values.

Remove the following html code at the end of the script:
```
'</form>
</body>
</html>'
```

#### In file bases/www/prologoact.pft

Change:  
```
<script type="text/javascript" src="/iah/js/highlight.js"></script>
```  
into  
```
<script type="text/javascript" src="/central/dataentry/js/highlight.js"></script>
```

#### Modifications in <database>/dr_path.def

Existing `dr_path.def` files must edited. With a text editor or by menu item `Advanced database settings (dr_path.def)`
- The allowed values for `unicode` are 0 or 1

#### New configuration file <database>/def/listoflabels.tab

This table lists the available label names.
Existing installations with enabled label/barcode functionality have to create this file.  
`Update database Definitions` &rArr; `Table with label & barcode names`
