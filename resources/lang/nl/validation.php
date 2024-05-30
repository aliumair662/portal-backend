<?php

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

    'accepted' => 'De :attribute moet worden geaccepteerd.',
    'accepted_if' => 'De :attribute moet worden geaccepteerd wanneer :other :value is.',
    'active_url' => 'De :attribute is geen geldige URL.',
    'after' => 'De :attribute moet een datum zijn na :date.',
    'after_or_equal' => 'De :attribute moet een datum zijn na of gelijk aan :date.',
    'alpha' => 'De :attribute mag alleen letters bevatten.',
    'alpha_dash' => 'De :attribute mag alleen letters, cijfers, streepjes en underscores bevatten.',
    'alpha_num' => 'De :attribute mag alleen letters en cijfers bevatten.',
    'array' => 'De :attribute moet een array zijn.',
    'before' => 'De :attribute moet een datum zijn voor :date.',
    'before_or_equal' => 'De :attribute moet een datum zijn voor of gelijk aan :date.',
    'between' => [
        'numeric' => 'De :attribute moet tussen :min en :max zijn.',
        'file' => 'De :attribute moet tussen :min en :max kilobytes zijn.',
        'string' => 'De :attribute moet tussen :min en :max karakters zijn.',
        'array' => 'De :attribute moet tussen :min en :max items bevatten.',
    ],
    'boolean' => 'Het :attribute veld moet waar of onwaar zijn.',
    'confirmed' => 'De :attribute bevestiging komt niet overeen.',
    'current_password' => 'Het wachtwoord is incorrect.',
    'date' => 'De :attribute is geen geldige datum.',
    'date_equals' => 'De :attribute moet een datum zijn gelijk aan :date.',
    'date_format' => 'De :attribute komt niet overeen met het formaat :format.',
    'declined' => 'De :attribute moet worden geweigerd.',
    'declined_if' => 'De :attribute moet worden geweigerd wanneer :other :value is.',
    'different' => 'De :attribute en :other moeten verschillend zijn.',
    'digits' => 'De :attribute moet :digits cijfers zijn.',
    'digits_between' => 'De :attribute moet tussen :min en :max cijfers zijn.',
    'dimensions' => 'De :attribute heeft ongeldige afbeeldingsafmetingen.',
    'distinct' => 'Het :attribute veld heeft een dubbele waarde.',
    'email' => 'De :attribute moet een geldig e-mailadres zijn.',
    'ends_with' => 'De :attribute moet eindigen met een van de volgende waarden: :values.',
    'enum' => 'De geselecteerde :attribute is ongeldig.',
    'exists' => 'De geselecteerde :attribute is ongeldig.',
    'file' => 'De :attribute moet een bestand zijn.',
    'filled' => 'Het :attribute veld moet een waarde bevatten.',
    'gt' => [
        'numeric' => 'De :attribute moet groter zijn dan :value.',
        'file' => 'De :attribute moet groter zijn dan :value kilobytes.',
        'string' => 'De :attribute moet groter zijn dan :value karakters.',
        'array' => 'De :attribute moet meer dan :value items bevatten.',
    ],

    'gte' => [
        'numeric' => 'De :attribute moet groter zijn dan of gelijk zijn aan :value.',
        'file' => 'De :attribute moet groter zijn dan of gelijk zijn aan :value kilobytes.',
        'string' => 'De :attribute moet groter zijn dan of gelijk zijn aan :value karakters.',
        'array' => 'De :attribute moet :value items of meer bevatten.',
    ],
    'image' => 'De :attribute moet een afbeelding zijn.',
    'in' => 'De geselecteerde :attribute is ongeldig.',
    'in_array' => 'Het :attribute veld bestaat niet in :other.',
    'integer' => 'De :attribute moet een geheel getal zijn.',
    'ip' => 'De :attribute moet een geldig IP-adres zijn.',
    'ipv4' => 'De :attribute moet een geldig IPv4-adres zijn.',
    'ipv6' => 'De :attribute moet een geldig IPv6-adres zijn.',
    'json' => 'De :attribute moet een geldige JSON-string zijn.',
    'lt' => [
        'numeric' => 'De :attribute moet kleiner zijn dan :value.',
        'file' => 'De :attribute moet kleiner zijn dan :value kilobytes.',
        'string' => 'De :attribute moet kleiner zijn dan :value karakters.',
        'array' => 'De :attribute moet minder dan :value items bevatten.',
    ],
    'lte' => [
        'numeric' => 'De :attribute moet kleiner zijn dan of gelijk zijn aan :value.',
        'file' => 'De :attribute moet kleiner zijn dan of gelijk zijn aan :value kilobytes.',
        'string' => 'De :attribute moet kleiner zijn dan of gelijk zijn aan :value karakters.',
        'array' => 'De :attribute mag niet meer dan :value items bevatten.',
    ],
    'mac_address' => 'De :attribute moet een geldig MAC-adres zijn.',
    'max' => [
        'numeric' => 'De :attribute mag niet groter zijn dan :max.',
        'file' => 'De :attribute mag niet groter zijn dan :max kilobytes.',
        'string' => 'De :attribute mag niet groter zijn dan :max karakters.',
        'array' => 'De :attribute mag niet meer dan :max items bevatten.',
    ],
    'mimes' => 'De :attribute moet een bestand zijn van het type: :values.',
    'mimetypes' => 'De :attribute moet een bestand zijn van het type: :values.',
    'min' => [
        'numeric' => 'De :attribute moet minimaal :min zijn.',
        'file' => 'De :attribute moet minimaal :min kilobytes zijn.',
        'string' => 'De :attribute moet minimaal :min karakters zijn.',
        'array' => 'De :attribute moet minimaal :min items bevatten.',
    ],
    'multiple_of' => 'De :attribute moet een veelvoud zijn van :value.',
    'not_in' => 'De geselecteerde :attribute is ongeldig.',
    'not_regex' => 'Het formaat van :attribute is ongeldig.',
    'numeric' => 'De :attribute moet een nummer zijn.',
    'password' => 'Het wachtwoord is incorrect.',
    'present' => 'Het :attribute veld moet aanwezig zijn.',
    'prohibited' => 'Het :attribute veld is verboden.',
    'prohibited_if' => 'Het :attribute veld is verboden wanneer :other gelijk is aan :value.',
    'prohibited_unless' => 'Het :attribute veld is verboden, tenzij :other voorkomt in :values.',
    'prohibits' => 'Het :attribute veld verbiedt dat :other aanwezig is.',
    'regex' => 'Het formaat van :attribute is ongeldig.',
    'required' => 'Het :attribute veld is vereist.',
    'required_array_keys' => 'Het :attribute veld moet ingangen bevatten voor: :values.',
    'required_if' => 'Het :attribute veld is vereist wanneer :other gelijk is aan :value.',
    'required_unless' => 'Het :attribute veld is vereist, tenzij :other voorkomt in :values.',
    'required_with' => 'Het :attribute veld is vereist wanneer :values aanwezig is.',
    'required_with_all' => 'Het :attribute veld is vereist wanneer :values aanwezig zijn.',
    'required_without' => 'Het :attribute veld is vereist wanneer :values niet aanwezig is.',
    'required_without_all' => 'Het :attribute veld is vereist wanneer geen van :values aanwezig zijn.',
    'same' => 'Het :attribute en :other moeten overeenkomen.',
    'size' => [
        'numeric' => 'Het :attribute moet :size zijn.',
        'file' => 'Het :attribute moet :size kilobytes zijn.',
        'string' => 'Het :attribute moet :size karakters zijn.',
        'array' => 'Het :attribute moet :size items bevatten.',
    ],
    'starts_with' => 'Het :attribute moet beginnen met een van de volgende waarden: :values.',
    'string' => 'Het :attribute moet een string zijn.',
    'timezone' => 'Het :attribute moet een geldige tijdzone zijn.',
    'unique' => 'Het :attribute is al in gebruik.',
    'uploaded' => 'Het :attribute uploaden is mislukt.',
    'url' => 'Het :attribute moet een geldige URL zijn.',
    'uuid' => 'Het :attribute moet een geldige UUID zijn.',
    'invalid' => 'De opgegeven gegevens zijn ongeldig.',

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

    'attributes' => [
        'id' => 'ID',
        'first_name' => 'Voornaam',
        'last_name' => 'Achternaam',
        'company_name' => 'Bedrijfsnaam',
        'function' => 'Functie',
        'phone' => 'Telefoon',
        'date_of_birth' => 'Geboortedatum',
        'active' => 'Actief',
        'email' => 'E-mail',
        'email_verified_at' => 'E-mail geverifieerd op',
        'password' => 'Wachtwoord',
        'odoo_user_id' => 'Odoo gebruiker ID',
        'odoo_organisation_type_id' => 'Odoo organisatietype ID',
        'odoo_tarif_id' => 'Odoo tarief ID',
        'odoo_invoice_rules' => 'Odoo factuurregels',
        'parent_user_id' => 'Bovenliggende gebruiker ID',
        'remember_token' => 'Herinnerings-token',
        'signup_confirmed_at' => 'Aanmelding bevestigd op',
        'created_at' => 'Aangemaakt op',
        'updated_at' => 'Bijgewerkt op',
        'last_login_at' => 'Laatst ingelogd op',
        'deleted_at' => 'Verwijderd op',
        'user_id' => 'Gebruikers-ID',
        'undertaker' => 'Begrafenisondernemer',
        'undertaker_id' => 'Begrafenisondernemer ID',
        'quantity' => 'Hoeveelheid',
        'product_id' => 'Product ID',
        'product_data' => 'Productgegevens',
        'deceased' => 'Overledene',
        'reason' => 'Reden',
        'lot_id' => 'Kavel-ID',
        'file_number' => 'Dossiernummer',
        'ref' => 'Referentie',
        'name_user' => 'Naam gebruiker',
        'subject' => 'Onderwerp',
        'status' => 'Status',
        'ticket_viewed' => 'Ticket bekeken',
        'name' => 'Naam',
        'website' => 'Website',
        'city' => 'Stad',
        'zip' => 'Postcode',
        'odoo_id' => 'Odoo ID',
        'odoo_delivery_address_id' => 'Odoo leveradres ID',
        'address_line' => 'Adresregel',
        'number' => 'Huisnummer',
        'postcode' => 'Postcode',
    ],

];
