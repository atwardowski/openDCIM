<?php
/* All functions contained herein will be general use functions */

/* Generic html sanitization routine */

if(!function_exists("sanitize")){
	function sanitize($string,$stripall=true){
		// Trim any leading or trailing whitespace
		$clean=trim($string);

		// Convert any special characters to their normal parts
		$clean=html_entity_decode($clean,ENT_COMPAT,"UTF-8");

		// By default strip all html
		$allowedtags=($stripall)?'':'<a><b><i><img><u><br>';

		// Strip out the shit we don't allow
		$clean=strip_tags($clean, $allowedtags);
		// If we decide to strip double quotes instead of encoding them uncomment the 
		//	next line
	//	$clean=($stripall)?str_replace('"','',$clean):$clean;
		// What is this gonna do ?
		$clean=filter_var($clean, FILTER_SANITIZE_SPECIAL_CHARS);

		// There shoudln't be anything left to escape but wtf do it anyway
		$clean=addslashes($clean);

		return $clean;
	}
}

/* 
Regex to make sure a valid URL is in the config before offering options for contact lookups
http://www.php.net/manual/en/function.preg-match.php#93824

Example Usage:
	if(isValidURL("http://test.com"){//do something}

*/
function isValidURL($url){
	$urlregex="((https?|ftp)\:\/\/)?"; // SCHEME
	$urlregex.="([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
	$urlregex.="([a-z0-9-.]*)\.([a-z]{2,3})"; // Host or IP
	$urlregex.="(\:[0-9]{2,5})?"; // Port
	$urlregex.="(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path
	$urlregex.="(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
	$urlregex.="(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor 
// Testing out the php url validation, leaving the regex for now
//	if(preg_match("/^$urlregex$/",$url)){return true;}
	return filter_var($url, FILTER_VALIDATE_URL);
}

//Convert hex color codes to rgb values
function html2rgb($color){
	if($color[0]=='#'){
		$color=substr($color,1);
	}
	if(strlen($color)==6){
		list($r,$g,$b)=array($color[0].$color[1],$color[2].$color[3],$color[4].$color[5]);
	}elseif(strlen($color)==3){
		list($r,$g,$b)=array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
	}else{
		return false;
	}
	$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

	return array($r, $g, $b);
}

/*
Used to ensure a properly formatted url in use of instances header("Location")

Example usage:
	header("Location: ".redirect());
	exit;
			- or -
	header("Location: ".redirect('storageroom.php'));
	exit;
			- or -
	$url=redirect("index.php?test=23")
	header("Location: $url");
	exit;
*/
function path(){
	$path=explode("/",$_SERVER['REQUEST_URI']);
	unset($path[(count($path)-1)]);
	$path=implode("/",$path);
	return $path;
}
function redirect($target = null) {
	// No argument was passed.  If a referrer was set, send them back to whence they came.
	if(is_null($target)){
		if(isset($_SERVER["HTTP_REFERER"])){
			return $_SERVER["HTTP_REFERER"];
		}else{
			// No referrer was set so send them to the root application directory
			$target=path();
		}
	}else{
		//Try to ensure that a properly formatted uri has been passed in.
		if(substr($target, 4)!='http'){
			//doesn't start with http or https check to see if it is a path
			if(substr($target, 1)!='/'){
				//didn't start with a slash so it must be a filename
				$target=path()."/".$target;
			}else{
				//started with a slash let's assume they know what they're doing
				$target=path().$target;
			}
		}else{
			//Why the heck did you send a full url here instead of just doing a header?
			return $target;
		}
	}
	if(array_key_exists('HTTPS', $_SERVER) && $_SERVER["HTTPS"]=='on') {
		$url = "https://".$_SERVER['HTTP_HOST'].$target;
	} else {
		$url = "http://".$_SERVER['HTTP_HOST'].$target;
	}
	return $url;
}

// search haystack for needle and return an array of the key path,
// FALSE otherwise.
// if NeedleKey is given, return only for this key
// mixed ArraySearchRecursive(mixed Needle,array Haystack[,NeedleKey[,bool Strict[,array Path]]])

function ArraySearchRecursive($Needle,$Haystack,$NeedleKey="",$Strict=false,$Path=array()) {
	if(!is_array($Haystack))
		return false;
	foreach($Haystack as $Key => $Val) {
		if(is_array($Val)&&$SubPath=ArraySearchRecursive($Needle,$Val,$NeedleKey,$Strict,$Path)) {
			$Path=array_merge($Path,Array($Key),$SubPath);
			return $Path;
		}elseif((!$Strict&&$Val==$Needle&&$Key==(strlen($NeedleKey)>0?$NeedleKey:$Key))||($Strict&&$Val===$Needle&&$Key==(strlen($NeedleKey)>0?$NeedleKey:$Key))) {
			$Path[]=$Key;
			return $Path;
		}
	}
	return false;
}

/*
 * Sort multidimentional array in natural order
 *
 * $array = sort2d ( $array, 'key to sort on')
 */
function sort2d ($array, $index){
	//Create array of key and label to sort on.
	foreach(array_keys($array) as $key){$temp[$key]=$array[$key][$index];}
	//Case insensative natural sorting of temp array.
	natcasesort($temp);
	//Rebuild original array using the newly sorted order.
	foreach(array_keys($temp) as $key){$sorted[$key]=$array[$key];}
	return $sorted;
}  
/*
 * Sort multidimentional array in reverse order
 *
 * $array = sort2d ( $array, 'key to sort on')
 */
function arsort2d ($array, $index){
	//Create array of key and label to sort on.
	foreach(array_keys($array) as $key){$temp[$key]=$array[$key][$index];}
	//Case insensative natural sorting of temp array.
	arsort($temp);
	//Rebuild original array using the newly sorted order.
	foreach(array_keys($temp) as $key){$sorted[$key]=$array[$key];}
	return $sorted;
}  

/*
 * Define multibyte string functions in case they aren't present
 *
 */

if(!extension_loaded('mbstring')){
	function mb_strtoupper($text,$encoding=null){
		return strtoupper($text);
	}
	function mb_strtolower($text,$encoding=null){
		return strtolower($text);
	}
	function mb_convert_case($string, $transform, $locale){
		switch($transform){
			case 'MB_CASE_UPPER':
				$string=mb_strtoupper($string);
				break;
			case 'MB_CASE_LOWER':
				$string=mb_strtolower($string);
				break;
			case 'MB_CASE_TITLE':
				$string=ucwords(mb_strtolower($string));
				break;
		}
		return $string;
	}
}

/*
 * Transform text to uppercase, lowercase, initial caps, or do nothing based on system config
 * 2nd parameter is optional to override the system default
 *
 */
function transform($string,$method=null){
	$config=new Config();
	$method=(is_null($method))?$config->ParameterArray['LabelCase']:$method;
	switch ($method){
		case 'upper':
			$string=mb_convert_case($string, MB_CASE_UPPER, "UTF-8");
			break;
		case 'lower':
			$string=mb_convert_case($string, MB_CASE_LOWER, "UTF-8");
			break;
		case 'initial':
			$string=mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
			break;
		default:
			// Don't you touch my string.
	}
	return $string;
}

/*
 * Language internationalization slated for v2.0
 *
 */
if(isset($_COOKIE["lang"])){
	$locale=$_COOKIE["lang"];
}else{
	$locale=$config->ParameterArray['Locale'];
}

if(extension_loaded('gettext')){
	if(isset($locale)){
		setlocale(LC_ALL,$locale);
		putenv("LC_ALL=$locale");
		bindtextdomain("openDCIM","./locale");

		$codeset='utf8';
		if(isset($codeset)){
			bind_textdomain_codeset("openDCIM",$codeset);
		}
		textdomain("openDCIM");
	}
}

function GetValidTranslations() {
	$path='./locale';
	$dir=scandir($path);
	$lang=array();
	global $locale;

	foreach($dir as $i => $d){
		// get list of directories in locale that aren't . or ..
		if(is_dir($path.DIRECTORY_SEPARATOR.$d) && $d!=".." && $d!="."){
			// check the list of valid directories above to see if there is an openDCIM translation file present
			if(file_exists($path.DIRECTORY_SEPARATOR.$d.DIRECTORY_SEPARATOR."LC_MESSAGES".DIRECTORY_SEPARATOR."openDCIM.mo")){
				// build array of valid language choices
				$lang[$d]=$d;
			}
		}
	}
	return $lang;
}

function __($string){
	if(extension_loaded('gettext')){
		return _($string);
	}else{
		return $string;
	}
}


/**
 * Parse a string which contains numeric or alpha repetition specifications. It
 *  returns an array of tokens or a message of the parsing exception encountered
 *  with the location of the failing character.
 *
 * @param string $pat
 * @return mixed
 */
function parseGeneratorString($pat)
{
    $result = array();
    $cstr = '';
    $escape = false;
    $patLen = strlen($pat);

    for ($i=0; $i < $patLen; $i++) {
        if ($escape) {
            $cstr .= $pat[$i];
            $escape = false;
            continue;
        }
        if ($pat[$i] == '\\') {
            $escape = true;
            continue;
        }
        if ($pat[$i] == '(') {
            // current string complete, start of a pattern
            $result[] = array('String', array($cstr));
            $cstr = '';
            list($i, $patSpec, $msg) = parsePatternSpec($pat, $patLen, ++$i);
            if (! $patSpec) {
                echo 'Error: Parse pattern return error - \'', $msg, '\' ', $i, PHP_EOL;
                return array(null, $msg, $i);
            }
            $result[] = $patSpec;
            continue;
        }
        $cstr .= $pat[$i];
    }
    if ($cstr != '') {
        $result[] = array('String', array($cstr));
    }

    return array($result, '', $i);
}

/**
 * Parse the numeric or alpha pattern specification and return the specification
 *  token or a the message explaining the exception encountered and the position
 *  of the character where the exception was detected.
 *
 * @param type $pat
 * @param type $patLen
 * @param type $idx
 * @return type
 */
function parsePatternSpec(&$pat, $patLen, $idx) {
    $stopChars = array(';', ')');
    $patSpec = array();
    $msg = 'Wrong pattern specification';

    $ValueStr = '';
    $token = 'StartValue';
    $startValue = null;
    $increment = 1;
    $patType = '';

    for ($i = $idx; $i < $patLen; ++$i) {
        if (ctype_digit($pat[$i])) {
            list($ValueStr, $i, $msg) = getNumericString($pat, $i, $stopChars);
            $patType = 'numeric';
        } elseif (ctype_alpha($pat[$i])) {
            list($ValueStr, $i, $msg) = getAlphaString($pat, $i, $stopChars);
            $patType = 'alpha';
        } else {
            if ($token == 'StartValue') {
                $msg = 'No start value detected.';
            } elseif ($token == 'Increment') {
                $msg = 'Missing increment value.';
            } else {
                $msg = 'Unexpected character \'' . $pat[$i] . '\'';
            }
            return array($i, null, $msg);
        }
        if (($token == 'StartValue') and ($i >= $patLen)) {
            $msg = 'Incomplete pattern specification, missing stop character [\''
                . implode('\',\'', $stopChars) . '\']';
            return array($i, null, $msg);
        }
        if (($token == 'StartValue') and (in_array($pat[$i], $stopChars))) {
            if ($ValueStr === '') {
                $msg = 'Missing start value';
                return array($i, null, $msg);
            }
            if ($patType == 'numeric') {
                $startValue = intval($ValueStr);
            } else {
                $startValue = $ValueStr;
            }
            $ValueStr = '';
            if ($pat[$i] == ')') {
                $token = 'right_parenthesis';
            } elseif ($pat[$i] == ';') {
                $token = 'Increment';
                continue;
            }
        }
        if (($token == 'Increment')) {
            if ($patType == 'numeric') {
                $increment = intval($ValueStr);
            } else {
                $msg = 'Increment must be a number, wrong value \'' . $ValueStr . '\'';
                return array($i, null, $msg);
            }
        }
        if ($pat[$i] == ')') {
            $patSpec = array('Pattern', array($patType, $startValue, $increment));
            break;
        }
        $msg = 'Unexpected character \'' . $pat[$i] . '\' for token \'' . $token . '\'.';
        return array($i, null, $msg);
    }
    if ((! $patSpec) and ($token == 'Increment')) {
        $msg = 'Incomplete increment specification';
        return array($i, null, $msg);
    }
    return array($i, $patSpec, $msg);
}

/**
 * Parse a numeric string.
 *
 * @param string $pat
 * @param int $idx
 * @param array $stopChars
 * @return mixed
 */
function getNumericString(&$pat, $idx, &$stopChars) {
    $strValue = '';
    for ($i=$idx; $i < strlen($pat); $i++) {
        $char = $pat[$i];
        if (in_array($char, $stopChars)) {
                return array(intval($strValue), $i, 'NumericValue');
        }
        if (ctype_digit($char)) {
            $strValue .= $char;
        } else {
            $msg = 'Non-numeric character encountered \'' . $char . '\' ';
            return array(null, $i, $msg);
        }
    }
    $msg = 'Stop character not encountered [\'' . implode('\',\'', $stopChars) . '\'].';
    return array(null, $i, $msg);
}

/**
 * Parse an alpha string.
 *
 * @param string $pat
 * @param int $idx
 * @param array $stopChars
 * @return mixed
 */
function getAlphaString(&$pat, $idx, &$stopChars) {
    $strValue = '';
    $patType = 'alpha';
    $escaped = false;

    for ($i=$idx; $i < strlen($pat); $i++) {
        $char = $pat[$i];
        if ($char == '\\') {
            $escaped= true;
            continue;
        }
        if ($escaped) {
            $strValue .= $char;
            $escaped = false;
            continue;
        }
        if (in_array($char, $stopChars)) {
            return array($strValue, $i, 'AlphaValue');
        }
        if (ctype_alpha($char)) {
            $strValue .= $char;
        } else {
            $msg = 'Non-numeric character encountered \'' . $char . '\' ';
            return array(null, $i, $msg);
        }
    }
    $msg = 'Stop character not encountered \'' . implode(',', $stopChars) . '\'.';
    return array(null, $i, $msg);
}

// Code provided for num2alpha and alpha2num in
// http://stackoverflow.com/questions/5554369/php-how-to-output-list-like-this-aa-ab-ac-all-the-way-to-zzzy-zzzz-zzzza
//function num2alpha($n, $shift=0) {
//    for ($r = ''; $n >= 0; $n = intval($n / 26) - 1)
//        $r = chr($n % 26 + 0x41 + $shift) . $r;
//    return $r;
//}

/**
 * Return the alpha represenation of the integer based on Excel offset.
 *
 * @param int $n
 * @param int $offset
 * @return string
 */
function num2alpha($n, $offset = 0x40) {
    for ($r = ''; $n >= 0; $n = intval($n / 26) - 1) {
        $r = chr($n % 26 + ($offset + 1)) . $r;
    }
    return $r;
}

/**
 * Return the numeric representation the alpha string  based on Excel offset.
 * @param string $a
 * @param int $offset
 * @return int
 */
function alpha2num($a, $offset = 0x40)
{
    $base = 26;
    $l = strlen($a);
    $n = 0;
    for ($i = 0; $i < $l; $i++) {
        $n = $n*$base + ord($a[$i]) - $offset;
    }
    return $n-1;
}

/**
 * Take the generator string specification produced by parseGeneratorString and
 *  return a list of strings where the patterns are instantiated.
 *
 * @param array $patSpecs
 * @param int $count
 * @return array
 */
function generatePatterns($patSpecs, $count) {
    $patternList = array();
    for ($i=0; $i < $count; $i++) {
        $str = '';
        foreach ($patSpecs as $pat) {
            if ($pat) {
               if ($pat[0] == 'String') {
                    $str .= $pat[1][0];
                } elseif ($pat[0] == 'Pattern') {
                    if ($pat[1][0] == 'numeric') {
                        $str .= (integer)($pat[1][1] + $i*$pat[1][2]);
                    } elseif ($pat[1][0] == 'alpha') {
                        $charIntVal = ord($pat[1][1]);
                        if (($charIntVal >= 65) and ($charIntVal <= 90)) {
                            $offset = 0x40;
                            $charIntVal = alpha2num($pat[1][1]);
                        } else {
                            $offset = 0x60;
                            $charIntVal = alpha2num($pat[1][1], $offset);
                        }
                        $str .= num2alpha(($charIntVal + $i*$pat[1][2]), $offset);
                    }
                }
            }
        }
        $patternList[] = $str;
    }

    return $patternList;
}

function locale_number( $number, $decimals=2 ) {
    $locale = localeconv();
    return number_format($number,$decimals,
               $locale['decimal_point'],
               $locale['thousands_sep']);
}

// This will build an array that can be json encoded to represent the makeup of
// the installations containers, zones, rows, etc.  It didn't seem appropriate
// to be on any single class
if(!function_exists("buildNavTreeArray")){
	function buildNavTreeArray(){
		$con=new Container();
		$cabs=Cabinet::ListCabinets();

		$menu=array();

		function processcontainer($container,$cabs){
			$menu=array($container);
			foreach($container->GetChildren() as $child){
				if(get_class($child)=='Container'){
					$menu[]=processcontainer($child,$cabs);
				}elseif(get_class($child)=='DataCenter'){
					$menu[]=processdatacenter($child,$cabs);
				}
			}
			return $menu;
		}
		function processdatacenter($dc,$cabs){
			$menu=array($dc);
			foreach($dc->GetChildren() as $child){
				if(get_class($child)=='Zone'){
					$menu[]=processzone($child,$cabs);
				}elseif(get_class($child)=='CabRow'){
					$menu[]=processcabrow($child,$cabs);
				}else{
					$menu[]=processcab($child,$cabs);
				}
			}
			return $menu;
		}
		function processzone($zone,$cabs){
			$menu=array($zone);
			foreach($zone->GetChildren() as $child){
				if(get_class($child)=='CabRow'){
					$menu[]=processcabrow($child,$cabs);
				}else{
					$menu[]=processcab($child,$cabs);
				}
			}
			return $menu;
		}
		function processcabrow($row,$cabs){
			$menu=array($row);
			foreach($cabs as $cab){
				if($cab->CabRowID==$row->CabRowID){
					$menu[]=processcab($cab,$cabs);
				}
			}
			return $menu;
		}
		function processcab($cab,$cabs){
			return $cab;
		}

		foreach($con->GetChildren() as $child){
			if(get_class($child)=='Container'){
				$menu[]=processcontainer($child,$cabs);
			}elseif(get_class($child)=='DataCenter'){
				$menu[]=processdatacenter($child,$cabs);
			}
		}

		return $menu;
	}
}

// This will format the array above into the format needed for the side bar navigation
// menu. 
if(!function_exists("buildNavTreeHTML")){
	function buildNavTreeHTML($menu=null){
		$tl=1; //tree level

		$menu=(is_null($menu))?buildNavTreeArray():$menu;

		function buildnavmenu($ma,&$tl){
			foreach($ma as $i => $level){
				if(is_object($level)){
					if(isset($level->Name)){
						$name=$level->Name;
					}elseif(isset($level->Location)){
						$name=$level->Location;
					}else{
						$name=$level->Description;
					}
					if($i==0){--$tl;}
					foreach($level as $prop => $value){
						if(preg_match("/id/i", $prop)){
							$ObjectID=$value;
							break;
						}
					}
					$class=get_class($level);
					$cabclose='';
					if($class=="Container"){
						$href="container_stats.php?container=";
						$id="c$ObjectID";
					}elseif($class=="Cabinet"){
						$href="cabnavigator.php?cabinetid=";
						$id="cab$ObjectID";
						$cabclose="</li>";
					}elseif($class=="Zone"){
						$href="zone_stats.php?zone=";
						$id="zone$ObjectID";
					}elseif($class=="DataCenter"){
						$href="dc_stats.php?dc=";
						$id="dc$ObjectID";
					}elseif($class=="CabRow"){
						$href="rowview.php?row=";
						$id="cr$ObjectID";
					}

					print str_repeat("\t",$tl).'<li class="liClosed" id="'.$id.'"><a class="'.$class.'" href="'.$href.$ObjectID."\">$name</a>$cabclose\n";
					if($i==0){
						++$tl;
						print str_repeat("\t",$tl)."<ul>\n";
					}
				}else{
					$tl++;
					buildnavmenu($level,$tl);
					if(get_class($level[0])=="DataCenter"){
						print str_repeat("\t",$tl).'<li id="dc-'.$level[0]->DataCenterID.'"><a href="storageroom.php?dc='.$level[0]->DataCenterID.'">Storage Room</a></li>'."\n";
					}
					print str_repeat("\t",$tl)."</ul>\n";
					$tl--;
					print str_repeat("\t",$tl)."</li>\n";
				}
			}
		}

		print '<ul class="mktree" id="datacenters">'."\n";
		buildnavmenu($menu,$tl);
		print '<li id="dc-1"><a href="storageroom.php">'.__("General Storage Room")."</a></li>\n</ul>";
	}
}


/*
	Check if we are doing a new install or an upgrade has been applied.  
	If found then force the user into only running that function.

	To bypass the installer check from running, simply add
	$devMode = true;
	to the db.inc.php file.
*/

if(isset($devMode)&&$devMode){
	// Development mode, so don't apply the upgrades
}else{
	if(file_exists("install.php") && basename($_SERVER['PHP_SELF'])!="install.php" ){
		// new installs need to run the install first.
		header("Location: ".redirect('install.php'));
		exit;
	}
}

/*
	If we are using Oauth authentication, go ahead and figure out who
	we are.  It may be needed for the installation.
*/

if ( !isset($_SERVER["REMOTE_USER"] ) && !isset( $_SESSION['userid'] ) && AUTHENTICATION=="Oauth" ) {
	header("Location: ".redirect('login.php'));
	exit;
}

// Using to offset errors from the header additions
if(!isset($_SESSION['userid']) && isset($_SERVER["REMOTE_USER"])){
	$_SESSION['userid']=$_SERVER["REMOTE_USER"];
}
	
if ( ! People::Current() ) {
 	if ( AUTHENTICATION == "Oauth" ) {
		header( "Location: login.php" );
		exit;
	} elseif ( AUTHENTICATION == "Apache" ) {
		print "<h1>You must have some form of Authentication enabled to use openDCIM.</h1>";
		exit;
	}
}

/*	New in 4.0

	Got rid of the separate User vs Contact tables.  Now all merged into People table.
	This section will just do a sanity check to make sure that the People table is
	populated - if it's empty, slurp in the data from the User and Contact tables
	to build it.
	
	Will need to be moved to the installer once we're ready for release.  This is just
	a fix to let developers easily migrate real data into test.
*/

$p = new People;
$c = new Contact;
$u = new User;

$plist = $p->GetUserList();
if ( sizeof( $plist ) == 0 ) {
	// We've got an empty fac_People table, so merge the user and contact tables to create it
	$clist = $c->GetContactList();
	foreach( $clist as $tmpc ) {
		$p->PersonID = $tmpc->ContactID;
		$p->UserID = $tmpc->UserID;
		$p->LastName = $tmpc->LastName;
		$p->FirstName = $tmpc->FirstName;
		$p->Phone1 = $tmpc->Phone1;
		$p->Phone2 = $tmpc->Phone2;
		$p->Phone3 = $tmpc->Phone3;
		$p->Email = $tmpc->Email;
		
		$u->UserID = $p->UserID;
		$u->GetUserRights();
		$p->AdminOwnDevices = $u->AdminOwnDevices;
		$p->ReadAccess = $u->ReadAccess;
		$p->WriteAccess = $u->WriteAccess;
		$p->DeleteAccess = $u->DeleteAccess;
		$p->ContactAdmin = $u->ContactAdmin;
		$p->RackRequest = $u->RackRequest;
		$p->RackAdmin = $u->RackAdmin;
		$p->SiteAdmin = $u->SiteAdmin;
		$p->Disabled = $u->Disabled;
		
		$sql = sprintf( "insert into fac_People set PersonID=%d, UserID='%s', AdminOwnDevices=%d, ReadAccess=%d, WriteAccess=%d,
			DeleteAccess=%d, ContactAdmin=%d, RackRequest=%d, RackAdmin=%d, SiteAdmin=%d, Disabled=%d, LastName='%s',
			FirstName='%s', Phone1='%s', Phone2='%s', Phone3='%s', Email='%s'", $p->PersonID, $p->UserID, $p->AdminOwnDevices,
			$p->ReadAccess, $p->WriteAccess, $p->DeleteAccess, $p->ContactAdmin, $p->RackRequest, $p->RackAdmin, $p->SiteAdmin,
			$p->Disabled, $p->LastName, $p->FirstName, $p->Phone1, $p->Phone2, $p->Phone3, $p->Email );

		$dbh->query( $sql );
	}
	
	$ulist = $u->GetUserList();
	foreach ( $ulist as $tmpu ) {
		/* This time around we have to see if the User is already in the fac_People table */
		$p->UserID = $tmpu->UserID;
		if ( ! $p->GetPersonByUserID() ) {
			$p->LastName = $tmpu->Name;
			$p->AdminOwnDevices = $tmpu->AdminOwnDevices;
			$p->ReadAccess = $tmpu->ReadAccess;
			$p->WriteAccess = $tmpu->WriteAccess;
			$p->DeleteAccess = $tmpu->DeleteAccess;
			$p->ContactAdmin = $tmpu->ContactAdmin;
			$p->RackRequest = $tmpu->RackRequest;
			$p->RackAdmin = $tmpu->RackAdmin;
			$p->SiteAdmin = $tmpu->SiteAdmin;
			$p->Disabled = $tmpu->Disabled;
			
			$p->CreatePerson();
		}
	}
}

/* This is used on every page so we might as well just init it once */
$person=People::Current();
	
/* 
 * This is an attempt to be sane about the rights management and the menu.
 * The menu will be built off a master array that is a merger of what options
 * the user has available.  
 *
 * Array structure:
 * 	[]->Top Level Menu Item
 *	[top level menu item]->Array(repeat previous structure)
 *
 */

$menu=$rmenu=$rrmenu=$camenu=$wamenu=$samenu=array();

$rmenu[]='<a href="reports.php"><span>'.__("Reports").'</span></a>';

if($config->ParameterArray["WorkOrderBuilder"]){
	if(isset($_COOKIE['workOrder']) && $_COOKIE['workOrder']!='[0]'){
		array_unshift($rmenu , '<a href="workorder.php"><span>'.__("Work Order").'</span></a>');
	}
}

if ( $config->ParameterArray["RackRequests"] == "enabled" && $person->RackRequest ) {
	$rrmenu[]='<a href="rackrequest.php"><span>'.__("Rack Request Form").'</span></a>';
}
if ( $person->ContactAdmin ) {
	$camenu[__("User Administration")][]='<a href="usermgr.php"><span>'.__("User Administration").'</span></a>';
	$camenu[__("User Administration")][]='<a href="departments.php"><span>'.__("Dept. Administration").'</span></a>';
	$camenu[__("Issue Escalation")][]='<a href="timeperiods.php"><span>'.__("Time Periods").'</span></a>';
	$camenu[__("Issue Escalation")][]='<a href="escalations.php"><span>'.__("Escalation Rules").'</span></a>';
}
if ( $person->WriteAccess ) {
	$wamenu[__("Template Management")][]='<a href="device_templates.php"><span>'.__("Edit Device Templates").'</span></a>';
	$wamenu[__("Infrastructure Management")][]='<a href="cabinets.php"><span>'.__("Edit Cabinets").'</span></a>';
	$wamenu[__("Template Management")][]='<a href="image_management.php#pictures"><span>'.__("Device Image Management").'</span></a>';
}
if ( $person->SiteAdmin ) {
	$samenu[__("Template Management")][]='<a href="device_manufacturers.php"><span>'.__("Edit Manufacturers").'</span></a>';
	$samenu[__("Template Management")][]='<a href="sensor_templates.php"><span>'.__("Edit Sensor Templates").'</span></a>';
	$samenu[__("Supplies Management")][]='<a href="supplybin.php"><span>'.__("Manage Supply Bins").'</span></a>';
	$samenu[__("Supplies Management")][]='<a href="supplies.php"><span>'.__("Manage Supplies").'</span></a>';
	$samenu[__("Infrastructure Management")][]='<a href="datacenter.php"><span>'.__("Edit Data Centers").'</span></a>';
	$samenu[__("Infrastructure Management")][]='<a href="container.php"><span>'.__("Edit Containers").'</span></a>';
	$samenu[__("Infrastructure Management")][]='<a href="zone.php"><span>'.__("Edit Zones").'</span></a>';
	$samenu[__("Infrastructure Management")][]='<a href="cabrow.php"><span>'.__("Edit Rows of Cabinets").'</span></a>';
	$samenu[__("Infrastructure Management")][]='<a href="image_management.php#drawings"><span>'.__("Facilities Image Management").'</span></a>';
	$samenu[__("Power Management")][]='<a href="power_source.php"><span>'.__("Edit Power Sources").'</span></a>';
	$samenu[__("Power Management")][]='<a href="power_panel.php"><span>'.__("Edit Power Panels").'</span></a>';
	$samenu[__("Path Connections")][]='<a href="paths.php"><span>'.__("View Path Connection").'</span></a>';
	$samenu[__("Path Connections")][]='<a href="pathmaker.php"><span>'.__("Make Path Connection").'</span></a>';
	$samenu[]='<a href="configuration.php"><span>'.__("Edit Configuration").'</span></a>';
}

function download_file($archivo, $downloadfilename = null) {
	if (file_exists($archivo)) {
		$downloadfilename = $downloadfilename !== null ? $downloadfilename : basename($archivo);
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . $downloadfilename);
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($archivo));
		ob_clean();
		flush();
		readfile($archivo);
	}
}
function download_file_from_string($string, $downloadfilename) {
	//download_file_from_string("Hola Pepe ¿Qué tal?", "pepe.txt");
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename=' . $downloadfilename);
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: ' . strlen($string));
	flush();
	echo $string;
}

?>
