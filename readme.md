#CONCISE WMS


##Overview

Simple and concise website content management system

##Installation

Zunächst alle Verzeichnisse und Dateien in das Rootverzeichnis auf den Webserver Kopieren bzw. die Zip Datei dort durch Aufrufen von "unzip.php" entpacken.
Über den Webbrowser die Seite http://www.ihre-domain.de/install aufrufen.
Die Informationen zur Datenbank (DB-Host, DB-Name, DB-User, DB-Passwort) eingeben und "Datenbank anlegen" klicken.
Jetzt sollte das System installiert sein und Sie können sich zunächst mit dem Standardbenutzer (admin) einloggen.

User: admin
PW: concise

Vergessen Sie nicht, nach erfolgreicher Installation das Verzeichnis "install" im Root-Verzeichnis zu löschen.
Ändern Sie in jedem Fall auch das Passwort für den Standardbenutzer.



First copy all files to webserver's root directory or unzip files there, respectively, by calling "unzip.php".
In your web browser go to http://www.your-domain.com/install.
Type in your data base details (DB host, DB name, DB user, DB password) and click "Create database".
The system should now be properly installed. You can now login as default user (admin).

User: admin
PW: concise

Do not forget to delete the "install" directory within your server's root directory.
Also remember to change the password of the default user.





##Version 2.8

2016-09


###Neuerungen

- Medienordner umbenannt für klarere Struktur (mp3 => audio, movies => video / CC_MOVIE_FOLDER => CC_VIDEO_FOLDER)
- Medienordner flash entfernt (flash-Dateien jetzt im Ordner "video" / CC_FLASH_FOLDER => CC_VIDEO_FOLDER)
- Mediaplayer-Update auf jPlayer 2.9.2
- Video-Element update
- Neues Inhaltselement "cards"
- Neue Portfolio-Galerie 3 mit Tag-Filter-Funktion
- Neue FE-Events z.B. global.extend_styles_fe zur Erweiterung von Element-Styles (z.B. WOW-Plugin)
- jQuery knob eingeführt für Dashboard-Statistiken
- Tour (Kurzeinführung) für Adminseiten und FE-Mode
- Admin-Theme angepasst
- Daten- und Benutzersuche in Controlbar
- Edit-Bereich: Anzahl an Inhaltselementen pro Seite begrenzt (max input size)
- Custombox update auf v3.0.2
- Update check via Ajax-Aufruf (verhindert Hängen bei Nichterreichbarkeit des Update-Servers)


###New features:

- Media folders renamed for the sake of conveinience (mp3 => audio, movies => video / CC_MOVIE_FOLDER => CC_VIDEO_FOLDER)
- Medienordner flash entfernt (flash-Dateien jetzt im Ordner "video" / CC_FLASH_FOLDER => CC_VIDEO_FOLDER)
- Mediaplayer update to jPlayer 2.9.2
- Update video element
- New content element "cards"
- New portfolio gallery 3 with tag filter function
- New FE events like global.extend_styles_fe extending element styles (e.g. WOW-Plugin)
- jQuery knob introduced for dashboard statistics
- Tour (short introduction) for admin task pages and FE mode
- Adaption of admin theme
- Data and user search in control bar
- Edit task: reduced amount of elements per page (max input size)
- Custombox update to v3.0.2
- Update check via ajax call (prevents hanging due to non-available update server)




##Version 2.7

2016-08


###Neuerungen

- Update auf PHP7
- Update auf PHPMailer 5.2.14
- CC_CRYPT_KEY auf 16 Stellen umgestellt
- Verbessertes Frontend-Editing
- Browser Edge integriert in Stats
- Facebook Posts (Plugin) App-Verifizierung
- Update auf rGraph 4.54
- Statistiken verbessert (referer spam, Datei /inc/blacklist.txt)
- Statistikdarstellung ergänzt
- Einführung von image source sets und den Verzeichnissen "small" und "medium" für Bilder mit automatischer Bildversionenerstellung und den neuen Kontstanten "SMALL_IMG_SIZE" und "MEDIUM_IMG_SIZE" (v2.7.3)
- Enlarge- und Zoom-Option für Bilder eingeführt
- Videodateien in Galerie-Ordnern erlaubt (Dateiname ohne Erweiterung muss dem Namen des Poster-Images entsprechen)


###New features:

- Update to PHP7
- Update to PHPMailer 5.2.14
- CC_CRYPT_KEY changed to 16 digits
- Improved front-end editing
- Edge browser integrated in stats
- Facebook posts (plug-in) app verification
- Update to rGraph 4.54
- Statistics improved (referer spam, file /inc/blacklist.txt)
- Statistics added
- Introduction of image source sets and directories "small" and "medium" for images including automatic generation of image versions; new contstants "SMALL_IMG_SIZE" and "MEDIUM_IMG_SIZE" (v2.7.3)
- Enlarge and zoom options introduced for images
- Video files now allowed in gallery folders (file name w/o extension must match the name of the respective poster image)




##Version 2.6

2014-10 / 2015-12


###Neuerungen

- Implementierung des Symfony Event Dispatchers und Einführung von Events
- Einführung eines DB-Tabellen-Präfixes
- DB-Tabellen-Felder mit Editor-Bearbeitung erweitert von `text` auf `mediumtext`
- Einführung von sha256 und Salt zur sicheren Verschlüsselung
- DB Felder "salt" und "active" zu Tabelle "user" hinzugefügt
- Echtes Frontend-Editing für alle Inhaltselemente
- Neues Admin Theme (icomoon) mit verschiedenen Skins
- Responsive Admin und FE-Themes
- Neue Themeauswahl für Admin und FE
- Bootstrap als primäres FE-Framework
- Einführung von Skins für den Adminbereich
- Mehrfachlöschung bei verschiedenen Tasks eingeführt
- Mehrfacheingabe Textfelder mit Tags versehen
- Update auf TinyMCE 4.3.2
- Update auf TCPDF 6.2.8
- Plupload (Version 2.1.8) als Standard-Dateiuploadskript implementiert
- jQuery FullCallender für Datenmodule implementiert
- Tags für Bildergalerien eingeführt
- Neue Standard-Galerie-Typen
- Formvalidation (jQuery Formvalidator version 2.2.42)
- Social Login via Facebook (Plugin)


###New features:

- Implementation of Symfony's Event Dispatcher and introduction of events
- Integration of db table prefix
- db table fields with editor editing altered from `text` to `mediumtext`
- Implementation of sha256 and salt for secure encryption
- Fields "salt" and "active" added to table "user"
- True front-end editing for all content element types
- New admin theme (icomoon) with different skins
- Responsive admin and FE themes
- New theme selection for admin and FE
- Bootstrap as principal FE framework
- Multiple deletion added for certain tasks
- Tags added to multiple input textareas
- Update to TinyMCE 4.3.2
- Update to TCPDF 6.2.8
- Plupload (version 2.1.8) integrated as default upload script
- Integration of jQuery FullCallender for data modules
- Tags introduced for image galleries
- New default gallery types
- Formvalidation (jQuery Formvalidator version 2.2.42)
- Social login via Facebook (plug-in)




##Version 2.5

2014-01 / 2014-09


###Neuerungen

- Überarbeitung der Code-Struktur (OOP)
- Teilweise Ajaxifizierung
- Änderung der Linkstruktur von Datensätzen (Artikel etc.), hierdurch Verkürzung und suchmaschinenfreundlicher (www.seite-xyz/Kategoriealias/Artikelalias-catID[anp]dataID
- Datenmodul-Objekte jetzt mit mehrsprachigen Angaben (z.B. Title-Tag bei Bildern)
- Benutzer können Benutzerbild hochladen
- Option "über neue Kommentare benachrichtigen" bei Kommentaren hinzugefügt (Tabellenspalten: "userid", "url", "notify")
- Formulare können aktiv/inaktiv (field: `active`) gesetzt werden und haben ein optionales Ablaufdatum (field: `end_date`)
- Als Newsletterempfänger können zusätzliche E-Mailsadressen von nicht registrierten Benutzern eingegeben und optional in der Tabelle "user" gespeichert werden (automatisches Anlegen von Newsletter-Benutzern)
- Integration von elFinder als alternativer Dateimanager
- Integration von Codemirror als (html/css/js) editor in elFinder und im Templatebreich
- Integration von Codemagic als TinyMCE-Plugin
- Integration von HeadJS als Scriptloader
- Integration von Modernizr
- Html5Shiv separat zuschaltbar (settings.inc.php)
- Neue Verzeichnisstruktur
- Verzeichnisschutz via htaccess
- Die Website lässt sich mit einem Click vom Live-Modus in den Wartungsmodus versetzen
- Upgrade auf TinyMCE 4
- Implementierung einer LiveUpdate-Funktion
- Browser Version in Tabelle "log" hinzugefügt
- RGraph für Statistik-Diagramme implementiert (Ersatz für jpGraph)
- Kontextmenü (Rechtsklick) für FE-Editing
- Bildergalerien: Bilddaten-Tabelle ausgegliedert ("galleries_images", -> Normalisierung); Spalten hinzugefügt zu "galleries": "create_date", "active", "tags" und "name_xy", zu "galleries_images": "gallery_id" (ersetzt "gallery_name"), "img_tags"
- Update auf jQuery 1.11.0
- Update auf jQuery UI 1.10.4
- canonical url (optional, Tabelle "pages") hinzugefügt
- Page copy (Tabelle "pages") Seiten-Duplikat hinzugefügt, wenn aktiviert, werden Ihalte der Seite unter canonical url eingelesen
- Elternrootmenü (Typ "parroot") hinzugefügt
- Plug-ins können deaktiviert werden



###New features:

- Rearrangement of code structure (OOP)
- Partial ajaxification
- Link structure of data modules (Artikel etc.) changed. Thus reduced search engine friendly url-size (www.page-xyz/Category-alias/Articel-alias-catID[anp]dataID
- Data module objects now multilingual (e.g., title tag of images)
- Upload of user image enabled
- Option "notify me of new comments" added in comments module (data base fields: "userid", "url", "notify")
- Forms can be set active/inactive (field: `active`) and be given an optional expirery date (field: `end_date`)
- Additional email addresses can be specified as newsletter receipients in addition to registered users and can optionally be saved to table "user" (a new newsletter user is created automatically)
- Implementation of elFinder as a file manager alternative
- Implementation of Codemirror as a (html/css/js) editor for elFinder an within template module
- Integration of Codemagic as a TinyMCE-Plugin
- Integration of HeadJS as a script loader
- Integration of Modernizr
- Html5Shiv can be enabled separately (settings.inc.php)
- New directory structure
- Directory protection via htaccess
- Website can be switched from live mode to maintenance mode by one click
- TinyMCE 4 upgrade
- Implementation of a live update module
- Browser version added to "log" table
- RGraph implemented for statistics diagrams (replaces jpGraph)
- Context menu (rigth click) for FE editing
- Galleries: extra images table ("galleries_images", -> normalization); columns added to "galleries": "create_date", "active", "tags" and "name_xy", to "galleries_images": "gallery_id" (replaces "gallery_name"), "img_tags"
- Update to jQuery 1.11.0
- Update to jQuery UI 1.10.4
- canonical url (optional, table "pages") added
- page copy (table "pages") page ducplicate added; if activated, contents of the page given under canonical url are included
- Parent root menu (Typ "parroot") added
- Plug-ins can be inacitvated



##Version 2.4.1

2013-07


###Neuerungen

- Online-Status einer Seite bei Neuanlegung einstellbar
- File-Upload jetzt auch über listBox möglich
- Theme-Auswahl über Galerie. Vorschau-Modus im Frontend
- feButtons mit Icon
- feMode angepasst (Elemente bei Hovering)
- Bot-Liste in eigene Datei "inc/botlist.inc.php" ausgelagert zur einfacheren Wartung (include in class.Log.php)
- Formular für Kommentare wird jetzt vor der Kommentarliste angezeigt
- Spalte "userid" bei Tabelle "comments" hinzugefügt (nach "author"); falls geloggter Benutzer, wird diese bei Kommentaren eingetragen
- Datei sitemap.xml wird (falls vorhanden) um Dateneintrag-Url (z.B. News) erweitert, wenn im Bearbeitungsmodus auf Eintrag veröffentlichen geklickt wird
- Analytics-Code kann unter Einstellungen in head oder body eingebunden werden
- Analytics-Datei umbenannt (access/js/analytics.js)


###New features:

- Online state of a page can be set while adding a new page
- File-Upload module available in listBox
- Theme selection via gallery. Preview mode in front-end
- feButtons got an icon
- feMode adapted (elements on hovering)
- Bot list included in distinct file "inc/botlist.inc.php" for more conveinient maintenance (include in class.Log.php)
- The comments form is now shown inline with comments list
- Column"userid" added to table "comments" (after "author"); the userid of a logged user will be stored along with comments
- The file sitemap.xml (if present) is extended by a data url (e.g., news), when publish entry is clicked in editing mode
- Analytics dode can be included in head or body tag in settings
- Analytics file has been renamed (access/js/analytics.js)



##Version 2.4

2013-05


###Neuerungen

- HTML5 (unter Settings) zuschaltbar
- Individuelle Formulare als Poll einsetzbar
- Bilder jetzt auch in Höhe skalierbar
- Anzeigen von Bildmaßen in Vorschau
- Https-Protokoll für Systemseiten einschaltbar
- Umbenennung von PROJECT_DOCUMENT_ROOT in PROJECT_DOC_ROOT und Definition von Systempfaden (ADMIN_HTTP_ROOT, SYSTEM_HTTP_ROOT und SYSTEM_DOC_ROOT) zur Unterscheidung Frontend- und Backend-Http-Protokollen
- Datei paths.php in settings.php integriert
- Vordefinierte Sprachen im Ordner "languages" jetzt direkt installierbar
- Ordner "sitegrafx" jetzt "system/themes/current/img"
- TinyMCE-MediaBrowser zur Auswahl von Bild- und Videodateien über die ListBox implementiert
- Spalte für eigene Benutzergruppen "own_groups" in Tabelle "user" eingefügt; Benutzer können damit mehreren (eigenen) Gruppen gleichzeitig angehören
- Filesordner auch in Edit- und Module-Bereich als Upload-Ziel auswählbar
- Filesordner als Standardordner aktivierbar (checkbox automatisch gecheckt und Ordnerauswahl eingeblendet)
- Datei "edit_step2.inc.php" aufgeteilt
- Datei "modules.inc.php" aufgeteilt
- Inhaltselemente können in Form von Plug-ins eingebunden werden. Plug-ins werden im Ordner "/plugins" gespeichert (Plug-in-Name = Ordnername) und erfordern die Dateien "config_pluginname.inc.php" und "create_pluginname.inc.php".
- Modul-Daten (z.B. News) sind jetzt mit Tabelle "user" über "author_id" bzw. "userid" verknüpft.
- Tags zu Moduldaten werden, so bereits vergeben, ausgelesen und zur Auswahl angeboten.
- Meta-Tag "robots" (index,follow) im Editbereich für jede Seite einstellbar.


###New features:

- HTML5 document standard can be enabled (in settings)
- Individual forms can be used as poll
- Scaling of image height enabled
- Image preview contains size data
- Https protocol can be enabled for system's pages
- PROJECT_DOCUMENT_ROOT renamed to PROJECT_DOC_ROOT and definition of system paths (ADMIN_HTTP_ROOT, SYSTEM_HTTP_ROOT and SYSTEM_DOC_ROOT) to allow distinct usage of frontend and backend http-protocols
- File paths.php combined with settings.php
- Predefined languages in folder "languages" can now be directly installed
- Directory "sitegrafx" now is "system/themes/current/img"
- TinyMCE media browser function for selection of image and video files via ListBox installed
- Column "own_groups" added to table "user"; a user can now belong to multiple (own) user groups
- Files folder can now function as an upload target in edit and modules area
- Files folder can be set as default upload folder (checkbox automatically activated and folder selection displayed by default)
- File "edit_step2.inc.php" has been split
- File "modules.inc.php" has been split
- Content elements can be included as plug-ins. Plug-ins are included in the directory "/plugins" (Plug-in name = folder name). The following files are required "config_pluginname.inc.php" and "create_pluginname.inc.php".
- Module data are now linked to table "user" by "author_id" and "userid", respectively.
- Module data tags are now directly selectable once present.
- Meta tag "robots" (index,follow) can now be set for each page in edit section.



##Version 2.3

2012-08


###Neuerungen

- File-Upload optional in files-Ordner anstelle der Default-Ordner möglich
- Umschalten zwischen Default- und files-Ordner und Auswahl von Dateien aus dem files-Ordner für Bilder und Dokumente ermöglicht
- Direktauswahl von Bildern und Bildergalerien und Bearbeitung von HTML-Elementen im Frontend-Editing-Modus
- Auswahl von Themes über das Frontend
- Update von TinyMCE auf Version 3.5.5
- Umschalten von Artikel-/Nachrichten-/Termin-Listenansicht
- Sortieren von Artikel/Nachrichten/Terminen per Drag & Drop implementiert
- Inhaltssuche für den Adminbereich implementiert
- Newslettermodul erweitert für Inline-Attachment von Bildern über TinyMCE
- Ajax-Suche: Suchbegriffe werden vorgeschlagen
- Freies Sortieren von Inhaltselementen im Frontend-Editing-Modus über jQuery sortable
- Eigene Dialogboxen für JavaScript-Meldungen
- HTML-Cache für Seiten (ohne Formulare etc.) aktivierbar


###New features:

- Alternative file Upload to files folder instead of a default folder enabled
- Switching between the default folder and the files folder for file selection of images and documents
- Direct selection of images and image galleries and editing of html code elements within frontend editing mode
- Theme selection within frontend
- Update of TinyMCE to version 3.5.5
- Toggling of list view for articles/news/planner items
- Sorting of articles/news/planner items by drag & drop
- Content search for admin area implemented
- Newsletter module extended by the possibility of inline attachment of images via TinyMCE
- Ajax search: a list of potential search terms is offered
- Free sorting of content elements within front end editing mode via jQuery sortable
- Custom dialogs for javascript dialog boxes
- HTML cache can be enabled for pages (e.g., without forms)



##Version 2.2

2012-02


###Neuerungen

- Anpassung der Rechteverwaltung; Zugrifssrechte für Seiten und Moduldaten sind jetzt für mehrere Benutzergruppen erteilbar
- Modul für die Anzeige und Bearbeitung von Formular-Daten (autoform) eingerichtet
- Sortierung, Filterung und Suche von Gallerie (-bildern) und Dateien (z.B. bei Auswahl über listBox-Popup) ermöglicht
- Sortierung von Sprachen eingerichtet
- lesbare Tooltips im Backend und FE-Modus
- Anpassung des Layouts für den Adminbereich
- Inhaltselement "menu" für eindimensionale Linklisten hinzugefügt
- Inhaltselement "mediaplayer" für das Einbinden von HTML5 Audio-/Videodateien und -Playlists
- Ausblenden einzelner Bilder von Bildergalerien (ohne Löschen) ermöglicht
- Cron-Job um wöchentliches entfernen mutmaßlicher Bot-Einträge erweitert (Ausführung: Montags)
- Datenmodule um Option zur Festlegung einer alternativen Template-Datei erweitert
- Umbennenen von Dateien ermöglicht


###New features:

- adjustment of rights; access to web pages and module data can now be granted to multiple user groups
- module for displaying and editing of form data (autoform) established
- sorting, filtered view and searching of gallery (images) and files (e.g., as selected within listBox popup) enabled
- sorting of languages enabled
- radable tooltips for backend and FE-Mode
- backend layout modified
- content element "menu" for the generation of one-dimensional link lists added
- content element "mediaplayer" for HTML5 based embedding of audio and video data/play lists added
- disabling option of images within galleries implemented
- Weekly cleaning of log data from potential bot entries added to cron jobs (conducted mondays)
- Extension of data modules by the possibility to optionally define an alternative template filename
- Renaming of files enabled



##Version 2.1

2011-11


###Neuerungen

- Implementierung des Moduls Forms für die Verwaltung individueller Formulare im Backend
- Implementierung eines FormMailers für den Versand von Formulardaten aus individuellen Formularen
- Implementierung der Klassen pdfMaker und TCPDF zur automatischen Erstellung von pdf-Dateien
- Implementierung der Klasse phpMailer für den Versand von E-Mails via SMTP/sendmail/mail() als Ersatz für reine php mail()-Funktion
- Ergänzung der Benutzerdaten-Tabelle um Felder für Benutzerdetails (`last_name` bis `company`)
- Sortieren von Seiten-/Templateinhalten und Datenmodul-Objekten mit jQuery UI sortable implementiert
- Umbenennen von Bildergallerien (Ordnername) ermöglicht
- Mehrfachlöschung bei Gästebuch/Kommentareinträgen ermöglicht (Backend)
- Anpassung des Backend-Layouts (erweitert auf 960px)
- Verwaltung von unbekannten user agents (DB-Tabelle log_bots)


###New features:

- Implementation of the module forms allowing administration of individual forms within the backend
- Implementation of a FormMailer for the purpose of sending form data from an individual form via email
- Implementation of the classes pdfMaker and TCPDF which allow for automated generation of pdf files
- Implementation of the phpMailer class for automated sending of emails via SMTP/sendmail/mail(), replaces sole php mail() function
- Fields for user details have been added to the user table (`last_name` through `company`)
- Sorting of page/template content elements and of data module objects via jQuery UI sortable enabled
- Renaming of picture galleries (folder name) enabled
- multiple deleting options enabled for guest book and comments (backend)
- backend layout modified (extended to 960px)
- management of unknown user agents (db table log_bots)



##Version 2.0


2011-10


###Neuerungen

- Echtes Frontend-Editing für erweiterte Textfelder mittels Ajax
- Auto Formular Funktion für die Einbindung automatisch erstellter Formulare via MySQL Tabelle
- Theme-Editing erlaubt das globale Ändern von Theme-Farben im Backend
- Einheitliche Einbindung von jQuery/jQuery UI (-Versionen/-Themes); Einstellung in settings.php



###New features:

- True frontend editing for extended text fields using Ajax
- Auto form function to create automatically generated forms via MySQL table
- Theme editing allows global changing of theme colors in backend
- Standardized embedding of jQuery/jQuery UI (versions/themes); settings changed in settings.php
