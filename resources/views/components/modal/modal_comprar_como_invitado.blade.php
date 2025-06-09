<div class='modal_invitado hidden' id='modal_invitado'>
    <div class='container_invitado' id='container_invitado'>
        @csrf
        <form>
        @csrf
            <div class='invitado_titulo' id='invitado_titulo'>
                Compra como Invitado
            </div>
            <div class='separador'></div>
            <div class='invitado_body' id='invitado-body'>
                <div class='invitado_texto_titulo' id='invitado_texto_titulo1'>
                    ¿Aún no estás registrado?
                </div>
                <div class='invitado_texto_texto' id='invitado_texto_texto1'>
                    ¡Regístrate para obtener descuentos especiales para miembros de Cosmos Cinema!
                </div>
                <div class='invitado_texto_titulo' id='invitado_texto_titulo2'>
                    ¿Ya estás registrado?
                </div>
                <div class='invitado_texto_texto' id='invitado_texto_texto1'>
                    ¡Inicia Sesión y disfruta de tus privilegios como miembro!
                </div>
            </div>
            <div class="separador"></div>
            <div class='invitado_correo'>
                <div class='invitado_correo_label'>Para continuar introduzca un Email</div>
                <input type="email" name="email_invitado" id='email_invitado' required class="invitado_correo_input">
            </div>
            <div class="invitado_botones" id='invitado_botones'>
                <button class="boton_volver" id="boton_invitado_volver">Volver</button>
                <button class="boton_continuar" id='boton_invitado_continuar'>Continuar como Invitado</button>
            </div>
        </form>
    </div>
</div>