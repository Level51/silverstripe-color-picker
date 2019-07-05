<?php

namespace Level51\ColorPicker;

use SilverStripe\Forms\FormField;
use SilverStripe\Forms\Validator;
use SilverStripe\View\Requirements;

/**
 * FormField showing a basic RGB color picker.
 *
 * The value will be stored in single database field as JSON string, e.g. {"R":"73","G":"128","B":"140"}.
 *
 * @package Level51\ColorPicker
 */
class ColorPickerField extends FormField {

    public function Field($properties = array()) {
        Requirements::javascript('level51/silverstripe-color-picker: client/dist/colorPickerField.js');
        Requirements::css('level51/silverstripe-color-picker: client/dist/colorPickerField.css');

        return parent::Field($properties);
    }

    /**
     * Get the payload passed to the vue component.
     *
     * @return string
     */
    public function getPayload() {
        return json_encode([
            'id'    => $this->ID(),
            'name'  => $this->getName(),
            'value' => $this->Value(),
            'i18n'  => $this->getFrontendI18NPayload()
        ]);
    }

    /**
     * Get a list of all labels used within the frontend.
     *
     * @return array
     */
    public function getFrontendI18NPayload() {
        $payload = [];
        $keys = [
            'ADD_RGB_VALUES'
        ];

        foreach ($keys as $key) {
            $payload[$key] = _t(__CLASS__ . '.' . $key);
        }

        return $payload;
    }

    /**
     * Turn the submitted value (array with 3 single values) into a JSON string for storage.
     *
     * @param array $value
     * @param null  $data
     *
     * @return $this
     */
    public function setSubmittedValue($value, $data = null) {
        return $this->setValue(json_encode($value), $data);
    }

    /**
     * Validate the input, ensure that each value is between -1 and 255.
     *
     * @param Validator $validator
     *
     * @return bool
     */
    public function validate($validator) {
        $values = $this->Value() ? json_decode($this->Value(), true) : null;

        if (!$values) return true;

        foreach ($values as $value) {
            if ($value === '') continue;

            $number = intval($value);
            if (($number === 0 && ($value !== "0")) || is_null($number) || $number > 255 || $number < -1) {
                $validator->validationError(
                    $this->getName(),
                    _t(__CLASS__ . '.ERR_INVALID_VALUE'),
                    "validation"
                );

                return false;
            }
        }

        return true;
    }
}
