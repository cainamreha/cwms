<?php
namespace Concise;



/**
 * Alle Angaben laut MySQL 5.0 Dokumentation 
 * (www.mysql.com)
 * 
 * 
 */

#$setTypesArray = array("ENUM","SET");

#$numericTypesArray = array("TINYINT","SMALLINT","MEDIUMINT","BIGINT","INT",);
#$complexNumericTypesArray = array("FLOAT","DOUBLE","DECIMAL");

/**********************************************/
/****          NUMERIC TYPES               ****/
/**********************************************/
//TINYINT
define('TINYINT_MIN',-128);
define('TINYINT_MAX',127);
define('TINYINT_UNSIGNED_MIN',0);
define('TINYINT_UNSIGNED_MAX',255);

//SMALLINT
define('SMALLINT_MIN',-32768);
define('SMALLINT_MAX',32767);
define('SMALLINT_UNSIGNED_MIN',0);
define('SMALLINT_UNSIGNED_MAX',65535);

//MEDIUMINT
define('MEDIUMINT_MIN',-8388608);
define('MEDIUMINT_MAX',8388607);
define('MEDIUMINT_UNSIGNED_MIN',0);
define('MEDIUMINT_UNSIGNED_MAX',16777215);

//INT
define('INT_MIN',-2147483648);
define('INT_MAX',2147483647);
define('INT_UNSIGNED_MIN',0);
define('INT_UNSIGNED_MAX',4294967295);


//BIGINT
define('BIGINT_MIN',-9223372036854775808);
define('BIGINT_MAX',9223372036854775807);
define('BIGINT_UNSIGNED_MIN',0);
define('BIGINT_UNSIGNED_MAX',18446744073709551615);

/** KOMPLEXE NUMERIC-TYPEN **/

//FLOAT
define('FLOAT_MIN',-3.40282e+038);
define('FLOAT_MAX',3.40282e+038);

//DECIMAL
//ist abhängig von der Konfiguration 
// -> decimal(20,4) hätte z.B. 20 Stellen davon 4 nach dem Komma.

//DOUBLE
//hat ebenfalls keine Beschränkung nach oben oder unten
//und keine weiteren Vorgaben
define('DOUBLE_MIN',-1.7976931348623157E+308);
define('DOUBLE_MAX',1.7976931348623157E+308);


/**********************************************/
/****        DATE AND TIME TYPES           ****/
/**********************************************/

#$dateTypesArray = array("DATETIME","DATE","TIMESTAMP","TIME","YEAR");

//DIE REGEXPRESSIONS HIER FESTLEGEN
//[Jeweils YEAR-MONTH-DAY]
/**** DATETIME *****/
//Muster: 0000-00-00 00:00:00  
define('DATETIME_REGEXP','/^[0-9]{4}-[0-9]{2}-[0-9]{2}\s[0-9]{2}:[0-9]{2}:[0-9]{2}$/');

/**** DATE *****/
//wird nicht benötigt

/**** TIMESTAMP *****/
//Muster: 00000000000000 (maximal 14-stellig, minimal 0 stellig)
define('TIMESTAMP_REGEXP','/^[0-9]{0,14}$/');

/**** TIME *****/
//Muster: 00:00:00
define('TIME_REGEXP','/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/');

/**** YEAR *****/
//Muster: 0000
define('YEAR_REGEXP','/^[0-9]{4}$/');



//ENUM und SET 
//muss nicht getestet werden, wenn das Eingabe-Formular mit
//Checkboxes und Radiobuttons bzw. Selectliste aufgebaut wird.



/**********************************************/
/****            STRING TYPES              ****/
/**********************************************/

#$stringTypesArray = array("VARCHAR","CHAR","TINYTEXT","MEDIUMTEXT","LONGTEXT","TEXT");

//VARCHAR
define('VARCHAR_MAXCHARS',pow(2,16)-1); // 2^16 -1 Zeichen
//CHAR
define('CHAR_MAXCHARS',pow(2,8)-1); //2^8 -1 Zeichen 

//TINYTEXT
define('TINYTEXT_MAXCHARS',pow(2,8)-1); //2^8 - 1 Zeichen

//MEDIUMTEXT
define('MEDIUMTEXT_MAXCHARS',pow(2,24)-1); //2^24 - 1  Zeichen

//LONGTEXT
define('LONGTEXT_MAXCHARS',pow(2,32)-1); //2^32 - 1 Zeichen

//LONGTEXT
define('TEXT_MAXCHARS',pow(2,16)-1); //2^16 - 1 Zeichen


/**********************************************/
/****       BIG BYTE-FILE TYPES          ****/
/**********************************************/
#$byteTypesArray = array("TINYBLOB","MEDIUMBLOB","LONGBLOB","BLOB","VARBINARY","BINARY");
//alle Angaben in Bytes!

//TINYTEXT
define('TINYBLOB_MAXBYTES',pow(2,8)-1); //2^8 - 1 Bytes

//MEDIUMTEXT
define('MEDIUMBLOB_MAXBYTES',pow(2,24)-1); //2^24 - 1  Bytes

//LONGTEXT
define('LONGBLOB_MAXBYTES',pow(2,32)-1); //2^32 -1  Bytes

//LONGTEXT
define('BLOB_MAXBYTES',pow(2,16)-1); //2^16 -1 Bytes

//BINARY
define('BINARY_MAXBYTES',pow(2,8)-1);  // wie CHAR 2^8 -1 (aber hier Bytes) 

//VARBINARY
define('VARBINARY_MAXBYTES',pow(2,16)-1); // wie VARCHAR 2^16 -1 (aber hier Bytes)
