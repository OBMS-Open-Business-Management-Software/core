<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'        => ':attribute muss akzeptiert werden.',
    'accepted_if'     => ':attribute muss akzeptiert werden, wenn :other :value ist.',
    'active_url'      => ':attribute ist keine gültige URL.',
    'after'           => ':attribute muss ein Datum nach :date sein.',
    'after_or_equal'  => ':attribute muss ein Datum nach oder gleich :date sein.',
    'alpha'           => ':attribute darf nur Buchstaben enthalten.',
    'alpha_dash'      => ':attribute darf nur Buchstaben, Zahlen, Bindestriche und Unterstriche enthalten.',
    'alpha_num'       => ':attribute darf nur Buchstaben und Zahlen enthalten.',
    'array'           => ':attribute muss ein Array sein.',
    'before'          => ':attribute muss ein Datum vor :date sein.',
    'before_or_equal' => ':attribute muss ein Datum vor oder gleich :date sein.',
    'between'         => [
        'numeric' => ':attribute muss zwischen :min und :max liegen.',
        'file'    => ':attribute muss zwischen :min und :max Kilobyte groß sein.',
        'string'  => ':attribute muss zwischen :min und :max Zeichen lang sein.',
        'array'   => ':attribute muss zwischen :min und :max Elemente haben.',
    ],
    'boolean'          => ':attribute Feld muss true oder false sein.',
    'confirmed'        => 'Die :attribute Bestätigung stimmt nicht überein.',
    'current_password' => 'Passwort ist falsch.',
    'date'             => ':attribute ist kein gültiges Datum.',
    'date_equals'      => ':attribute muss ein Datum gleich :date sein.',
    'date_format'      => ':attribute entspricht nicht dem Format :format.',
    'declined'         => ':attribute muss abgelehnt werden.',
    'declined_if'      => ':attribute muss abgelehnt werden, wenn :other :value ist.',
    'different'        => ':attribute und :other müssen unterschiedlich sein.',
    'digits'           => ':attribute muss :digits Ziffern haben.',
    'digits_between'   => ':attribute muss zwischen :min und :max Ziffern haben.',
    'dimensions'       => ':attribute hat ungültige Bildabmessungen.',
    'distinct'         => ':attribute Feld hat einen doppelten Wert.',
    'email'            => ':attribute muss eine gültige E-Mail-Adresse sein.',
    'ends_with'        => ':attribute muss mit einem der folgenden enden: :values.',
    'exists'           => 'ausgewählte :attribute ist ungültig.',
    'file'             => ':attribute muss eine Datei sein.',
    'filled'           => ':attribute Feld muss einen Wert haben.',
    'gt'               => [
        'numeric' => ':attribute muss größer als :value sein.',
        'file'    => ':attribute muss größer als :value Kilobyte sein.',
        'string'  => ':attribute muss größer als :value Zeichen sein.',
        'array'   => ':attribute muss mehr als :value Elemente haben.',
    ],
    'gte' => [
        'numeric' => ':attribute muss größer oder gleich :value sein.',
        'file'    => ':attribute muss größer oder gleich :value Kilobyte sein.',
        'string'  => ':attribute muss größer oder gleich :value Zeichen sein.',
        'array'   => ':attribute muss :value oder mehr Elemente haben.',
    ],
    'image'    => ':attribute muss ein Bild sein.',
    'in'       => 'ausgewählte :attribute ist ungültig.',
    'in_array' => ':attribute Feld existiert nicht in :other.',
    'integer'  => ':attribute muss eine ganze Zahl sein.',
    'ip'       => ':attribute muss eine gültige IP-Adresse sein.',
    'ipv4'     => ':attribute muss eine gültige IPv4-Adresse sein.',
    'ipv6'     => ':attribute muss eine gültige IPv6-Adresse sein.',
    'json'     => ':attribute muss eine gültige JSON-Zeichenkette sein.',
    'lt'       => [
        'numeric' => ':attribute muss kleiner als :value sein.',
        'file'    => ':attribute muss kleiner als :value Kilobyte sein.',
        'string'  => ':attribute muss kleiner als :value Zeichen sein.',
        'array'   => ':attribute muss weniger als :value Elemente haben.',
    ],
    'lte' => [
        'numeric' => ':attribute muss kleiner oder gleich :value sein.',
        'file'    => ':attribute muss kleiner oder gleich :value Kilobyte sein.',
        'string'  => ':attribute muss kleiner oder gleich :value Zeichen sein.',
        'array'   => ':attribute darf nicht mehr als :value Elemente haben.',
    ],
    'max' => [
        'numeric' => ':attribute darf nicht größer als :max sein.',
        'file'    => ':attribute darf nicht größer als :max Kilobyte sein.',
        'string'  => ':attribute darf nicht größer als :max Zeichen sein.',
        'array'   => ':attribute darf nicht mehr als :max Elemente haben.',
    ],
    'mimes'     => ':attribute muss eine Datei vom Typ: :values sein.',
    'mimetypes' => ':attribute muss eine Datei vom Typ: :values sein.',
    'min'       => [
        'numeric' => ':attribute muss mindestens :min sein.',
        'file'    => ':attribute muss mindestens :min Kilobyte groß sein.',
        'string'  => ':attribute muss mindestens :min Zeichen lang sein.',
        'array'   => ':attribute muss mindestens :min Elemente haben.',
    ],
    'multiple_of'          => ':attribute muss ein Vielfaches von :value sein.',
    'not_in'               => 'ausgewählte :attribute ist ungültig.',
    'not_regex'            => 'Format von :attribute ist ungültig.',
    'numeric'              => ':attribute muss eine Zahl sein.',
    'password'             => 'Passwort ist falsch.',
    'present'              => ':attribute Feld muss vorhanden sein.',
    'regex'                => 'Format von :attribute ist ungültig.',
    'required'             => ':attribute Feld ist erforderlich.',
    'required_if'          => ':attribute Feld ist erforderlich, wenn :other :value ist.',
    'required_unless'      => ':attribute Feld ist erforderlich, außer :other ist in :values.',
    'required_with'        => ':attribute Feld ist erforderlich, wenn :values vorhanden ist.',
    'required_with_all'    => ':attribute Feld ist erforderlich, wenn :values vorhanden sind.',
    'required_without'     => ':attribute Feld ist erforderlich, wenn :values nicht vorhanden ist.',
    'required_without_all' => ':attribute Feld ist erforderlich, wenn keine der :values vorhanden sind.',
    'prohibited'           => ':attribute Feld ist verboten.',
    'prohibited_if'        => ':attribute Feld ist verboten, wenn :other :value ist.',
    'prohibited_unless'    => ':attribute Feld ist verboten, außer :other ist in :values.',
    'prohibits'            => ':attribute Feld verbietet :other von der Anwesenheit.',
    'same'                 => ':attribute und :other müssen übereinstimmen.',
    'size'                 => [
        'numeric' => ':attribute muss :size sein.',
        'file'    => ':attribute muss :size Kilobyte groß sein.',
        'string'  => ':attribute muss :size Zeichen lang sein.',
        'array'   => ':attribute muss :size Elemente enthalten.',
    ],
    'starts_with' => ':attribute muss mit einem der folgenden beginnen: :values.',
    'string'      => ':attribute muss eine Zeichenkette sein.',
    'timezone'    => ':attribute muss eine gültige Zeitzone sein.',
    'unique'      => ':attribute wurde bereits verwendet.',
    'uploaded'    => ':attribute konnte nicht hochgeladen werden.',
    'url'         => ':attribute muss eine gültige URL sein.',
    'uuid'        => ':attribute muss eine gültige UUID sein.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
