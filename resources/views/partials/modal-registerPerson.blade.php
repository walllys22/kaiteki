<form action="{{ url('admin/ajax/person/store') }}" id="create-form-person" method="POST">
    @php
        $userDojoId = auth()->user()->dojo_id;
        $dojos = \App\Models\Dojo::whereNull('deleted_at')->orderBy('nombre')->get();
    @endphp
    <div class="modal fade" tabindex="-1" id="modal-create-person" role="dialog">
        <div class="modal-dialog modal-primary">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" style="color: #ffffff !important"><i class="voyager-plus"></i> Registrar Persona</h4>
                </div>
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="status" value="1">

                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="modal_person_dojo_select">Sucursal / Dojo @if(!$userDojoId)<span class="text-danger">*</span>@endif</label>
                            @if ($userDojoId)
                                <select id="modal_person_dojo_select" class="form-control" disabled>
                                    @foreach ($dojos as $dojo)
                                        <option value="{{ $dojo->id }}" {{ (string) $userDojoId === (string) $dojo->id ? 'selected' : '' }}>
                                            {{ $dojo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="dojo_id" id="modal_person_dojo_id" value="{{ $userDojoId }}">
                                <small class="text-muted">La persona se registrara en tu sucursal asignada.</small>
                            @else
                                <select name="dojo_id" id="modal_person_dojo_select" class="form-control" required>
                                    <option value="">Seleccione una sucursal</option>
                                    @foreach ($dojos as $dojo)
                                        <option value="{{ $dojo->id }}">{{ $dojo->nombre }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="document_type_person">Tipo Documento</label>
                            <select name="documentType" id="document_type_person" class="form-control">
                                <option value="" selected>Seleccione</option>
                                <option value="Ci">CI</option>
                                <option value="Nit">NIT</option>
                            </select>
                        </div>
                        <div class="form-group col-md-8">
                            <label for="ci_person">NIT/CI</label>
                            <input type="text" id="ci_person" name="ci" class="form-control" placeholder="123456789">
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="first_name_person">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" id="first_name_person" name="first_name" class="form-control" placeholder="Juan Perez" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="phone_person">Celular</label>
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
                                        <li><a href="#" onclick="setCountryPerson('51', 'pe'); return false;"><span class="fi fi-pe"></span> Peru (+51)</a></li>
                                        <li><a href="#" onclick="setCountryPerson('1', 'us'); return false;"><span class="fi fi-us"></span> USA (+1)</a></li>
                                        <li><a href="#" onclick="setCountryPerson('34', 'es'); return false;"><span class="fi fi-es"></span> Espana (+34)</a></li>
                                    </ul>
                                </span>
                                <input type="tel" id="phone_person" name="phone" class="form-control" pattern="[0-9]*" title="Ingrese un numero de celular." placeholder="76558214" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="gender">Genero <span class="text-danger">*</span></label>
                            <select name="gender" id="gender" class="form-control" required>
                                <option value="" disabled selected>Seleccione una opcion</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="birth_date_person">F. Nacimiento</label>
                            <input type="date" id="birth_date_person" name="birth_date" class="form-control">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email_person">Email</label>
                            <input type="email" id="email_person" name="email" class="form-control" placeholder="correo@ejemplo.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address_person">Direccion</label>
                        <textarea id="address_person" name="address" class="form-control" rows="3" placeholder="C/ 18 de nov. Nro 123 zona central"></textarea>
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
