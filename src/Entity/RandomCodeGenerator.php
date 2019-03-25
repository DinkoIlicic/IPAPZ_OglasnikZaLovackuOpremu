<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/20/19
 * Time: 3:21 PM
 */

namespace App\Entity;

class RandomCodeGenerator
{

    private $fromChars = "0123456789abcdefghij";
    private $toChars = "234679QWERTYUPADFGHX";
    private $length = 10;
    private $useLetters = true;
    private $map = [
        "2" => 0,
        "3" => 1,
        "4" => 2,
        "6" => 3,
        "7" => 4,
        "9" => 5,
        "Q" => 6,
        "W" => 7,
        "E" => 8,
        "R" => 9,
        "T" => 10,
        "Y" => 11,
        "U" => 12,
        "P" => 13,
        "A" => 14,
        "D" => 15,
        "F" => 16,
        "G" => 17,
        "H" => 18,
        "X" => 19
    ];

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * @return boolean
     */
    public function isUsingLetters()
    {
        return $this->useLetters;
    }

    /**
     * @param boolean $useLetters
     */
    public function useLetters($useLetters)
    {
        $this->useLetters = $useLetters;
    }

    /**
     * @param  integer $amount
     * @return \SplFixedArray
     */
    public function generate($amount)
    {
        $codes = [];
        $number = 0;
        $string = "";
        $finalCode = str_repeat('-', $this->length);
        $completeCodes = new \SplFixedArray($amount);
        $index = 0;

        do {
            $number = bin2hex(openssl_random_pseudo_bytes($this->length));
            if ($this->useLetters) {
                $string = base_convert($number, 16, 20);
                $string = strtr($string, $this->fromChars, $this->toChars);
            } else {
                $string = base_convert($number, 16, 10);
            }

            $string = substr($string, 0, $this->length);
            $chars = str_split($string);

            if (!$this->isInArray($chars, $codes, 0)) {
                $finalCode = str_repeat('-', $this->length);
                $this->addToarray($chars, $codes, 0, $finalCode);
                $completeCodes[$index] = $finalCode;
                $index++;
            }
        } while ($index < $amount);

        unset($codes);
        return $completeCodes;
    }

    /**
     * @param  array $chars
     * @param  array $codes
     * @param  integer $index
     * @return bool
     */
    private function isInArray(&$chars, &$codes, $index)
    {
        if ($index < $this->length - 2 && !is_array($codes)) {
            if ($codes == implode("", array_slice($chars, $index + 1))) {
                return true;
            }

            return false;
        }

        if (!isset($codes[$this->map[$chars[$index]]])) {
            return false;
        } else {
            if ($index == $this->length - 2) {
                return $codes[$this->map[$chars[$index]]] == $chars[$index + 1];
            }

            if ($index < $this->length - 2) {
                return $this->isInArray($chars, $codes[$this->map[$chars[$index]]], ++$index);
            }
        }

        return false;
    }

    /**
     * @param array $chars
     * @param array $codes
     * @param integer $index
     * @param string $finalCode
     */
    private function addToarray(&$chars, &$codes, $index, &$finalCode)
    {
        if ($index < $this->length - 2) {
            if (!is_array($codes)) {
                $code = $codes;
                $codes = [];
                $codes[$this->map[$code[0]]] = substr($code, 1);
                $finalCode = substr($finalCode, 0, $index) . implode("", array_slice($chars, $index));
                return;
            }

            if (count($codes) == 1 && isset($codes[0]) && !is_array($codes[0])) {
                $code = $codes[0];
                unset($codes[0]);
                $codes[$this->map[$code[0]]] = substr($code, 1);
            }

            if (!isset($codes[$this->map[$chars[$index]]])) {
                $codes[$this->map[$chars[$index]]][] = implode("", array_slice($chars, $index + 1));
                $finalCode = substr($finalCode, 0, $index) . implode("", array_slice($chars, $index));
                return;
            }

            $finalCode[$index] = $chars[$index];

            $this->addToarray($chars, $codes[$this->map[$chars[$index]]], ++$index, $finalCode);
        } else {
            $codes[$this->map[$chars[$index]]][] = $chars[$index + 1];
            $finalCode[$index] = $chars[$index];
            $finalCode[$index + 1] = $chars[$index + 1];
        }
    }
}
