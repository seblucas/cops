<?php
/**
 * PHP renderer for doT templating engine 
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */


class doT {
    public $functionBody;
    private $functionCode;
    private $def;
    
    private function resolveDefs ($block) {
        return preg_replace_callback ("/\{\{#([\s\S]+?)\}\}/", function ($m) {
            $d = $m[1];
            $d = substr ($d, 4);
            if (!array_key_exists ($d, $this->def)) {
                return "";
            }
            if (preg_match ("/\{\{#([\s\S]+?)\}\}/", $this->def [$d])) {
                return $this->resolveDefs ($this->def [$d], $this->def);
            } else {
                return $this->def [$d];
            }
        }, $block);
    }
    
    private function handleDotNotation ($string) {
        $out = preg_replace ("/(\w+)\.(.*?)([\s,\)])/", "\$$1[\"$2\"]$3", $string);
        $out = preg_replace ("/(\w+)\.([\w\.]*?)$/", "\$$1[\"$2\"] ", $out);
        $out = preg_replace ("/\./", '"]["', $out);
        
        // Special hideous case : shouldn't be committed
        $out = preg_replace ("/^i /", ' $i ', $out);
        return $out;
    }
    
    public function template ($string, $def) {
        $func = preg_replace ("/'|\\\/", "\\$&", $string);

        // deps
        if (empty ($def)) {
            $func = preg_replace ("/\{\{#([\s\S]+?)\}\}/", "", $func);
        } else {
            $this->def = $def;
            $func = $this->resolveDefs ($func);
        }
        // interpolate
        $func = preg_replace_callback ("/\{\{=([\s\S]+?)\}\}/", function ($m) {
            return "' . " . $this->handleDotNotation ($m[1]) . " . '";
        }, $func);
        // Conditional
        $func = preg_replace_callback ("/\{\{\?(\?)?\s*([\s\S]*?)\s*\}\}/", function ($m) {
            $elsecase = $m[1];
            $code = $m[2];
            if ($elsecase) {
                if ($code) {
                    return "';} else if (" . $this->handleDotNotation ($code) . ") { $" . "out.='";
                } else {
                    return "';} else { $" . "out.='";
                }
            } else {
                if ($code) {
                    return "'; if (" . $this->handleDotNotation ($code) . ") { $" . "out.='";
                } else {
                    return "';} $" . "out.='";
                }
            }
        }, $func);
        // Iterate
        $func = preg_replace_callback ("/\{\{~\s*(?:\}\}|([\s\S]+?)\s*\:\s*([\w$]+)\s*(?:\:\s*([\w$]+))?\s*\}\})/", function ($m) {
            if (count($m) > 1) {
                $iterate = $m[1];
                $vname = $m[2];
                $iname = $m[3];
                $iterate = $this->handleDotNotation ($iterate); 
                return "'; for (\$$iname = 0; \$$iname < count($iterate); \$$iname++) { \$$vname = $iterate [\$$iname]; \$out.='";
            } else {
                return "';} $" . "out.='";
            }
        }, $func);
        $func = '$out = \'' . $func . '\'; return $out;';
        
        $this->functionBody = $func;
        
        //$this->functionCode = create_function ('$it', $func);
        return create_function ('$it', $func);
    }
    
    public function execute ($data) {
        return $this->functionCode ($data);
    }

}