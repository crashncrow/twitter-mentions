<?php
class Tools{
    /**
    * Tu nombre de usuario no puede superar los 15 caracteres.
    * Un nombre de usuario solo puede contener caracteres alfanuméricos (letras de la A a la Z, números de 0 a 9)
    * con la sola excepción de los guiones bajos.
    */
    public static function validateUser($username) {
        return preg_match('/^[A-Za-z0-9_]{1,15}$/', $username);
    }

    public static function cleanText($string){

        //Quitamos las ñ
        $string = str_replace('ñ', 'n', $string);
        $string = str_replace('Ñ', 'n', $string);

        //Quitamos algunos simbolos
        $string = str_replace(',', '', $string);
        $string = str_replace(';', '', $string);
        $string = str_replace('(', '', $string);
        $string = str_replace(')', '', $string);
        $string = str_replace('[', '', $string);
        $string = str_replace(']', '', $string);
        $string = str_replace('{', '', $string);
        $string = str_replace('}', '', $string);
        $string = str_replace('-', '', $string);
        $string = str_replace('|', '', $string);
        $string = str_replace('"', '', $string);
        $string = str_replace('?', '', $string);
        $string = str_replace('¿', '', $string);
        $string = str_replace('!', '', $string);
        $string = str_replace('¡', '', $string);
        $string = str_replace('.', '', $string);
        $string = str_replace('·', '', $string);
        $string = str_replace('º', '', $string);
        $string = str_replace('%', '', $string);
        $string = str_replace('¨', '', $string);
        $string = str_replace('«', '', $string);
        $string = str_replace('»', '', $string);
        $string = str_replace('>', '', $string);
        $string = str_replace('<', '', $string);
        $string = str_replace('+', '', $string);
        $string = str_replace('@', '', $string);
        $string = str_replace('#', '', $string);
        $string = str_replace('=', '', $string);

        //Quitamos los numeros
        $string = str_replace('0', '', $string);
        $string = str_replace('1', '', $string);
        $string = str_replace('2', '', $string);
        $string = str_replace('3', '', $string);
        $string = str_replace('4', '', $string);
        $string = str_replace('5', '', $string);
        $string = str_replace('6', '', $string);
        $string = str_replace('7', '', $string);
        $string = str_replace('8', '', $string);
        $string = str_replace('9', '', $string);

        //Reemplazamos los acentos (ahora NO está funcionando)
        $string = str_replace('á', 'a', $string);
        $string = str_replace('é', 'e', $string);
        $string = str_replace('í', 'i', $string);
        $string = str_replace('ó', 'o', $string);
        $string = str_replace('ú', 'u', $string);

        $string = str_replace('Á', 'a', $string);
        $string = str_replace('É', 'e', $string);
        $string = str_replace('Í', 'i', $string);
        $string = str_replace('Ó', 'o', $string);
        $string = str_replace('Ú', 'u', $string);

        //Borramos links
        $regex = "@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@";
        $string =  preg_replace($regex, ' ', $string);

        $regex = "@(http?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@";
        $string = preg_replace($regex, ' ', $string);

        //Quitamos symbols luego de quitar las urls
        $string = str_replace(':', '', $string);
        $string = str_replace('/', '', $string);

        //Quitamos los enters
        $string = str_replace(array("\r", "\n"), '', $string);

        //Lowercase
        $string = strtolower($string);

        //Quiramos RT
        $string = str_replace('rt ', ' ', $string);

        $string = addslashes(utf8_decode($string));


        //TODO: mejorar las stopwords
        $pre = array(
            "a", "ante", "bajo", "cabe", "con", "contra", "de", "desde", "en",
            "entre", "hacia", "hasta", "para", "por", "según", "sin", "so",
            "sobre", "tras", "durante", "mediante", "versus", "via",
            "yo", "tu", "vos", "usted", "el", "ella", "ello", "nosotros",
            "nosotras", "vosotros", "vosotras", "ustedes", "ellos", "ellas",
            "mi", "ti", "si", "no", "consigo", "me", "te", "se", "lo", "la", "le",
            "se", "nos", "os", "los", "las", "y", "que", "todo", "muy", "hs",
            "hoy", "sale", "un", "una", "mientras", "poco", "mucho", "del", "fue",
            "alli", "ahora", "dia", "ht", "tus", "ya", "ir", "dias", "dia", "su",
            "al", "este", "hay", "aca", "son", "esta", "sus", "mas", "o", "u",
            "asi", "es", "les", "https", "http", "anda", "$", "podes", "he", "n",
            "the", "e", "tambien", "&gt", "am", "pm", "mira", "l", "c" , "unlp", "como"
        );

        foreach($pre as $p){
            $string = preg_replace('/\b'.$p.'\b/', '', $string);
        }

        //Quitamos espacios en blanco
        $string = preg_replace('!\s+!', ' ', $string);
        $string = trim($string);

        return $string;
    }

    public static function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}
