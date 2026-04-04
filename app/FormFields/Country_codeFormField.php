<?php

namespace App\FormFields;

use TCG\Voyager\FormFields\AbstractHandler;

class Country_codeFormField extends AbstractHandler
{
    protected $codename = 'country_code';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('vendor.voyager.formfields.country_code', [
            'row' => $row,
            'options' => $options,
            'dataType' => $dataType,
            'dataTypeContent' => $dataTypeContent
        ]);
    }
}