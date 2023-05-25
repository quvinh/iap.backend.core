<?php
namespace App\Helpers\Utils;

use App\Helpers\Common\Constants;

class StringHelper{
    const GMP = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

    /**
     * Handle search string when has character: "%" and "_"
     * @param string $str
     * @return string
     */
    public static function escapeLikeQueryParameter(string $str): string
    {
        $str = str_replace('%', '\%', $str);
        $str = str_replace('_', '\_', $str);
        $str = str_replace('--', '\--', $str);
        $str = str_replace("'", "\'", $str);
        return str_replace('"', '\"', $str);
    }

    /**
     * Normalize the given string
     * @param $str
     * @return string
     */
    public static function normalize($str): string
    {
        $str = ($str == null)? "" : $str;
        $str = trim($str);
        while (strpos($str, '  ')) $str = str_replace('  ', ' ', $str);
        return $str;
    }

    /**
     * Convert the given string to ASCII and uppercase output
     * @param string $str
     * @return string
     */
    public static function toASCIIUperCases(string $str): string
    {
        mb_internal_encoding("UTF-8");
        $vocabulary = Constants::getVOCALBULARY();
        $str = StringHelper::normalize($str);
        $str = mb_strtolower($str);
        $items = mb_str_split($str);
        if (!is_array($items)) return "";
        for($i = 0; $i < count($items); $i++) {
            $key = $items[$i];
            $items[$i] =  (array_key_exists($key, $vocabulary))? $vocabulary[$key] : strtoupper($key);
        }

        return implode("", $items);
    }

    /**
     * Return the list of vowels appear in the given string, which follows numerologist concepts.
     * @param string $str
     * @return string
     */
    public static function extractVowels(string $str): string
    {
        $str = self::toASCIIUperCases($str);
        $ret = "";
        $items = mb_str_split($str);
        $vowels = Constants::getVowels();
        $consonants = Constants::getConsonants();
        for($i = 0; $i < count($items); $i++) {
            $key = $items[$i];
            if (in_array($key, $vowels)) { $ret = $ret.$key; continue; }
            if($key == 'Y'){
                // Nếu trước Y không có ký tự nào
                if(!isset($items[$i - 1]) || $items[$i-1] == " "){
                    // Nếu sau Y không có ký tự nào thì Y là Nguyên Âm
                    if(!isset($items[$i+1])){
                        $ret = $ret.$key; continue;
                    }
                    // Nếu sau Y có ký tự
                    if(isset($items[$i+1])){
                        // Nếu ký tự đó là khoảng trống thì Y là Nguyên Âm
                        if($items[$i+1] == " "){
                            $ret = $ret.$key; continue;
                        }
                        // Nếu ký tự đó là phụ âm thì Y là Nguyên Âm
                        if(in_array($items[$i+1], $consonants)) { $ret = $ret.$key; continue; }
                    }
                }else{
                    // Nếu trước Y là phụ âm
                    if(in_array($items[$i - 1],$consonants)){
                        // Nếu sau Y là khoảng trống hoặc không còn ký tự nào thì Y là Nguyên Âm
                        if(!isset($items[$i+1]) || $items[$i+1] == " "){
                            $ret = $ret.$key; continue;
                        }else if(in_array($items[$i + 1],$consonants)){ // Nếu sau Y là phụ âm thì Y là nguyên âm
                            $ret = $ret.$key; continue;
                        }else{
                            continue;
                        }
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * Return the list of consonants appear in the given string, which follows numerologist concepts.
     * @param string $str
     * @return string
     */
    public static function extractConsonants(string $str): string
    {
        $str = self::toASCIIUperCases($str);
        $ret = "";
        $items = mb_str_split($str);
        $vowels = Constants::getVowels();
        $consonants = Constants::getConsonants();
        $previousIsVowel = false;
        for($i = 0; $i < count($items); $i++) {
            $key = $items[$i];
            if (in_array($key, $consonants))  { $ret = $ret.$key; $previousIsVowel = false; continue; }
            if($key == "Y"){
                // Nếu trước Y không có ký tự nào
                if(!isset($items[$i - 1]) || $items[$i-1] == " "){
                    // Nếu sau Y không có ký tự nào thì Y là không là Phụ Âm
                    if(!isset($items[$i+1])){
                        continue;
                    }
                    // Nếu sau Y có ký tự
                    if(isset($items[$i+1])){
                        // Nếu ký tự đó là khoảng trống thì Y là không là Phụ Âm
                        if($items[$i+1] == " "){
                            continue;
                        }
                        // Nếu ký tự đó là nguyên âm thì Y là Phụ Âm
                        if(in_array($items[$i+1], $vowels)) { $ret = $ret.$key; continue; }
                    }
                }else{
                    // Nếu trước Y là nguyên âm
                    if(in_array($items[$i - 1],$vowels)){
                        $ret = $ret.$key; continue;
                    }else{ // Nếu trước Y là phụ âm
                        // Nếu sau Y là khoảng trống hoặc không còn ký tự nào thì Y không là Phụ Âm
                        if(!isset($items[$i+1]) || $items[$i+1] == " "){
                            continue;
                        }else if(in_array($items[$i + 1],$vowels)){ // Nếu sau Y là nguyên âm thì Y là Phụ âm
                            $ret = $ret.$key; continue;
                        }else{
                            continue;
                        }
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * Convert a string to its numerical representation
     * @param $str
     * @return array<int>
     */
    public  static function toPythagoreNumbers($str): array{
        $str = self::toASCIIUperCases($str);
        $pythagore = Constants::getPYTHAGORE();
        $items = mb_str_split($str);
        for($i = 0; $i < count($items); $i++) {
            $key = $items[$i];
            $items[$i] =  (array_key_exists($key, $pythagore))? $pythagore[$key] : 0;
        }
        return $items;
    }

    /**
     * Return the first character of each words
     * @param string $str
     * @return string
     */
    public static function getFirstCharacterOfWords(string $str): string
    {
        $items = preg_split('/\s+/', $str);
        $ret = "";
        foreach ($items as $item)
            if (strlen($item) > 0) {
                $list =  mb_str_split($item);//preg_split('', $item, -1, PREG_SPLIT_NO_EMPTY);
                $ret .= $list[0];
            }
        return $ret;
    }

    /**
     * Generate a random string
     * @param int $len
     * @param string $root
     * @return string
     */
    public  static function randAlphanumericString(int $len, string $root = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890'): string
    {
        $len = ($len && $len > 0)? $len : 0;
        if ($len == 0) return "";
        $arr = str_split($root); // get all the characters into an array
        shuffle($arr); // randomize the array
        $arr = array_slice($arr, 0, $len); // get the first six (random) characters out
        $str = implode('', $arr); // mush them back into a string
        return $str;
    }

    /**
     * Return the next character in alphabet orders.
     * @param string $data
     * @return string
     */
    public static function nextGMPDigit(string $data): string
    {
        $i = strpos(StringHelper::GMP, $data);
        if (($i !== NULL) && $i != strlen(StringHelper::GMP) - 1) {
            return StringHelper::GMP[$i + 1];
        } else {
            return "";
        }
    }
}
?>
