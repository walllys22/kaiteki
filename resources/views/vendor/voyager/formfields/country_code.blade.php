@php
    $countryCodes = ['591' => 'bo', '54' => 'ar', '55' => 'br', '56' => 'cl', '51' => 'pe', '1' => 'us', '34' => 'es'];
    $currentCode = old($row->field, $dataTypeContent->{$row->field} ?? '591');
    $currentFlag = $countryCodes[$currentCode] ?? 'bo';
@endphp

<input type="hidden" name="{{ $row->field }}" id="{{ $row->field }}_input" value="{{ $currentCode }}">
<span class="input-group-btn">
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false" style="min-width: 110px; text-align: left; margin-top: 0; margin-bottom: 0;">
            <span id="{{ $row->field }}_flag" class="fi fi-{{ $currentFlag }}"></span> <span id="{{ $row->field }}_code">+{{ $currentCode }}</span>
            <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        <li><a href="#" onclick="setCountry_{{ $row->field }}('591', 'bo'); return false;"><span class="fi fi-bo"></span> Bolivia (+591)</a></li>
        <li><a href="#" onclick="setCountry_{{ $row->field }}('54', 'ar'); return false;"><span class="fi fi-ar"></span> Argentina (+54)</a></li>
        <li><a href="#" onclick="setCountry_{{ $row->field }}('55', 'br'); return false;"><span class="fi fi-br"></span> Brasil (+55)</a></li>
        <li><a href="#" onclick="setCountry_{{ $row->field }}('56', 'cl'); return false;"><span class="fi fi-cl"></span> Chile (+56)</a></li>
        <li><a href="#" onclick="setCountry_{{ $row->field }}('51', 'pe'); return false;"><span class="fi fi-pe"></span> Perú (+51)</a></li>
        <li><a href="#" onclick="setCountry_{{ $row->field }}('1', 'us'); return false;"><span class="fi fi-us"></span> USA (+1)</a></li>
        <li><a href="#" onclick="setCountry_{{ $row->field }}('34', 'es'); return false;"><span class="fi fi-es"></span> España (+34)</a></li>
    </ul>
</span>

<script>
    function setCountry_{{ $row->field }}(code, flag) {
        document.getElementById('{{ $row->field }}_code').innerText = '+' + code;
        document.getElementById('{{ $row->field }}_flag').className = 'fi fi-' + flag;
        document.getElementById('{{ $row->field }}_input').value = code;
    }
</script>