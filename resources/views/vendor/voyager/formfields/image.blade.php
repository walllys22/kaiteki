@if(isset($dataTypeContent->{$row->field}))
    <div data-field-name="{{ $row->field }}">
        <a href="#" class="voyager-x remove-single-image" style="position:absolute;"></a>
        @php
            $rawImg = $dataTypeContent->{$row->field};
            $isUrl = filter_var($rawImg, FILTER_VALIDATE_URL);
            $croppedKey = str_replace('.avif', '', $rawImg) . '-cropped.webp';
            $imgSrc = $isUrl ? $rawImg : \Storage::disk(env('FILESYSTEM_DRIVER'))->url($croppedKey);
        @endphp
        <img src="{{ $imgSrc }}"
          onerror="this.onerror=null; this.src='{{ $isUrl ? $rawImg : \Storage::disk(env('FILESYSTEM_DRIVER'))->url($rawImg) }}'; this.setAttribute('data-fallback','1');"
          data-file-name="{{ $rawImg }}" data-id="{{ $dataTypeContent->getKey() }}"
          style="max-width:200px; height:auto; clear:both; display:block; padding:2px; border:1px solid #ddd; margin-bottom:10px;">
    </div>
@endif
<input @if($row->required == 1 && !isset($dataTypeContent->{$row->field})) required @endif type="file" name="{{ $row->field }}" accept="image/*">
