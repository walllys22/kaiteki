<form action="{{ url('admin/ajax/person/store') }}" id="create-form-person" method="POST">
    <div class="modal fade" tabindex="-1" id="modal-create-person" role="dialog">
        <div class="modal-dialog modal-primary">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" style="color: #ffffff !important"><i class="voyager-plus" ></i> Registrar Persona</h4>
                </div>
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="full_name">Primer Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" placeholder="Juan" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="full_name">Segundo Nombre (Opcional)</label>
                            <input type="text" name="middle_name" class="form-control" placeholder="Daniel">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="full_name">Apellido Paterno <span class="text-danger">*</span></label>
                            <input type="text" name="paternal_surname" class="form-control" placeholder="Perez" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="full_name">Apellido Materno</label>
                            <input type="text" name="maternal_surname" class="form-control" placeholder="Ortiz" >
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="full_name">NIT/CI <span class="text-danger">*</span></label>
                            <input type="text" name="ci" class="form-control" placeholder="123456789" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone">Celular</label>
                            @php
                                $countryCodes = ['591' => 'bo', '54' => 'ar', '55' => 'br', '56' => 'cl', '51' => 'pe', '1' => 'us', '34' => 'es'];
                                $currentCode = old('country_code', '591');
                                $currentFlag = $countryCodes[$currentCode] ?? 'bo';
                            @endphp
                            <input type="hidden" name="country_code" id="country_code_person" value="{{ $currentCode }}">
                            <div class="input-group">
                                <span class="input-group-btn">
                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false" style="min-width: 110px; text-align: left; margin-top: 0; margin-bottom: 0;">
                                        <span id="flag-icon-person" class="fi fi-{{ $currentFlag }}"></span> <span id="phone-code-person">+{{ $currentCode }}</span>
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="#" onclick="setCountryPerson('591', 'bo'); return false;"><span class="fi fi-bo"></span> Bolivia (+591)</a></li>
                                        <li><a href="#" onclick="setCountryPerson('54', 'ar'); return false;"><span class="fi fi-ar"></span> Argentina (+54)</a></li>
                                        <li><a href="#" onclick="setCountryPerson('55', 'br'); return false;"><span class="fi fi-br"></span> Brasil (+55)</a></li>
                                        <li><a href="#" onclick="setCountryPerson('56', 'cl'); return false;"><span class="fi fi-cl"></span> Chile (+56)</a></li>
                                        <li><a href="#" onclick="setCountryPerson('51', 'pe'); return false;"><span class="fi fi-pe"></span> Perú (+51)</a></li>
                                        <li><a href="#" onclick="setCountryPerson('1', 'us'); return false;"><span class="fi fi-us"></span> USA (+1)</a></li>
                                        <li><a href="#" onclick="setCountryPerson('34', 'es'); return false;"><span class="fi fi-es"></span> España (+34)</a></li>
                                    </ul>
                                </span>
                                <input type="tel" name="phone" class="form-control" pattern="[0-9]*" title="Ingrese un número de celular." placeholder="76558214" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="full_name">Género <span class="text-danger">*</span></label>
                            <select name="gender" id="gender" class="form-control select2" required>
                                <option value="" disabled selected>--Seleccione una opción--</option>
                                <option value="masculino">Masculino</option>
                                <option value="femenino">Femenino</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="full_name">F. Nacimiento</label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Dirección</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="C/ 18 de nov. Nro 123 zona central"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <input type="submit" class="btn btn-primary btn-save-person" value="Guardar">
                </div>
            </div>
        </div>
    </div>
</form>
<script>
    function setCountryPerson(code, flag) {
        document.getElementById('phone-code-person').innerText = '+' + code;
        document.getElementById('flag-icon-person').className = 'fi fi-' + flag;
        document.getElementById('country_code_person').value = code;
    }
</script>