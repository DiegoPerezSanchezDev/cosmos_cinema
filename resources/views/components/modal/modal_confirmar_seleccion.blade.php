<div class="modal_confirmar hidden" id="modal_confirmar_seleccion">
    <form method='POST' action='{{ route('procesar_pago') }}' id='modal_confirmar_seleccion_form'>
    @csrf



        <div id='asientos_div'></div>
        <div id='datos_sesion_div'></div>
        <div id='precio_div'></div>
    </form>
</div>