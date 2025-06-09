<div class="modal hidden" id="modalCuenta">
    <div class="modal-content">
       

        <div class="modal-header">
            <h2 class="modal-title" id="cuentaModalTitle">Mi Cuenta</h2>
                <button class="close-button1" id="cerrarCuentaModal">&times;</button>
        </div>

        <div class="modal-body">
            <form id="formCuentaUsuario" data-user-id=""> <!-- Añadir data-user-id para DNI unique rule -->

                {{-- Nombre --}}
                <div class="user-info-item form-row">
                    <strong>Nombre:</strong>
                    <span id="infoNombreDisplay" class="display-field"></span>
                    <div class="edit-field-container" style="display: none;">
                        <input type="text" id="infoNombreEdit" name="nombre" class="edit-field input" required>
                        <p id="infoNombreError" class="client-side-field-error error-text" style="display: none;"></p>
                    </div>
                </div>

                {{-- Apellidos --}}
                <div class="user-info-item form-row">
                    <strong>Apellidos:</strong>
                    <span id="infoApellidosDisplay" class="display-field"></span>
                    <div class="edit-field-container" style="display: none;">
                        <input type="text" id="infoApellidosEdit" name="apellidos" class="edit-field input" required>
                        <p id="infoApellidosError" class="client-side-field-error error-text" style="display: none;"></p>
                    </div>
                </div>

                {{-- Email (NO Editable después del primer guardado) --}}
                <div class="user-info-item form-row">
                    <strong>Email:</strong>
                    <span id="infoEmailDisplay" class="display-field"></span>
                    <div class="edit-field-container" style="display: none;">
                        <input type="email" id="infoEmailEdit" name="email" class="edit-field input" disabled> {{-- Siempre disabled en el input, se carga desde JS --}}
                    </div>
                </div>

                {{-- Fecha de Nacimiento (Editable SOLO al completar perfil, luego no) --}}
                <div class="user-info-item form-row">
                    <strong>*Fecha de Nacimiento:</strong>
                    <span id="infoFechaNacimientoDisplay" class="display-field"></span>
                    <div class="edit-field-container" style="display: none;">
                        <input type="date" id="infoFechaNacimientoEdit" name="fecha_nacimiento" class="edit-field input">
                        <p id="infoFechaNacimientoError" class="client-side-field-error error-text" style="display: none;"></p>
                    </div>
                </div>

                <!-- {{-- DNI (Editable SOLO al completar perfil, luego no) --}}
                <div class="user-info-item form-row">
                    <strong>DNI:</strong>
                    <span id="infoDniDisplay" class="display-field"></span>
                    <div class="edit-field-container" style="display: none;">
                        <input type="text" id="infoDniEdit" name="dni" class="edit-field input" maxlength="9">
                        <p id="infoDniError" class="client-side-field-error error-text" style="display: none;"></p>
                    </div>
                </div> -->

                {{-- Teléfono --}}
                <div class="user-info-item form-row">
                    <strong>Teléfono:</strong>
                    <span id="infoTelefonoDisplay" class="display-field"></span>
                    <div class="edit-field-container" style="display: none;">
                        <input type="text" id="infoTelefonoEdit" name="numero_telefono" class="edit-field input" maxlength="9">
                        <p id="infoTelefonoError" class="client-side-field-error error-text" style="display: none;"></p>
                    </div>
                </div>

                {{-- Dirección --}}
                <div class="user-info-item form-row">
                    <strong>Dirección:</strong>
                    <span id="infoDireccionDisplay" class="display-field"></span>
                    <div class="edit-field-container" style="display: none;">
                        <input type="text" id="infoDireccionEdit" name="direccion" class="edit-field input">
                        <p id="infoDireccionError" class="client-side-field-error error-text" style="display: none;"></p>
                    </div>
                </div>

                {{-- Ciudad --}}
                <div class="user-info-item form-row">
                    <strong>*Ciudad:</strong>
                    <span id="infoCiudadDisplay" class="display-field"></span>
                    <div class="edit-field-container" style="display: none;">
                        <select id="infoCiudadEdit" name="ciudad_id" class="edit-field input">
                            <option value="">Selecciona tu ciudad</option>
                            {{-- Las opciones de ciudad se cargarán dinámicamente con JS --}}
                        </select>
                        <p id="infoCiudadError" class="client-side-field-error error-text" style="display: none;"></p>
                    </div>
                </div>

                {{-- Código Postal --}}
                <div class="user-info-item form-row">
                    <strong>Código Postal:</strong>
                    <span id="infoCodigoPostalDisplay" class="display-field"></span>
                    <div class="edit-field-container" style="display: none;">
                        <input type="text" id="infoCodigoPostalEdit" name="codigo_postal" class="edit-field input" maxlength="5">
                        <p id="infoCodigoPostalError" class="client-side-field-error error-text" style="display: none;"></p>
                    </div>
                </div>

                {{-- Checkbox Mayor de 14 años (Visible en modo edición/completar) --}}
                <div class="user-info-item form-row edit-field-container" style="display: none;">
                    <label for="infoMayorEdadConfirmadoEdit" class="checkbox-label">
                        <input type="checkbox" id="infoMayorEdadConfirmadoEdit" name="mayor_edad_confirmado" value="1" class="edit-field" required>
                        Soy mayor de 14 años
                    </label>
                    <p id="infoMayorEdadConfirmadoError" class="client-side-field-error error-text" style="display: none;"></p>
                </div>

                {{-- Checkbox Acepta Publicidad --}}
                <div class="user-info-item form-row edit-field-container">
                    <label for ="infoAceptaTerminosEdit" class="checkbox-label">
                    <input type="checkbox" id="infoAceptaTerminosEdit" name="acepta_terminos" value="1" class="edit-field" required>
                        <a href="{{ route('footer_terminos_y_condiciones') }}">Leer y aceptar los términos y condiciones</a>
                    </label>
                    <p id="infoAceptaTerminosEditError" class="client-side-field-error error-text" style="display: none;"></p>
                </div>

            </form> {{-- Fin del form --}}

            <button class="btn btn-primary edit-button" id="editarPerfilBtn" style="display: block;">Editar Perfil</button>

            <div class="modal-actions">
                <button type="button" class="btn btn-primary" id="guardarCambiosBtn" style="display: none;">Guardar Cambios</button>
                <button type="button" class="btn btn-secondary" id="cancelarEdicionBtn" style="display: none;">Cancelar</button>
            </div>

            <div class="client-side-errors error-messages" style="display: none; color: red; margin-top: 10px;">
                <strong>Por favor, corrige los siguientes errores:</strong>
                <ul></ul>
            </div>
        </div>
    </div>
</div>