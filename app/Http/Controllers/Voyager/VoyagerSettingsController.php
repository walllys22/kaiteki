<?php

namespace App\Http\Controllers\Voyager;

use TCG\Voyager\Http\Controllers\VoyagerSettingsController as BaseVoyagerSettingsController;
use Illuminate\Http\Request;
use App\Http\Controllers\StorageController;

class VoyagerSettingsController extends BaseVoyagerSettingsController
{
    public function getContentBasedOnType(Request $request, $slug, $row, $options = null)
    {
        if ($row->type == 'image' && $request->hasFile($row->field)) {
            return (new StorageController())->store_image($request->file($row->field), $slug);
        }

        return parent::getContentBasedOnType($request, $slug, $row, $options);
    }
}