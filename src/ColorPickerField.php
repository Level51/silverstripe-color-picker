<?php

namespace Level51\ColorPicker;

use SilverStripe\Forms\FormField;
use SilverStripe\Forms\Validator;
use SilverStripe\View\Requirements;

/**
 * FormField showing a basic RGB color picker.
 *
 * In RGB mode, the value will be stored in single database field as JSON string, e.g. {"R":"73","G":"128","B":"140"}.
 * In Hex mode, the value will be a single string - so no manipulation needed here.
 *
 * @package Level51\ColorPicker
 */
class ColorPickerField extends FormField
{

    public const MODE_RGB = 'rgb';
    public const MODE_HEX = 'hex';

    /**
     * @var bool Whether or not a "Add RGB color values" is shown.
     */
    private $showCheckbox = true;

    /** @var string The color picker mode */
    private $mode = 'rgb';

    public function Field($properties = array())
    {
        Requirements::javascript('level51/silverstripe-color-picker: client/dist/colorPickerField.js');
        Requirements::css('level51/silverstripe-color-picker: client/dist/colorPickerField.css');

        return parent::Field($properties);
    }

    /**
     * Get the payload passed to the vue component.
     *
     * @return string
     */
    public function getPayload()
    {
        return json_encode(
            [
                'id'           => $this->ID(),
                'name'         => $this->getName(),
                'value'        => $this->Value(),
                'i18n'         => $this->getFrontendI18NPayload(),
                'mode'         => $this->mode,
                'showCheckbox' => $this->showCheckbox
            ]
        );
    }

    /**
     * Get a list of all labels used within the frontend.
     *
     * @return array
     */
    public function getFrontendI18NPayload()
    {
        $payload = [];
        $keys = [
            'ADD_RGB_VALUES'
        ];

        foreach ($keys as $key) {
            $payload[$key] = _t(__CLASS__ . '.' . $key);
        }

        return $payload;
    }

    public function setSubmittedValue($value, $data = null)
    {
        if ($this->mode === self::MODE_RGB) {
            return $this->setSubmittedRGBValue($value, $data);
        }

        return parent::setSubmittedValue($value, $data);
    }

    /**
     * Turn the submitted rgb value (array with 3 single values) into a JSON string for storage.
     *
     * @param array $value
     * @param null  $data
     *
     * @return $this
     */
    public function setSubmittedRGBValue($value, $data = null)
    {
        if (!$value) {
            return $this;
        }

        // trim whitespaces
        foreach ($value as $key => $v) {
            $value[$key] = trim($v);
        }

        return $this->setValue(json_encode($value), $data);
    }

    public function validate($validator)
    {
        if ($this->mode === self::MODE_RGB) {
            return $this->validateRGBMode($validator);
        }

        if ($this->mode === self::MODE_HEX) {
            return $this->validateHexMode($validator);
        }

        return true;
    }

    /**
     * Validate the input in RGB mode, ensure that each value is between -1 and 255.
     *
     * @param Validator $validator
     *
     * @return bool
     */
    private function validateRGBMode($validator)
    {
        $values = $this->Value() ? json_decode($this->Value(), true) : null;

        if (!$values) {
            return true;
        }

        foreach ($values as $value) {
            if ($value === '') {
                continue;
            }

            $number = intval($value);
            if (($number === 0 && ($value !== "0")) || is_null($number) || $number > 255 || $number < -1) {
                $validator->validationError(
                    $this->getName(),
                    _t(__CLASS__ . '.ERR_INVALID_RGB_VALUE'),
                    "validation"
                );

                return false;
            }
        }

        return true;
    }

    /**
     * Validate the input in Hex mode, ensure that the value is a string with exactly 7 chars.
     *
     * @param Validator $validator
     *
     * @return bool
     */
    private function validateHexMode($validator)
    {
        $value = $this->Value();

        if ($value && is_string($value) && strlen($value) !== 7) {
            $validator->validationError(
                $this->getName(),
                _t(__CLASS__ . '.ERR_INVALID_HEX_VALUE'),
                'validation'
            );

            return false;
        }

        return true;
    }

    /**
     * Disable the "Add RGB color values" checkbox, so instantly show the color picker.
     *
     * @return $this
     */
    public function disableCheckbox()
    {
        $this->showCheckbox = false;

        return $this;
    }

    /**
     * Change the color picker mode, see MODE_ constants.
     *
     * @param string $mode
     *
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }
}
