<?php
namespace Khartnett;

Class Normalization
{
    private $directional = array(
        "north" => "N",
        "northeast" => "NE",
        "east" => "E",
        "southeast" => "SE",
        "south" => "S",
        "southwest" => "SW",
        "west" => "W",
        "northwest" => "NW"
    );
    
    private $streetTypes = array(
        "allee" => "aly",
        "alley" => "aly",
        "ally" => "aly",
        "anex" => "anx",
        "annex" => "anx",
        "annx" => "anx",
        "arcade" => "arc",
        "av" => "ave",
        "aven" => "ave",
        "avenu" => "ave",
        "avenue" => "ave",
        "avn" => "ave",
        "avnue" => "ave",
        "bayoo" => "byu",
        "bayou" => "byu",
        "beach" => "bch",
        "bend" => "bnd",
        "bluf" => "blf",
        "bluff" => "blf",
        "bluffs" => "blfs",
        "bot" => "btm",
        "bottm" => "btm",
        "bottom" => "btm",
        "boul" => "blvd",
        "boulevard" => "blvd",
        "boulv" => "blvd",
        "branch" => "br",
        "brdge" => "brg",
        "bridge" => "brg",
        "brnch" => "br",
        "brook" => "brk",
        "brooks" => "brks",
        "burg" => "bg",
        "burgs" => "bgs",
        "bypa" => "byp",
        "bypas" => "byp",
        "bypass" => "byp",
        "byps" => "byp",
        "camp" => "cp",
        "canyn" => "cyn",
        "canyon" => "cyn",
        "cape" => "cpe",
        "causeway" => "cswy",
        "causway" => "cswy",
        "cen" => "ctr",
        "cent" => "ctr",
        "center" => "ctr",
        "centers" => "ctrs",
        "centr" => "ctr",
        "centre" => "ctr",
        "circ" => "cir",
        "circl" => "cir",
        "circle" => "cir",
        "circles" => "cirs",
        "ck" => "crk",
        "cliff" => "clf",
        "cliffs" => "clfs",
        "club" => "clb",
        "cmp" => "cp",
        "cnter" => "ctr",
        "cntr" => "ctr",
        "cnyn" => "cyn",
        "common" => "cmn",
        "corner" => "cor",
        "corners" => "cors",
        "course" => "crse",
        "court" => "ct",
        "courts" => "cts",
        "cove" => "cv",
        "coves" => "cvs",
        "cr" => "crk",
        "crcl" => "cir",
        "crcle" => "cir",
        "crecent" => "cres",
        "creek" => "crk",
        "crescent" => "cres",
        "cresent" => "cres",
        "crest" => "crst",
        "crossing" => "xing",
        "crossroad" => "xrd",
        "crscnt" => "cres",
        "crsent" => "cres",
        "crsnt" => "cres",
        "crssing" => "xing",
        "crssng" => "xing",
        "crt" => "ct",
        "curve" => "curv",
        "dale" => "dl",
        "dam" => "dm",
        "div" => "dv",
        "divide" => "dv",
        "driv" => "dr",
        "drive" => "dr",
        "drives" => "drs",
        "drv" => "dr",
        "dvd" => "dv",
        "estate" => "est",
        "estates" => "ests",
        "exp" => "expy",
        "expr" => "expy",
        "express" => "expy",
        "expressway" => "expy",
        "expw" => "expy",
        "extension" => "ext",
        "extensions" => "exts",
        "extn" => "ext",
        "extnsn" => "ext",
        "falls" => "fls",
        "ferry" => "fry",
        "field" => "fld",
        "fields" => "flds",
        "flat" => "flt",
        "flats" => "flts",
        "ford" => "frd",
        "fords" => "frds",
        "forest" => "frst",
        "forests" => "frst",
        "forg" => "frg",
        "forge" => "frg",
        "forges" => "frgs",
        "fork" => "frk",
        "forks" => "frks",
        "fort" => "ft",
        "freeway" => "fwy",
        "freewy" => "fwy",
        "frry" => "fry",
        "frt" => "ft",
        "frway" => "fwy",
        "frwy" => "fwy",
        "garden" => "gdn",
        "gardens" => "gdns",
        "gardn" => "gdn",
        "gateway" => "gtwy",
        "gatewy" => "gtwy",
        "gatway" => "gtwy",
        "glen" => "gln",
        "glens" => "glns",
        "grden" => "gdn",
        "grdn" => "gdn",
        "grdns" => "gdns",
        "green" => "grn",
        "greens" => "grns",
        "grov" => "grv",
        "grove" => "grv",
        "groves" => "grvs",
        "gtway" => "gtwy",
        "harb" => "hbr",
        "harbor" => "hbr",
        "harbors" => "hbrs",
        "harbr" => "hbr",
        "haven" => "hvn",
        "havn" => "hvn",
        "height" => "hts",
        "heights" => "hts",
        "hgts" => "hts",
        "highway" => "hwy",
        "highwy" => "hwy",
        "hill" => "hl",
        "hills" => "hls",
        "hiway" => "hwy",
        "hiwy" => "hwy",
        "hllw" => "holw",
        "hollow" => "holw",
        "hollows" => "holw",
        "holws" => "holw",
        "hrbor" => "hbr",
        "ht" => "hts",
        "hway" => "hwy",
        "inlet" => "inlt",
        "island" => "is",
        "islands" => "iss",
        "isles" => "isle",
        "islnd" => "is",
        "islnds" => "iss",
        "jction" => "jct",
        "jctn" => "jct",
        "jctns" => "jcts",
        "junction" => "jct",
        "junctions" => "jcts",
        "junctn" => "jct",
        "juncton" => "jct",
        "key" => "ky",
        "keys" => "kys",
        "knol" => "knl",
        "knoll" => "knl",
        "knolls" => "knls",
        "la" => "ln",
        "lake" => "lk",
        "lakes" => "lks",
        "landing" => "lndg",
        "lane" => "ln",
        "lanes" => "ln",
        "ldge" => "ldg",
        "light" => "lgt",
        "lights" => "lgts",
        "lndng" => "lndg",
        "loaf" => "lf",
        "lock" => "lck",
        "locks" => "lcks",
        "lodg" => "ldg",
        "lodge" => "ldg",
        "loops" => "loop",
        "manor" => "mnr",
        "manors" => "mnrs",
        "meadow" => "mdw",
        "meadows" => "mdws",
        "medows" => "mdws",
        "mill" => "ml",
        "mills" => "mls",
        "mission" => "msn",
        "missn" => "msn",
        "mnt" => "mt",
        "mntain" => "mtn",
        "mntn" => "mtn",
        "mntns" => "mtns",
        "motorway" => "mtwy",
        "mount" => "mt",
        "mountain" => "mtn",
        "mountains" => "mtns",
        "mountin" => "mtn",
        "mssn" => "msn",
        "mtin" => "mtn",
        "neck" => "nck",
        "orchard" => "orch",
        "orchrd" => "orch",
        "overpass" => "opas",
        "ovl" => "oval",
        "parks" => "park",
        "parkway" => "pkwy",
        "parkways" => "pkwy",
        "parkwy" => "pkwy",
        "passage" => "psge",
        "paths" => "path",
        "pikes" => "pike",
        "pine" => "pne",
        "pines" => "pnes",
        "pk" => "park",
        "pkway" => "pkwy",
        "pkwys" => "pkwy",
        "pky" => "pkwy",
        "place" => "pl",
        "plain" => "pln",
        "plaines" => "plns",
        "plains" => "plns",
        "plaza" => "plz",
        "plza" => "plz",
        "point" => "pt",
        "points" => "pts",
        "port" => "prt",
        "ports" => "prts",
        "prairie" => "pr",
        "prarie" => "pr",
        "prk" => "park",
        "prr" => "pr",
        "rad" => "radl",
        "radial" => "radl",
        "radiel" => "radl",
        "ranch" => "rnch",
        "ranches" => "rnch",
        "rapid" => "rpd",
        "rapids" => "rpds",
        "rdge" => "rdg",
        "rest" => "rst",
        "ridge" => "rdg",
        "ridges" => "rdgs",
        "river" => "riv",
        "rivr" => "riv",
        "rnchs" => "rnch",
        "road" => "rd",
        "roads" => "rds",
        "route" => "rte",
        "rvr" => "riv",
        "shoal" => "shl",
        "shoals" => "shls",
        "shoar" => "shr",
        "shoars" => "shrs",
        "shore" => "shr",
        "shores" => "shrs",
        "skyway" => "skwy",
        "spng" => "spg",
        "spngs" => "spgs",
        "spring" => "spg",
        "springs" => "spgs",
        "sprng" => "spg",
        "sprngs" => "spgs",
        "spurs" => "spur",
        "sqr" => "sq",
        "sqre" => "sq",
        "sqrs" => "sqs",
        "squ" => "sq",
        "square" => "sq",
        "squares" => "sqs",
        "station" => "sta",
        "statn" => "sta",
        "stn" => "sta",
        "str" => "st",
        "strav" => "stra",
        "strave" => "stra",
        "straven" => "stra",
        "stravenue" => "stra",
        "stravn" => "stra",
        "stream" => "strm",
        "street" => "st",
        "streets" => "sts",
        "streme" => "strm",
        "strt" => "st",
        "strvn" => "stra",
        "strvnue" => "stra",
        "sumit" => "smt",
        "sumitt" => "smt",
        "summit" => "smt",
        "terr" => "ter",
        "terrace" => "ter",
        "throughway" => "trwy",
        "tpk" => "tpke",
        "tr" => "trl",
        "trace" => "trce",
        "traces" => "trce",
        "track" => "trak",
        "tracks" => "trak",
        "trafficway" => "trfy",
        "trail" => "trl",
        "trails" => "trl",
        "trk" => "trak",
        "trks" => "trak",
        "trls" => "trl",
        "trnpk" => "tpke",
        "trpk" => "tpke",
        "tunel" => "tunl",
        "tunls" => "tunl",
        "tunnel" => "tunl",
        "tunnels" => "tunl",
        "tunnl" => "tunl",
        "turnpike" => "tpke",
        "turnpk" => "tpke",
        "underpass" => "upas",
        "union" => "un",
        "unions" => "uns",
        "valley" => "vly",
        "valleys" => "vlys",
        "vally" => "vly",
        "vdct" => "via",
        "viadct" => "via",
        "viaduct" => "via",
        "view" => "vw",
        "views" => "vws",
        "vill" => "vlg",
        "villag" => "vlg",
        "village" => "vlg",
        "villages" => "vlgs",
        "ville" => "vl",
        "villg" => "vlg",
        "villiage" => "vlg",
        "vist" => "vis",
        "vista" => "vis",
        "vlly" => "vly",
        "vst" => "vis",
        "vsta" => "vis",
        "walks" => "walk",
        "well" => "wl",
        "wells" => "wls",
        "wy" => "way"
    );
    
    private $streetTypesList = array();
    
    private $stateCodes = array(
        "alabama" => "AL",
        "alaska" => "AK",
        "american samoa" => "AS",
        "arizona" => "AZ",
        "arkansas" => "AR",
        "california" => "CA",
        "colorado" => "CO",
        "connecticut" => "CT",
        "delaware" => "DE",
        "district of columbia" => "DC",
        "federated states of micronesia" => "FM",
        "florida" => "FL",
        "georgia" => "GA",
        "guam" => "GU",
        "hawaii" => "HI",
        "idaho" => "ID",
        "illinois" => "IL",
        "indiana" => "IN",
        "iowa" => "IA",
        "kansas" => "KS",
        "kentucky" => "KY",
        "louisiana" => "LA",
        "maine" => "ME",
        "marshall islands" => "MH",
        "maryland" => "MD",
        "massachusetts" => "MA",
        "michigan" => "MI",
        "minnesota" => "MN",
        "mississippi" => "MS",
        "missouri" => "MO",
        "montana" => "MT",
        "nebraska" => "NE",
        "nevada" => "NV",
        "new hampshire" => "NH",
        "new jersey" => "NJ",
        "new mexico" => "NM",
        "new york" => "NY",
        "north carolina" => "NC",
        "north dakota" => "ND",
        "northern mariana islands" => "MP",
        "ohio" => "OH",
        "oklahoma" => "OK",
        "oregon" => "OR",
        "palau" => "PW",
        "pennsylvania" => "PA",
        "puerto rico" => "PR",
        "rhode island" => "RI",
        "south carolina" => "SC",
        "south dakota" => "SD",
        "tennessee" => "TN",
        "texas" => "TX",
        "utah" => "UT",
        "vermont" => "VT",
        "virgin islands" => "VI",
        "virginia" => "VA",
        "washington" => "WA",
        "west virginia" => "WV",
        "wisconsin" => "WI",
        "wyoming" => "WY"
    );
    
    public $street_type_regexp;
    public $number_regexp;
    public $fraction_regexp;
    public $state_regexp;
    public $city_and_state_regexp;
    public $direct_regexp; 
    public $zip_regexp;
    public $corner_regexp;
    public $unit_regexp;
    public $street_regexp;
    public $place_regexp;
    public $address_regexp;
    public $informal_address_regexp;
    public $regex;
    
    public function __construct()
    {
        $this->setupStreetTypeList();
        $this->setupRegularExpressions();
    }
    
    private function setupStreetTypeList()
    {
        foreach ($this->streetTypes as $streetType => $streetTypeAbbr) {
            $this->streetTypesList[$streetType] = true;
            $this->streetTypesList[$streetTypeAbbr] = true;
        }
    }
    
    private function setupRegularExpressions()
    {
        $this->street_type_regexp = implode("|", array_keys($this->streetTypesList));
        $this->number_regexp = '\d+-?\d*';
        $this->fraction_regexp = '\d+\/\d+';
        $statesAndCodes = array();
        foreach($this->stateCodes as $state => $code) {
            $statesAndCodes[] = $state;
            $statesAndCodes[] = $code;
        }
        $this->state_regexp = preg_replace('/ /', "\\s", implode("|", $statesAndCodes));
        $this->city_and_state_regexp =
            '(?:'
            . '([^\d,]+?)\W+'
            . '(' . $this->state_regexp . ')'
            . ')';
        $directionalValues = array_values($this->directional);
        $expandedDirectionalValues = array();
        foreach($directionalValues as $directionalValue) {
            $expandedDirectionalValues[] = preg_replace('/(\w)/', "$1\\\\.", $directionalValue);
            $expandedDirectionalValues[] = $directionalValue;
        }        
        $this->direct_regexp = implode("|", array_keys($this->directional))
            . "|"
            . implode("|", $expandedDirectionalValues);

        $this->zip_regexp = '(\d{5})(?:-?(\d{4})?)';
        $this->corner_regexp = '(?:\band\b|\bat\b|&|\@)';
        $this->unit_regexp = '(?:(su?i?te|p\W*[om]\W*b(?:ox)?|dept|apt|apartment|ro*m|fl|unit|box)\W+|\#\W*)([\w-]+)';
        $this->street_regexp = 
            '(?:'
            . '(?:(' . $this->direct_regexp . ')\W+'
            . '(' . $this->street_type_regexp . ')\b)'
            . '|'
            . '(?:(' . $this->direct_regexp . ')\W+)?'
            . '(?:'
            . '([^,]+)'
            . '(?:[^\w,]+(' . $this->street_type_regexp . ')\b)'
            . '(?:[^\w,]+(' . $this->direct_regexp . ')\b)?'
            . '|'
            . '([^,]*\d)'
            . '(' . $this->direct_regexp . ')\b'
            . '|'
            . '([^,]+?)'
            . '(?:[^\w,]+(' . $this->street_type_regexp . ')\b)?'
            . '(?:[^\w,]+(' . $this->direct_regexp . ')\b)?'
            . ')'
            . ')';

        $this->place_regexp = 
            '(?:' . $this->city_and_state_regexp . '\W*)?'
            . '(?:' . $this->zip_regexp . ')?';

        $this->address_regexp =
            '\A\W*'
            . '(' . $this->number_regexp . ')\W*'
            . '(?:' . $this->fraction_regexp . '\W*)?'
            . $this->street_regexp . '\W+'
            . '(?:' . $this->unit_regexp . '\W+)?'
            . $this->place_regexp .
            '\W*\Z';

        $this->informal_address_regexp =
            '\A\s*'
            . '(' . $this->number_regexp . ')\W*'
            . '(?:' . $this->fraction_regexp . '\W*)?'
            . $this->street_regexp . '(?:\W+|\Z)'
            . '(?:' . $this->unit_regexp . '(?:\W+|\Z))?'
            . '(?:' . $this->place_regexp . ')?';
    }
    
    public function parse($address, $toString = false) {
        if ($toString) {
            return $this->parseToString($address);
        }
        return $this->parseToArray($address);
    }
    
    protected function parseToString($address) {
        $parsedAddress = $this->parseToArray($address);
        if($parsedAddress) {
            return $this->toString($parsedAddress);
        }
        return $address;
    }
    
    protected function parseToArray($address) {
        $match = array();
        preg_match('/' . $this->address_regexp . '/i', $address, $match);

        if (!$match) {
            return FALSE;
        }
        $street = @$match[5];
        if(!$street) {
            $street = @$match[10];
            if(!$street) {
                $street = @$match[2];
            }
        }
        
        $streetType = @$match[6];
        if(!$streetType) {
            $streetType = @$match[3];
        }
        
        $suffix = @$match[7];
        if(!$suffix) {
            $suffix = @$match[12];
        }
        
        $parsedAddress= array(
             'number' => @$match[1],
             'street' => $street,
             'street_type' => $streetType,
             'unit' => @$match[14],
             'unit_prefix' => @$match[13],
             'suffix' => $suffix,
             'prefix' => @$match[4],
             'city' => @$match[15],
             'state' => @$match[16],
             'postal_code' => @$match[17],
             'postal_code_ext' => @$match[18]
        );

        return $this->normalizeAddress($parsedAddress);
    }
    
    private function toString($add)
    {
        $line1 = $this->lineOne($add);
        $line2 = $this->lineTwo($add);
        if ($line1 && $line2) {
            return $line1 . ", " . $line2;
        }
        return $line1 . $line2;
    }
    
    private function lineOne($add)
    {
        $s = (string)$add['number'];
        $s .= $add['prefix'] ? " " . $add['prefix'] : "";
        $s .= $add['street'] ? " " . $add['street'] : "";
        $s .= $add['street_type'] ? " " . $add['street_type'] : "";
        $s .= $add['suffix'] ? " " . $add['suffix'] : "";
        if ($add['unit_prefix'] && $add['unit']) {
            $s .= " " . $add['unit_prefix'];
            $s .= " " . $add['unit'];
        } else if (!$add['unit_prefix'] && $add['unit'] ) {
            $s .= " #" . $add['unit'];
        }
        return $s;
    }
    
    private function lineTwo($add)
    {
        $s = (string)$add['city'];
        $s .= $add['state'] ? ", " . $add['state'] : "";
        $s .= $add['postal_code'] ? " " . $add['postal_code'] : "";
        $s .= $add['postal_code_ext'] ? "-" . $add['postal_code_ext'] : "";
        return $s;
    }
    
    private function normalizeAddress($addr)
    {
          $addr['state'] = (isset($addr['state'])) ? $this->normalizeState($addr['state']) : null;
          $addr['street_type'] = (isset($addr['street_type'])) ? $this->normalizeStreetType($addr['street_type']) : null;
          $addr['prefix'] = (isset($addr['prefix'])) ? $this->normalizeDirectional($addr['prefix']) : null;
          $addr['suffix'] = (isset($addr['suffix'])) ? $this->normalizeDirectional($addr['suffix']) : null;
          $addr['street'] = (isset($addr['street'])) ? ucwords($addr['street']) : null;                    
          $addr['street_type2'] = (isset($addr['street_type2'])) ? $this->normalizeStreetType($addr['street_type2']) : null;
          $addr['prefix2'] = (isset($addr['prefix2'])) ? $this->normalizeDirectional($addr['prefix2']) : null;
          $addr['suffix2'] = (isset($addr['suffix2'])) ? $this->normalizeDirectional($addr['suffix2']) : null;
          $addr['street2'] =  (isset($addr['street2'])) ? ucwords($addr['street2']) : null;
          $addr['city'] = (isset($addr['city'])) ? ucwords($addr['city']) : null;
          $addr['unit_prefix'] = (isset($addr['unit_prefix'])) ? ucfirst(strtolower($addr['unit_prefix'])) : null;
          return $addr;
    }
    
    private function normalizeState($state)
    {
        if(strlen($state) < 3) {
            return strtoupper($state);
        }
        $state = strtolower($state);
        if(isset($this->stateCodes[$state])) {
            return $this->stateCodes[$state];
        }
        return null;
    }
    
    private function normalizeStreetType($sType)
    {
        $sType = strtolower($sType);
        if(isset($this->streetTypes[$sType])) {
            return ucfirst($this->streetTypes[$sType]);
        }
        if(isset($this->streetTypesList[$sType])) {
            return ucfirst($sType);
        }
        return null;
    }
    
    private function normalizeDirectional($dir)
    {
        if(strlen($dir) < 3) {
            return strtoupper($dir);
        }
        $dir = strtolower($dir);
        if(isset($this->directional[$dir])) {
            return $this->directional[$dir];
        }
        return null;
    }
}