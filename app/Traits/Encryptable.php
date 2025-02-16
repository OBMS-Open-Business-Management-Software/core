<?php

namespace App\Traits;

/**
 * Trait Encryptable
 *
 * This trait enables attribute encryption for models.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
trait Encryptable
{
    /**
     * If the attribute is in the encryptable array
     * then decrypt it.
     *
     * @param  $key
     *
     * @return mixed $value
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (
            in_array($key, $this->encryptable) &&
            !empty($value)
        ) {
            $value = decrypt($value);
        }

        return $value;
    }

    /**
     * If the attribute is in the encryptable array
     * then encrypt it.
     *
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable)) {
            $value = encrypt($value);
        }

        return parent::setAttribute($key, $value);
    }
    /**
     * When need to make sure that we iterate through
     * all the keys.
     *
     * @return array
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        foreach ($this->encryptable as $key) {
            if (isset($attributes[$key])) {
                $attributes[$key] = decrypt($attributes[$key]);
            }
        }

        return $attributes;
    }
}
