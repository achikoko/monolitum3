<?php

namespace monolitum\model\values;

class Color
{

    /** @var int */
    private int $red, $green, $blue;
    private ?int $alpha;

    public function __construct(int $red=0, int $green=0, int $blue=0, int $alpha=null)
    {
        $this->red = self::fix_rgb_value($red);
        $this->green = self::fix_rgb_value($green);
        $this->blue = self::fix_rgb_value($blue);
        if($alpha !== null)
            $this->alpha = self::fix_rgb_value($alpha);

    }

    public function getRed(): int
    {
        return $this->red;
    }

    public function setRed(mixed $red)
    {
        $this->red = self::fix_rgb_value($red);
    }

    public function getGreen(): int
    {
        return $this->green;
    }

    public function setGreen(mixed $green)
    {
        $this->green = self::fix_rgb_value($green);
    }

    public function getBlue(): int
    {
        return $this->blue;
    }

    public function setBlue(mixed $blue)
    {
        $this->blue = self::fix_rgb_value($blue);
    }

    public function getAlpha(): ?int
    {
        return $this->alpha;
    }

    public function setAlpha(mixed $alpha)
    {
        $this->alpha = self::fix_rgb_value($alpha);
    }



    /**
     * Set the colour's red, green and blue values
     *
     * @access public
     * @param bool|integer|null $red [optional] The red value of the colour (between 0 and 255). Default value is 0.
     * @param integer|bool|null $green [optional] The green value of the colour (between 0 and 255). Default value is 0.
     * @param integer|bool|null $blue [optional] The blue value of the colour (between 0 and 255). Default value is 0.
     * @param integer|bool|null $alpha [optional] The blue value of the colour (between 0 and 255). Default value is 0.
     */
    public function set(bool|int|null $red=0, bool|int|null $green=0, bool|int|null $blue=0, bool|int|null $alpha=null): void
    {

        // add values
        if($red !== null) {
            if($this->red === false)
                $this->red = 0;
            else
                $this->red = self::fix_rgb_value($red);
        }
        if($green !== null) {
            if($this->green === false)
                $this->green = 0;
            else
                $this->green = self::fix_rgb_value($green);
        }
        if($blue !== null) {
            if($this->blue === false)
                $this->blue = 0;
            else
            $this->blue = self::fix_rgb_value($blue);
        }

        if($alpha !== null){
            if($this->alpha === false)
                $this->alpha = null;
            else
                $this->alpha = self::fix_rgb_value($alpha);
        }

    }

    /**
     * Modify the colour's red, green and blue values.
     *
     * @access public
     * @param integer $red [optional] The amount to modify the red value of the colour (between -255 and 255). Default value is 0.
     * @param integer $green [optional] The amount to modify the green value of the colour (between -255 and 255). Default value is 0.
     * @param integer $blue [optional] The amount to modify the blue value of the colour (between -255 and 255). Default value is 0.
     */
    public function add($red=0, $green=0, $blue=0, $alpha=null){

        // add values
        if($red !== null) $this->red = self::fix_rgb_value($this->red + (int)$red);
        if($green !== null) $this->green = self::fix_rgb_value($this->green + (int)$green);
        if($blue !== null) $this->blue = self::fix_rgb_value($this->blue + (int)$blue);

        if($alpha !== null){
            if($this->alpha === null)
                $this->alpha = 255;
            $this->alpha = self::fix_rgb_value($this->alpha + (int)$alpha);
        }

    }

    /**
     * Get the HEX code that represents the colour.
     *
     * @access public
     * @param boolean $hash Whether to prepend the HEX code with a '#' character. Default value is FALSE.
     * @return string Returns the HEX code.
     */
    public function getHexValue($hash=false){

        // convert rgb to hex
        $red = str_pad(dechex(self::fix_rgb_value($this->red)), 2, '0', STR_PAD_LEFT);
        $green = str_pad(dechex(self::fix_rgb_value($this->green)), 2, '0', STR_PAD_LEFT);
        $blue = str_pad(dechex(self::fix_rgb_value($this->blue)), 2, '0', STR_PAD_LEFT);

        if($this->alpha !== null){
            $alpha = str_pad(dechex(self::fix_rgb_value($this->alpha)), 2, '0', STR_PAD_LEFT);
            // concat and return
            return ($hash ? '#' : '') . $red . $green . $blue . $alpha;
        }else{
            // concat and return
            return ($hash ? '#' : '') . $red . $green . $blue;
        }

    }


    static function black($alpha=255){
        return new Color(0,0,0, $alpha);
    }

    static function white($alpha=255){
        return new Color(255,255,255, $alpha);
    }

    static function fromHex($hex){

        // trim the '#' character
        $hex = ltrim((string)$hex, '#');

        $red = null;
        $green = null;
        $blue = null;
        $alpha = null;

        if(!preg_match('/^[0-9a-f]{3,8}$/i', $hex)){
            return null;
        }

        // what kind of code do we have?
        if (strlen($hex)==8){

            // parse 6-character code into array
            $red = $hex[0] . $hex[1];
            $green = $hex[2] . $hex[3];
            $blue = $hex[4] . $hex[5];
            $alpha = $hex[6] . $hex[7];

        }
        else if (strlen($hex)==6){

            // parse 6-character code into array
            $red = $hex[0] . $hex[1];
            $green = $hex[2] . $hex[3];
            $blue = $hex[4] . $hex[5];

        }
        else if (strlen($hex)==3){

            // parse 3 character code into array
            $red = $hex[0] . $hex[0];
            $green = $hex[1] . $hex[1];
            $blue = $hex[2] . $hex[2];

        }
        else{

            // invalid code... oops
            return null;

        }

        return new Color(
            hexdec($red),
            hexdec($green),
            hexdec($blue),
            hexdec($alpha)
        );

    }


    /**
     * Fix a colour value (round and keep between 0 and 255).
     *
     * @access protected
     * @param integer $value The value to fix.
     */
    protected static function fix_rgb_value(int $value){

        // returned fixed value
        return max(min(round((int)$value), 255), 0);

    }

    public static function ofRandom(){
        return new Color(rand(0, 255),rand(0, 255),rand(0, 255));
    }


}
