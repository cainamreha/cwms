<?php
namespace Concise;



/**
 * Klasse Keyword-Generator
 *
 */

class Keygen
{

	/**
	 * Ermittelt Keywords aus einem Text
	 * 
     * @param	string	Text, aus dem Keywords generiert werden sollen
     * @param	string	Sprache für Stopwortliste
     * @param	int		Anzahl an auszugebenden Keywords  (default = 12)
	 * @access	public
	 * @return	string
	 */
	public static function getKeywords($text, $lang = "de", $number = 12)
	{
		
		if($lang == "de")
			$stopwords = array(
				"ab",  "bei",  "da",  "deshalb",  "ein",  "für",  "finde",  "haben",  "hier",  "ich",  "ja","kann",  "machen",  "muesste",  "nach",  "oder",  "seid",  "sonst",  "und",  "vom",  "wann",  "wenn","wie",  "zu",  "bin",  "eines",  "hat",  "manche",  "solches",  "an",  "anderm",  "bis",  "das",  "deinem", "demselben",  "dir",  "doch",  "einig",  "er",  "eurer",  "hatte",  "ihnen",  "ihre",  "ins",  "jenen", "keinen",  "manchem",  "meinen",  "nichts",  "seine",  "soll",  "unserm",  "welche",  "werden",  "wollte", "während",  "alle",  "allem",  "allen",  "aller",  "alles",  "als",  "also",  "am",  "ander",  "andere", "anderem",  "anderen",  "anderer",  "anderes",  "andern",  "anders",  "auch",  "auf",  "aus",  "bist", "bsp.",  "daher",  "damit",  "dann",  "dasselbe",  "dazu",  "daß",  "dein",  "deine",  "deinen", "deiner",  "deines",  "dem",  "den",  "denn",  "denselben",  "der",  "derer",  "derselbe", "derselben",  "des",  "desselben",  "dessen",  "dich",  "die",  "dies",  "diese",  "dieselbe", "dieselben",  "diesem",  "diesen",  "dieser",  "dieses",  "dort",  "du",  "durch",  "eine",  "einem", "einen",  "einer",  "einige",  "einigem",  "einigen",  "einiger",  "einiges",  "einmal",  "es",  "etwas", "euch",  "euer",  "eure",  "eurem",  "euren",  "eures",  "ganz",  "ganze",  "ganzen",  "ganzer", "ganzes",  "gegen",  "gemacht",  "gesagt",  "gesehen",  "gewesen",  "gewollt",  "hab",  "habe", "hatten",  "hin",  "hinter",  "ihm",  "ihn",  "ihr",  "ihrem",  "ihren",  "ihrer",  "ihres", "im",  "in",  "indem",  "ist",  "jede",  "jedem",  "jeden",  "jeder",  "jedes",  "jene",  "jenem", "jener",  "jenes",  "jetzt",  "kein",  "keine",  "keinem",  "keiner",  "keines",  "konnte",  "könnten", "können",  "könnte",  "mache",  "machst",  "macht",  "machte",  "machten",  "man",  "manchen",  "mancher", "manches",  "mein",  "meine",  "meinem",  "meiner",  "meines",  "mich",  "mir",  "mit",  "muss", "musste",  "müßt",  "nicht",  "noch",  "nun",  "nur",  "ob",  "ohne",  "sage",  "sagen",  "sagt", "sagte",  "sagten",  "sagtest",  "sehe",  "sehen",  "sehr",  "seht",  "sein",  "seinem",  "seinen", "seiner",  "seines",  "selbst",  "sich",  "sicher",  "sie",  "sind",  "so",  "solche",  "solchem", "solchen",  "solcher",  "sollte",  "sondern",  "um",  "uns",  "unse",  "unsen",  "unser",  "unses", "unter",  "viel",  "von",  "vor",  "war",  "waren",  "warst",  "was",  "weg",  "weil",  "weiter", "welchem",  "welchen",  "welcher",  "welches",  "welche",  "werde",  "wieder",  "will",  "wir",  "wird", "wirst",  "wo",  "wolle",  "wollen",  "wollt",  "wollten",  "wolltest",  "wolltet",  "würde",  "würden", "z.B.",  "zum",  "zur",  "zwar",  "zwischen",  "über",  "aber",  "abgerufen",  "abgerufene", "abgerufener",  "abgerufenes",  "acht",  "allein",  "allerdings",  "allerlei",  "allgemein", "allmählich",  "allzu",  "alsbald",  "andererseits",  "andernfalls",  "anerkannt",  "anerkannte", "anerkannter",  "anerkanntes",  "anfangen",  "anfing",  "angefangen",  "angesetze",  "angesetzt", "angesetzten",  "angesetzter",  "ansetzen",  "anstatt",  "arbeiten",  "aufgehört",  "aufgrund", "aufhören",  "aufhörte",  "aufzusuchen",  "ausdrücken",  "ausdrückt",  "ausdrückte",  "ausgenommen", "ausser",  "ausserdem",  "author",  "autor",  "außen",  "außer",  "außerdem",  "außerhalb",  "bald", "bearbeite",  "bearbeiten",  "bearbeitete",  "bearbeiteten",  "bedarf",  "bedurfte",  "bedürfen", "befragen",  "befragte",  "befragten",  "befragter",  "begann",  "beginnen",  "begonnen",  "behalten", "behielt",  "beide",  "beiden",  "beiderlei",  "beides",  "beim",  "bei",  "beinahe",  "beitragen", "beitrugen",  "bekannt",  "bekannte",  "bekannter",  "bekennen",  "benutzt",  "bereits",  "berichten", "berichtet",  "berichtete",  "berichteten",  "besonders",  "besser",  "bestehen",  "besteht", "beträchtlich",  "bevor",  "bezüglich",  "bietet",  "bisher",  "bislang",  "bis",  "bleiben", "blieb",  "bloss",  "bloß",  "brachte",  "brachten",  "brauchen",  "braucht",  "bringen",  "bräuchte", "bzw",  "böden",  "ca.",  "dabei",  "dadurch",  "dafür",  "dagegen",  "dahin",  "damals",  "danach", "daneben",  "dank",  "danke",  "danken",  "dannen",  "daran",  "darauf",  "daraus",  "darf",  "darfst", "darin",  "darum",  "darunter",  "darüber",  "darüberhinaus",  "dass",  "davon",  "davor",  "demnach", "denen",  "dennoch",  "derart",  "derartig",  "derem",  "deren",  "derjenige",  "derjenigen",  "derzeit", "desto",  "deswegen",  "diejenige",  "diesseits",  "dinge",  "direkt",  "direkte",  "direkten", "direkter",  "doppelt",  "dorther",  "dorthin",  "drauf",  "drei",  "dreißig",  "drin",  "dritte", "drunter",  "drüber",  "dunklen",  "durchaus",  "durfte",  "durften",  "dürfen",  "dürfte",  "eben", "ebenfalls",  "ebenso",  "ehe",  "eher",  "eigenen",  "eigenes",  "eigentlich",  "einbaün", "einerseits",  "einfach",  "einführen",  "einführte",  "einführten",  "eingesetzt",  "einigermaßen", "eins",  "einseitig",  "einseitige",  "einseitigen",  "einseitiger",  "einst",  "einstmals",  "einzig", "ende",  "entsprechend",  "entweder",  "ergänze",  "ergänzen",  "ergänzte",  "ergänzten",  "erhalten", "erhielt",  "erhielten",  "erhält",  "erneut",  "erst",  "erste",  "ersten",  "erster",  "eröffne", "eröffnen",  "eröffnet",  "eröffnete",  "eröffnetes",  "etc",  "etliche",  "etwa",  "fall",  "falls", "fand",  "fast",  "ferner",  "finden",  "findest",  "findet",  "folgende",  "folgenden",  "folgender", "folgendes",  "folglich",  "fordern",  "fordert",  "forderte",  "forderten",  "fortsetzen",  "fortsetzt", "fortsetzte",  "fortsetzten",  "fragte",  "frau",  "frei",  "freie",  "freier",  "freies",  "fuer", "fünf",  "gab",  "ganzem",  "gar",  "gbr",  "geb",  "geben",  "geblieben",  "gebracht",  "gedurft", "geehrt",  "geehrte",  "geehrten",  "geehrter",  "gefallen",  "gefiel",  "gefälligst",  "gefällt", "gegeben",  "gehabt",  "gehen",  "geht",  "gekommen",  "gekonnt",  "gemocht",  "gemäss",  "genommen", "genug",  "gern",  "gestern",  "gestrige",  "getan",  "geteilt",  "geteilte",  "getragen", "gewissermaßen",  "geworden",  "ggf",  "gib",  "gibt",  "gleich",  "gleichwohl",  "gleichzeitig", "glücklicherweise",  "gmbh",  "gratulieren",  "gratuliert",  "gratulierte",  "gut",  "gute",  "guten", "gängig",  "gängige",  "gängigen",  "gängiger",  "gängiges",  "gänzlich",  "haette",  "halb",  "hallo", "hast",  "hattest",  "hattet",  "heraus",  "herein",  "heute",  "heutige",  "hiermit",  "hiesige", "hinein",  "hinten",  "hinterher",  "hoch",  "hundert",  "hätt",  "hätte",  "hätten",  "höchstens", "igitt",  "immer",  "immerhin",  "important",  "indessen",  "info",  "infolge",  "innen",  "innerhalb", "insofern",  "inzwischen",  "irgend",  "irgendeine",  "irgendwas",  "irgendwen",  "irgendwer", "irgendwie",  "irgendwo",  "je",  "jedenfalls",  "jederlei",  "jedoch",  "jemand",  "jenseits", "jährig",  "jährige",  "jährigen",  "jähriges",  "kam",  "kannst",  "kaum",  "keines",  "keinerlei", "keineswegs",  "klar",  "klare",  "klaren",  "klares",  "klein",  "kleinen",  "kleiner",  "kleines","koennen",  "koennt",  "koennte",  "koennten",  "komme",  "kommen",  "kommt",  "konkret",  "konkrete", "konkreten",  "konkreter",  "konkretes",  "konnten",  "könn",  "könnt",  "könnten",  "künftig",  "lag", "lagen",  "langsam",  "lassen",  "laut",  "lediglich",  "leer",  "legen",  "legte",  "legten",  "leicht", "leider",  "lesen",  "letze",  "letzten",  "letztendlich",  "letztens",  "letztes",  "letztlich", "lichten",  "liegt",  "liest",  "links",  "längst",  "längstens",  "mag",  "magst",  "mal", "mancherorts",  "manchmal",  "mann",  "margin",  "mehr",  "mehrere",  "meist",  "meiste",  "meisten", "meta",  "mindestens",  "mithin",  "mochte",  "morgen",  "morgige",  "muessen",  "muesst",  "musst", "mussten",  "muß",  "mußt",  "möchte",  "möchten",  "möchtest",  "mögen",  "möglich",  "mögliche", "möglichen",  "möglicher",  "möglicherweise",  "müssen",  "müsste",  "müssten",  "müßte",  "nachdem", "nacher",  "nachhinein",  "nahm",  "natürlich",  "nacht",  "neben",  "nebenan",  "nehmen",  "nein", "neu",  "neue",  "neuem",  "neuen",  "neuer",  "neues",  "neun",  "nie",  "niemals",  "niemand", "nimm",  "nimmer",  "nimmt",  "nirgends",  "nirgendwo",  "nutzen",  "nutzt",  "nutzung",  "nächste", "nämlich",  "nötigenfalls",  "nützt",  "oben",  "oberhalb",  "obgleich",  "obschon",  "obwohl",  "oft", "per",  "pfui",  "plötzlich",  "pro",  "reagiere",  "reagieren",  "reagiert",  "reagierte",  "rechts", "regelmäßig",  "rief",  "rund",  "sang",  "sangen",  "schlechter",  "schließlich",  "schnell",  "schon", "schreibe",  "schreiben",  "schreibens",  "schreiber",  "schwierig",  "schätzen",  "schätzt", "schätzte",  "schätzten",  "sechs",  "sect",  "sehrwohl",  "sei",  "seit",  "seitdem",  "seite", "seiten",  "seither",  "selber",  "senke",  "senken",  "senkt",  "senkte",  "senkten",  "setzen", "setzt",  "setzte",  "setzten",  "sicherlich",  "sieben",  "siebte",  "siehe",  "sieht",  "singen", "singt",  "sobald",  "sodaß",  "soeben",  "sofern",  "sofort",  "sog",  "sogar",  "solange",  "solc", "hen",  "solch",  "sollen",  "sollst",  "sollt",  "sollten",  "solltest",  "somit",  "sonstwo", "sooft",  "soviel",  "soweit",  "sowie",  "sowohl",  "spielen",  "später",  "startet",  "startete", "starteten",  "statt",  "stattdessen",  "steht",  "steige",  "steigen",  "steigt",  "stets",  "stieg", "stiegen",  "such",  "suchen",  "sämtliche",  "tages",  "tat",  "tatsächlich",  "tatsächlichen", "tatsächlicher",  "tatsächliches",  "tausend",  "teile",  "teilen",  "teilte",  "teilten",  "titel", "total",  "trage",  "tragen",  "trotzdem",  "trug",  "trägt",  "toll",  "tun",  "tust",  "tut",  "txt", "tät",  "ueber",  "umso",  "unbedingt",  "ungefähr",  "unmöglich",  "unmögliche",  "unmöglichen", "unmöglicher",  "unnötig",  "unsem",  "unser",  "unsere",  "unserem",  "unseren",  "unserer", "unseres",  "unten",  "unterbrach",  "unterbrechen",  "unterhalb",  "unwichtig",  "usw",  "vergangen", "vergangene",  "vergangener",  "vergangenes",  "vermag",  "vermutlich",  "vermögen",  "verrate", "verraten",  "verriet",  "verrieten",  "version",  "versorge",  "versorgen",  "versorgt",  "versorgte", "versorgten",  "versorgtes",  "veröffentlichen",  "veröffentlicher",  "veröffentlicht", "veröffentlichte",  "veröffentlichten",  "veröffentlichtes",  "viele",  "vielen",  "vieler",  "vieles", "vielleicht",  "vielmals",  "vier",  "vollständig",  "voran",  "vorbei",  "vorgestern",  "vorher", "vorne",  "vorüber",  "völlig",  "während",  "wachen",  "waere",  "warum",  "weder",  "wegen", "weitere",  "weiterem",  "weiteren",  "weiterer",  "weiteres",  "weiterhin",  "weiß",  "wem",  "wen", "wenig",  "wenige",  "weniger",  "wenigstens",  "wenngleich",  "wer",  "werdet",  "weshalb",  "wessen", "weswegen",  "wichtig",  "wieso",  "wieviel",  "wiewohl",  "willst",  "wirklich",  "wodurch",  "wogegen", "woher",  "wohin",  "wohingegen",  "wohl",  "wohlweislich",  "womit",  "woraufhin",  "woraus",  "worin", "wurde",  "wurden",  "währenddessen",  "wär",  "wäre",  "wären",  "zahlreich",  "zehn",  "zeitweise", "ziehen",  "zieht",  "zog",  "zogen",  "zudem",  "zuerst",  "zufolge",  "zugleich",  "zuletzt",  "zumal", "zurück",  "zusammen",  "zuviel",  "zwanzig",  "zwei",  "zwölf",  "ähnlich", "übel",  "überall",  "überallhin",  "überdies",  "übermorgen",  "übrig",  "übrigens");
		else
			$stopwords = array("a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount", "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as", "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");
		
		foreach($stopwords as $stopword) {
			$text = str_ireplace(" " . $stopword . " ",' . ',$text);
			$text = str_ireplace(" " . $stopword . ".",' . ',$text);
			$text = str_ireplace(" " . $stopword . ",",' . ',$text);
			$text = str_ireplace(" " . $stopword . ";",' . ',$text);
			$text = str_ireplace(" " . $stopword . ":",' . ',$text);
			$text = str_ireplace(" " . $stopword . "/",' . ',$text);
			$text = str_ireplace("\n" . $stopword . " ",' . ',$text);
			$text = str_ireplace("\n" . $stopword . ".",' . ',$text);
			$text = str_ireplace("\n" . $stopword . ",",' . ',$text);
			$text = str_ireplace("\n" . $stopword . ";",' . ',$text);
			$text = str_ireplace("\n" . $stopword . ":",' . ',$text);
			$text = str_ireplace("\n" . $stopword . "/",' . ',$text);
		}
		$text = str_replace(array('-','_'),array(' '),$text);
		
		$pattern1 = "/\b[A-ZÄÖÜ]{1}+[A-Za-zäüöéèáàúùß]{2,}+ [A-ZÄÖÜ]{1}+[A-Za-zäüöéèáàúùß]{2,}+ [A-ZÄÖÜ]{1}+[A-Za-zäüöéèáàúùß]{2,}+|[A-ZÄÖÜ]{1}+[A-Za-zäüöéèáàúùß]{2,}+ [A-ZÄÖÜ]{1}+[A-Za-zäüöéèáàúùß]{2,}+|[A-ZÄÖÜ]{1}+[A-Za-zäüöéèáàúùß]{2,}+\b/um";
		$pattern2 = "/\b[A-ZÄÖÜ]{1}+[A-Za-zäüöéèáàúùß]{2,}+ [A-ZÄÖÜ]{1}+[A-Za-zäüöéèáàúùß]{2,}+ [A-ZÄÖÜ]{1}+[A-Za-zäüöéèáàúùß]{2,}+\b/um";
		$pattern3 = "/\b[A-ZÄÖÜ]{1}+[A-Za-zäüöéèáàúùß]{2,}+ [A-ZÄÖÜ]{1}+[A-Za-zäüöéèáàúùß]{2,}+\b/um";
		$pattern4 = "/\b[A-ZÄÖÜ]{1}+[A-Za-zäüöéèáàúùß]{2,}+\b/um";
		
		preg_match_all($pattern1, $text, $array1, PREG_PATTERN_ORDER);
		$text2 = implode(', ',$array1[0]);
		preg_match_all($pattern2, $text2, $array2);
		$text3 = implode(', ',$array1[0]);
		preg_match_all($pattern3, $text3, $array3);
		$text4 = implode(', ',$array1[0]);
		preg_match_all($pattern4, $text4, $array4);
		
		$array1[0] = array_map('ucwords', array_map('strtolower', $array1[0]));
		$array2[0] = array_map('ucwords', array_map('strtolower', $array2[0]));
		$array3[0] = array_map('ucwords', array_map('strtolower', $array3[0]));
		$array4[0] = array_map('ucwords', array_map('strtolower', $array4[0]));
		
		$ausgabe1 = array_count_values($array1[0]);
		$ausgabe2 = array_count_values($array2[0]);
		$ausgabe3 = array_count_values($array3[0]);
		$ausgabe4 = array_count_values($array4[0]);
		
		foreach ($ausgabe3 as $key => $value) {
			if (isset($ausgabe1[$key])) {$ausgabe1[$key] = $value;}
		}
		
		foreach ($ausgabe4 as $key => $value) {
			if (isset($ausgabe1[$key])) {$ausgabe1[$key] = $value;}
		}
		$new_keys = array_merge($ausgabe1,$ausgabe2);
		array_multisort($new_keys, SORT_DESC);
		$new_keys = array_slice($new_keys,0,$number);
		$keywords = array_keys($new_keys);
		$keywords = implode(', ',$keywords);		
		
		return $keywords;
	
	}

}
